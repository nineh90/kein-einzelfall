<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hält die Datenschutz-Zusagen fest, die über das gesetzliche Minimum hinausgehen.
 * Grundlage sind die Mängel, die wir auf der Altseite gemessen haben.
 */
class DatenschutzTest extends TestCase
{
    use RefreshDatabase;

    public function test_sicherheitsheader_werden_gesetzt(): void
    {
        // Die Altseite liefert davon keinen einzigen aus.
        $antwort = $this->get('/');

        $antwort->assertHeader('X-Content-Type-Options', 'nosniff');
        $antwort->assertHeader('X-Frame-Options', 'DENY');
        $antwort->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertNotEmpty($antwort->headers->get('Permissions-Policy'));
        $this->assertNotEmpty($antwort->headers->get('Content-Security-Policy'));
    }

    public function test_content_security_policy_verbietet_fremde_skripte(): void
    {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);

        // 'unsafe-inline' bei Skripten würde den Schutz weitgehend aufheben.
        $skriptRegel = collect(explode(';', $csp))
            ->first(fn ($r) => str_contains($r, 'script-src'));
        $this->assertStringNotContainsString('unsafe-inline', $skriptRegel);
        $this->assertStringContainsString('nonce-', $skriptRegel);
    }

    public function test_alle_skripte_tragen_das_nonce_der_antwort(): void
    {
        // Ohne Nonce blockiert 'strict-dynamic' das Skript — und damit die
        // Bedienoberfläche. Das betrifft auch die von Vite erzeugten Tags.
        $html = $this->get('/')->getContent();

        preg_match('/nonce="([^"]+)"/', $html, $treffer);
        $nonce = $treffer[1] ?? null;
        $this->assertNotNull($nonce);

        preg_match_all('/<script\b[^>]*>/', $html, $skripte);

        foreach ($skripte[0] as $tag) {
            $this->assertStringContainsString(
                'nonce="'.$nonce.'"',
                $tag,
                "Script-Tag ohne Nonce: {$tag}"
            );
        }
    }

    public function test_nonce_ist_bei_jedem_aufruf_neu(): void
    {
        preg_match('/nonce="([^"]+)"/', $this->get('/')->getContent(), $a);
        preg_match('/nonce="([^"]+)"/', $this->get('/')->getContent(), $b);

        $this->assertNotSame($a[1], $b[1]);
    }

    public function test_eingebettete_inhalte_laden_erst_nach_zustimmung(): void
    {
        // Auf der Altseite laden zwei betterplace-Rahmen auf /spenden/ ungefragt,
        // obwohl dort ein Cookie-Banner steht.
        $this->seed(AltseiteSeeder::class);
        $this->spendenBlockAnlegen();

        $html = $this->get('/spenden')->getContent();

        // Der Rahmen steckt in einem <template> — was dort steht, lädt der
        // Browser nicht. Ausserhalb davon darf keine Anbieter-Adresse stehen.
        $ohneTemplate = preg_replace('/<template[^>]*>.*?<\/template>/s', '', $html);

        $this->assertStringNotContainsString('<iframe', $ohneTemplate);
        $this->assertStringNotContainsString('project-widget.betterplace.org', $ohneTemplate);

        // Die Zustimmungsfrage muss aber sichtbar sein.
        $this->assertStringContainsString('Inhalt einmalig anzeigen', $html);
        $this->assertStringContainsString('betterplace.org', $html);
    }

    public function test_nur_freigegebene_anbieter_duerfen_eingebettet_werden(): void
    {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $frame = collect(explode(';', $csp))->first(fn ($r) => str_contains($r, 'frame-src'));

        foreach (config('embeds.erlaubte_quellen') as $quelle) {
            $this->assertStringContainsString($quelle, $frame);
        }

        // Ein beliebiger anderer Anbieter darf nicht durchkommen.
        $this->assertStringNotContainsString('youtube.com', $frame);
    }

    public function test_spendenseite_zeigt_die_angaben_des_vereins(): void
    {
        $this->seed(AltseiteSeeder::class);
        $this->spendenBlockAnlegen();

        $this->get('/spenden')
            ->assertSee('DE79 8306 5408 0006 8893 10')
            ->assertSee('GENODEF1SLR')
            ->assertSee('verwaltung@kein-einzelfall.de');
    }

    public function test_seite_setzt_nur_technisch_notwendige_cookies(): void
    {
        // Kein Tracking, keine Fremdinhalte ohne Zustimmung — deshalb braucht
        // diese Seite auch keinen Zustimmungsbanner. Sollte das jemals anders
        // sein, schlägt dieser Test an.
        $antwort = $this->get('/');

        $erlaubt = ['XSRF-TOKEN', config('session.cookie')];

        foreach ($antwort->headers->getCookies() as $cookie) {
            $this->assertContains(
                $cookie->getName(),
                $erlaubt,
                "Unerwartetes Cookie: {$cookie->getName()}"
            );
        }
    }

    private function spendenBlockAnlegen(): void
    {
        Page::where('slug', 'spenden')->first()->blocks()->create([
            'typ' => 'donation_options',
            'position' => 99,
            'data' => [
                'bank' => ['institut' => 'Deutsche Skatbank',
                    'iban' => 'DE79 8306 5408 0006 8893 10',
                    'bic' => 'GENODEF1SLR'],
                'projekte' => [[
                    'titel' => 'Onlinepräsenz',
                    'widget' => 'https://project-widget.betterplace.org/projects/170775?l=de',
                ]],
                'bescheinigung' => ['text' => 'Schreib uns.', 'email' => 'verwaltung@kein-einzelfall.de'],
            ],
        ]);
    }
}
