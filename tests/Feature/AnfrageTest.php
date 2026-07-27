<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Page;
use App\Notifications\NeueAnfrage;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Das Kontaktformular verarbeitet Angaben nach Art. 9 DSGVO — Menschen schreiben
 * hier über erlebte Straftaten und ihre Gesundheit. Diese Tests halten die
 * Zusagen fest, die wir dazu gemacht haben.
 */
class AnfrageTest extends TestCase
{
    use RefreshDatabase;

    private function gueltig(array $abweichend = []): array
    {
        return array_merge([
            'betreff' => 'Frage zum OEG-Antrag',
            'nachricht' => 'Hallo, ich habe eine Frage zu meinem Antrag und komme nicht weiter.',
            'einwilligung' => '1',
            'webseite' => '',
            'gestartet_um' => encrypt(now()->subSeconds(30)->timestamp),
        ], $abweichend);
    }

    public function test_anfrage_wird_gespeichert(): void
    {
        $this->post('/anfrage', $this->gueltig(['name' => 'Test', 'email' => 'a@b.de']))
            ->assertRedirect()
            ->assertSessionHas('anfrage_versendet');

        $anfrage = Inquiry::sole();
        $this->assertSame('Frage zum OEG-Antrag', $anfrage->betreff);
        $this->assertSame('Test', $anfrage->name);
        $this->assertSame('offen', $anfrage->status);
    }

    /**
     * Der Kern des Datenschutzkonzepts: Wer einen Datenbankabzug in die Hände
     * bekommt — Backup, Hoster-Panel, offenes phpMyAdmin — darf keinen Klartext
     * sehen.
     */
    public function test_inhalte_liegen_verschluesselt_in_der_datenbank(): void
    {
        $this->post('/anfrage', $this->gueltig([
            'name' => 'Maria Muster',
            'email' => 'maria@example.org',
            'betreff' => 'Sehr persönliches Anliegen',
            'nachricht' => 'Vertrauliche Schilderung eines Vorfalls.',
        ]));

        $roh = DB::table('inquiries')->sole();

        foreach (['Maria Muster', 'maria@example.org', 'Sehr persönliches Anliegen',
            'Vertrauliche Schilderung eines Vorfalls.'] as $klartext) {
            $this->assertStringNotContainsString($klartext, json_encode($roh));
        }

        // Über Eloquent aber ganz normal lesbar
        $this->assertSame('Maria Muster', Inquiry::sole()->name);
    }

    public function test_anonyme_anfrage_ist_moeglich(): void
    {
        // Auf der Altseite sind Name und E-Mail Pflichtfelder. Für diese
        // Zielgruppe ist das eine Hürde — hier ist beides freiwillig.
        $this->post('/anfrage', $this->gueltig())->assertRedirect();

        $anfrage = Inquiry::sole();
        $this->assertNull($anfrage->name);
        $this->assertNull($anfrage->email);
        $this->assertTrue($anfrage->istAnonym());
    }

    public function test_rueckmeldung_sagt_ehrlich_dass_ohne_adresse_keine_antwort_kommt(): void
    {
        $this->post('/anfrage', $this->gueltig())
            ->assertSessionHas('anfrage_versendet', fn ($text) => str_contains($text, 'nicht direkt antworten'));

        $this->post('/anfrage', $this->gueltig(['email' => 'wer@example.org']))
            ->assertSessionHas('anfrage_versendet', fn ($text) => str_contains($text, 'melden uns'));
    }

    /**
     * E-Mail ist ein unverschlüsselter Transportweg und bleibt jahrelang in
     * Postfächern liegen. Der Inhalt der Anfrage darf dort nicht auftauchen.
     */
    public function test_benachrichtigung_enthaelt_keinen_inhalt_der_anfrage(): void
    {
        Notification::fake();
        config(['mail.anfragen_an' => 'verwaltung@example.org']);

        $this->post('/anfrage', $this->gueltig([
            'name' => 'Maria Muster',
            'email' => 'maria@example.org',
            'betreff' => 'Sehr persönliches Anliegen',
            'nachricht' => 'Vertrauliche Schilderung eines Vorfalls.',
        ]));

        Notification::assertSentOnDemand(NeueAnfrage::class, function ($notification, $channels, $notifiable) {
            $inhalt = json_encode($notification->toMail($notifiable)->toArray());

            foreach (['Maria Muster', 'maria@example.org', 'Sehr persönliches Anliegen',
                'Vertrauliche Schilderung'] as $geheim) {
                $this->assertStringNotContainsString($geheim, $inhalt);
            }

            return true;
        });
    }

    public function test_anfrage_wird_auch_ohne_mailversand_gespeichert(): void
    {
        // Ein Ausfall des Mailservers darf keine Nachricht verschlucken.
        config(['mail.anfragen_an' => null]);

        $this->post('/anfrage', $this->gueltig())->assertRedirect();

        $this->assertSame(1, Inquiry::count());
    }

    public function test_pflichtfelder_und_einwilligung_werden_geprueft(): void
    {
        $this->post('/anfrage', $this->gueltig(['betreff' => '', 'nachricht' => '']))
            ->assertSessionHasErrors(['betreff', 'nachricht']);

        $this->post('/anfrage', $this->gueltig(['einwilligung' => null]))
            ->assertSessionHasErrors('einwilligung');

        $this->assertSame(0, Inquiry::count());
    }

    public function test_ungueltige_mailadresse_wird_abgelehnt(): void
    {
        $this->post('/anfrage', $this->gueltig(['email' => 'keine-adresse']))
            ->assertSessionHasErrors('email');
    }

    public function test_honigtopf_faengt_automatische_eintraege_ab(): void
    {
        // Kein CAPTCHA: das wäre eine zusätzliche Hürde ausgerechnet für die
        // Menschen, die ohnehin Mühe haben.
        $this->post('/anfrage', $this->gueltig(['webseite' => 'http://spam.example']))
            ->assertSessionHasErrors('webseite');

        $this->assertSame(0, Inquiry::count());
    }

    public function test_zu_schnelles_absenden_wird_abgelehnt(): void
    {
        $this->post('/anfrage', $this->gueltig([
            'gestartet_um' => encrypt(now()->timestamp),
        ]))->assertSessionHasErrors('nachricht');

        $this->assertSame(0, Inquiry::count());
    }

    public function test_formular_steht_im_server_html_und_braucht_kein_javascript(): void
    {
        // Muss auch in gehärteten Browsern oder über Tor funktionieren.
        $this->seed(AltseiteSeeder::class);

        Page::where('slug', 'anfragen')->first()->blocks()->create([
            'typ' => 'contact_form',
            'position' => 99,
            'data' => ['titel' => 'Schreib uns'],
        ]);

        $this->get('/anfragen')
            ->assertSee('<form method="POST"', false)
            ->assertSee('name="betreff"', false)
            ->assertSee('name="einwilligung"', false);
    }

    public function test_keine_ip_adresse_wird_gespeichert(): void
    {
        // Was nicht gespeichert wird, kann auch nicht abfliessen.
        $this->post('/anfrage', $this->gueltig());

        $spalten = array_keys((array) DB::table('inquiries')->sole());

        foreach (['ip', 'ip_address', 'user_agent'] as $unerwuenscht) {
            $this->assertNotContains($unerwuenscht, $spalten);
        }
    }
}
