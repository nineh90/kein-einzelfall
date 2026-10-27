<?php

namespace Tests\Feature;

use App\Mail\NachrichtAnOmbudsstelle;
use App\Models\Inquiry;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\BeschwerdemanagementSeeder;
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
        // KEV-214: Kritik geht an kontakt@, nicht mehr an kritik@.
        $this->assertStringContainsString('mailto:kontakt@kein-einzelfall.de', $html);
        $this->assertStringNotContainsString('kritik@', $html);
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

    /** KEV-213 bis KEV-216: Taddis Feinschliff. */
    public function test_gruener_satz_rechts_umbruch_oben_und_taddis_formulartexte(): void
    {
        $html = $this->get('/beschwerdemanagement')->getContent();

        // Grüner Satz unter beiden Spalten.
        $this->assertStringContainsString('Du bist nicht allein. Gemeinsam KE!N EINZELFALL!', $html);
        $this->assertMatchesRegularExpression('/font-hand[^>]*>\s*Deine Rückmeldung hilft uns, besser zu werden\./u', $html);

        // Unterzeile mit Umbruch nach dem Komma.
        $this->assertStringContainsString('whitespace-pre-line', $html);
        $this->assertStringContainsString("Deine Rückmeldung hilft uns,\nbesser zu werden.", $html);

        $this->assertStringContainsString('wenn du lieber anonym bleiben möchtest.', $html);
        $this->assertStringContainsString('Schreib so viel oder so wenig, wie sich für dich richtig anfühlt.', $html);
        $this->assertStringContainsString('Geht an die unabhängige Ombudsstelle.', $html);
        $this->assertStringNotContainsString('Geht direkt an', $html);
    }

    /** Bestehende Seiten bekommen KEV-214 per Migration, Eigenes bleibt. */
    public function test_nachtragen_tauscht_nur_den_alten_wortlaut(): void
    {
        $block = \App\Models\Page::where('slug', 'beschwerdemanagement')->where('locale', 'de')->sole()
            ->blocks()->where('typ', 'formular_spalten')->sole();
        $data = $block->data;
        $data['spalten'][0]['absaetze'][4] = 'Wenn du Unterstützung brauchst, melde dich bei uns: kritik@kein-einzelfall.de';
        unset($data['spalten'][1]['hand']);
        $data['spalten'][0]['hand'] = 'Vom Verein geändert.';
        $block->update(['data' => $data]);

        BeschwerdemanagementSeeder::nachtragen();
        BeschwerdemanagementSeeder::nachtragen();

        $spalten = $block->fresh()->data['spalten'];
        $this->assertSame('Wenn du Unterstützung brauchst, melde dich bei uns: kontakt@kein-einzelfall.de', $spalten[0]['absaetze'][4]);
        $this->assertSame('Vom Verein geändert.', $spalten[0]['hand']);
        $this->assertSame('Deine Rückmeldung hilft uns, besser zu werden.', $spalten[1]['hand']);
    }

    public function test_knopf_waehlt_den_weg_vor(): void
    {
        $html = $this->get('/beschwerdemanagement?weg=ombudsstelle')->getContent();

        $this->assertMatchesRegularExpression('/value="ombudsstelle"[^>]*checked/s', $html);
        $this->assertDoesNotMatchRegularExpression('/value="anfrage"[^>]*checked/s', $html);
    }

    /** KEV-98 wollte Position 5, seit KEV-97 stehen davor Schutzkonzept und Red Flags. */
    /** Seit KEV-178 an Position neun, hinter dem Gremium UKFB. */
    public function test_steht_im_menue_verein_an_position_neun(): void
    {
        $verein = collect(config('navigation.main'))->firstWhere('url', '/verein');

        $this->assertSame('/beschwerdemanagement', $verein['children'][8]['url']);
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
