<?php

namespace Tests\Feature;

use App\Mail\NachrichtAnOmbudsstelle;
use App\Models\Inquiry;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * KEV-98: Beschwerdemanagement mit zwei Wegen und einem Formular. Externe
 * Kritik landet wie jede Anfrage im Verwaltungsbereich, die interne
 * Beschwerde geht an die unabhängige Ombudsstelle und darf dort nicht landen.
 */
class BeschwerdemanagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
        Notification::fake();
    }

    private function gueltig(array $abweichend = []): array
    {
        return array_merge([
            'betreff' => 'Umgang im Verein',
            'nachricht' => 'Ich möchte mich über eine Entscheidung beschweren.',
            'einwilligung' => '1',
            'formular' => 'beschwerde',
            'webseite' => '',
            'gestartet_um' => encrypt(now()->subSeconds(30)->timestamp),
        ], $abweichend);
    }

    public function test_seite_zeigt_beide_wege_und_ein_formular_mit_auswahl(): void
    {
        $html = $this->get('/beschwerdemanagement')->assertOk()->getContent();

        $this->assertStringContainsString('Externe Kritik', $html);
        $this->assertStringContainsString('Interne Beschwerde', $html);
        $this->assertStringContainsString('mailto:kritik@kein-einzelfall.de', $html);
        // Taddis Tippfehler „beschwedemanagement“ ist korrigiert.
        $this->assertStringContainsString('mailto:beschwerdemanagement@kein-einzelfall.de', $html);
        $this->assertStringNotContainsString('beschwedemanagement', $html);

        // Ein Formular, nicht zwei gleiche.
        $this->assertSame(1, substr_count($html, '<form method="POST"'));
        $this->assertStringContainsString('action="'.url('/beschwerde').'"', $html);
        $this->assertStringContainsString('name="weg" value="anfrage"', $html);
        $this->assertStringContainsString('name="weg" value="ombudsstelle"', $html);

        // Nichts vorausgewählt: Eine Beschwerde über den Verein soll nicht aus
        // Versehen beim Verein landen.
        $this->assertStringNotContainsString(' checked', $html);

        // Die Knöpfe unter den Texten springen zum Formular und wählen vor.
        $this->assertStringContainsString('href="?weg=anfrage#formular-beschwerde"', $html);
        $this->assertStringContainsString('href="?weg=ombudsstelle#formular-beschwerde"', $html);
    }

    public function test_knopf_waehlt_den_weg_vor(): void
    {
        $html = $this->get('/beschwerdemanagement?weg=ombudsstelle')->getContent();

        $this->assertMatchesRegularExpression('/value="ombudsstelle"[^>]*checked/s', $html);
        $this->assertDoesNotMatchRegularExpression('/value="anfrage"[^>]*checked/s', $html);
    }

    /** KEV-98 wollte Position 5, seit KEV-97 stehen davor Schutzkonzept und Red Flags. */
    public function test_steht_im_menue_verein_an_position_sieben(): void
    {
        $verein = collect(config('navigation.main'))->firstWhere('url', '/verein');

        $this->assertSame('/beschwerdemanagement', $verein['children'][6]['url']);
    }

    public function test_ohne_auswahl_wird_nichts_verschickt(): void
    {
        Mail::fake();

        $this->from('/beschwerdemanagement')
            ->post('/beschwerde', $this->gueltig())
            ->assertRedirect('/beschwerdemanagement#formular-beschwerde')
            ->assertSessionHasErrors('weg');

        $this->assertSame(0, Inquiry::count());
        Mail::assertNothingSent();
    }

    public function test_kritik_landet_im_verwaltungsbereich(): void
    {
        Mail::fake();

        $this->from('/beschwerdemanagement')
            ->post('/beschwerde', $this->gueltig(['weg' => 'anfrage', 'herkunft' => 'beschwerdemanagement']))
            ->assertRedirect('/beschwerdemanagement#formular-beschwerde')
            ->assertSessionHas('anfrage_versendet');

        $this->assertSame('beschwerdemanagement · Kritik', Inquiry::sole()->herkunft);
        Mail::assertNothingSent();
    }

    public function test_interne_beschwerde_geht_an_die_ombudsstelle_und_wird_nicht_gespeichert(): void
    {
        Mail::fake();
        config(['mail.ombudsstelle_an' => 'ombud@example.org']);

        $this->from('/beschwerdemanagement')
            ->post('/beschwerde', $this->gueltig([
                'weg' => 'ombudsstelle',
                'name' => 'Maria',
                'email' => 'maria@example.org',
            ]))
            ->assertRedirect('/beschwerdemanagement#formular-beschwerde')
            ->assertSessionHas('anfrage_versendet');

        Mail::assertSent(NachrichtAnOmbudsstelle::class, function (NachrichtAnOmbudsstelle $mail) {
            return $mail->hasTo('ombud@example.org')
                && $mail->hasReplyTo('maria@example.org')
                && $mail->nachricht === 'Ich möchte mich über eine Entscheidung beschweren.';
        });

        // Weder im Verwaltungsbereich noch als Hinweis an den Verein.
        $this->assertSame(0, Inquiry::count());
        Notification::assertNothingSent();
    }

    public function test_mail_an_die_ombudsstelle_enthaelt_den_text_unveraendert(): void
    {
        $text = (new NachrichtAnOmbudsstelle(null, null, 'A & B', 'Zeile mit <b>Zeichen</b> & "Zitat"'))
            ->render();

        $this->assertStringContainsString('Zeile mit <b>Zeichen</b> & "Zitat"', $text);
        $this->assertStringContainsString('(nicht angegeben', $text);
    }

    public function test_beschwerde_braucht_dieselben_pflichtfelder(): void
    {
        Mail::fake();

        $this->post('/beschwerde', $this->gueltig(['weg' => 'ombudsstelle', 'nachricht' => '']))
            ->assertSessionHasErrors('nachricht');

        Mail::assertNothingSent();
    }

    public function test_auf_dem_server_ohne_echten_mailversand_wird_nichts_ins_log_geschrieben(): void
    {
        // Mit MAIL_MAILER=log stünde die Beschwerde im Klartext in storage/logs.
        Mail::fake();
        $this->app['env'] = 'production';
        config(['mail.default' => 'log']);
        // Ausserhalb von „testing“ prüft Laravel das CSRF-Token wirklich.
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->from('/beschwerdemanagement')
            ->post('/beschwerde', $this->gueltig(['weg' => 'ombudsstelle']))
            ->assertRedirect('/beschwerdemanagement#formular-beschwerde')
            ->assertSessionHas('versand_fehlgeschlagen', 'beschwerde')
            ->assertSessionHasInput('nachricht');

        Mail::assertNothingSent();
    }

    public function test_nach_einem_fehler_bleiben_wahl_und_text_stehen(): void
    {
        $this->from('/beschwerdemanagement')
            ->post('/beschwerde', $this->gueltig([
                'weg' => 'ombudsstelle',
                'betreff' => 'x',
                'nachricht' => 'Mein Text bleibt stehen.',
            ]));

        $html = $this->get('/beschwerdemanagement')->getContent();

        $this->assertStringContainsString('id="beschwerde-betreff-fehler"', $html);
        $this->assertStringContainsString('Mein Text bleibt stehen.', $html);
        $this->assertMatchesRegularExpression('/value="ombudsstelle"[^>]*checked/s', $html);
    }

    public function test_formularkennung_kann_keine_adresse_einschleusen(): void
    {
        $this->from('/beschwerdemanagement')
            ->post('/anfrage', $this->gueltig(['formular' => 'x"><script>']))
            ->assertSessionHasErrors('formular');
    }
}
