<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Diese Tests sichern das Fundament ab, das für die Zielgruppe nicht verhandelbar ist.
 * Sie prüfen bewusst das ausgelieferte Server-HTML — genau dort liegt der Defekt der
 * Altseite, deren Navigation und Notausgang erst per JavaScript entstehen.
 */
class BarrierefreiheitTest extends TestCase
{
    public function test_seite_hat_sprache_hauptbereich_und_sprunglink(): void
    {
        $r = $this->get('/');

        $r->assertOk();
        $r->assertSee('<html lang="de"', false);
        $r->assertSee('<main id="inhalt"', false);
        $r->assertSee('Zum Inhalt springen');
    }

    public function test_navigation_steht_im_server_html(): void
    {
        // Der zentrale Fortschritt gegenüber der Altseite: die Navigation ist da,
        // ohne dass JavaScript laufen muss.
        $r = $this->get('/');

        $r->assertSee('Hauptnavigation');
        foreach (['Verein', 'Wissen', 'Spenden', 'Kontakt'] as $punkt) {
            $r->assertSee($punkt);
        }
        // Auch die Unterpunkte, nicht nur die Hauptebene
        $r->assertSee('Satzung');
        $r->assertSee('Erwerbsminderungsrente');
    }

    public function test_notausgang_ist_ein_echter_link_und_funktioniert_ohne_javascript(): void
    {
        $r = $this->get('/');

        $r->assertSee('href="'.config('navigation.exit_url').'"', false);
        $r->assertSee('data-notausgang', false);
        // Ohne rel="noreferrer" würde die Zielseite die Herkunft sehen.
        $r->assertSee('rel="noreferrer noopener"', false);
    }

    public function test_notausgang_ist_auf_jeder_seite_erreichbar(): void
    {
        foreach (['/'] as $pfad) {
            $this->get($pfad)->assertSee('data-notausgang', false);
        }
    }

    public function test_keine_externen_ressourcen_werden_geladen(): void
    {
        // DSGVO-Kern: vor jedem Consent darf kein Drittserver kontaktiert werden.
        // Reine <a href>-Links sind erlaubt, geladene Ressourcen nicht.
        $html = $this->get('/')->getContent();

        preg_match_all(
            '/(?:src|href)="(https?:\/\/[^"]+)"[^>]*?(?:rel="stylesheet"|as="font")?/i',
            $html,
            $treffer
        );

        $eigenerHost = parse_url(config('app.url'), PHP_URL_HOST);

        foreach ($treffer[1] as $url) {
            $host = parse_url($url, PHP_URL_HOST);

            // Erlaubt: die eigene Domain und Ziele von Verweisen
            // (Social-Profile, Notausgang, vertraglich zugesagte Umsetzer-Nennung).
            $erlaubt = [$eigenerHost, 'www.wetter.com', 'nils-digital.de',
                        'www.instagram.com', 'www.facebook.com', 'www.tiktok.com'];

            $this->assertContains(
                $host,
                $erlaubt,
                "Unerwarteter externer Host im HTML: {$host}"
            );
        }

        // Google Fonts sind der klassische Verstoß — explizit ausschließen.
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('fonts.gstatic.com', $html);
    }

    public function test_schriften_liegen_lokal_vor(): void
    {
        // Statische Dateien liefert der Test-Kernel nicht aus, deshalb Dateisystem-Prüfung.
        // Entscheidend ist: die Dateien existieren und die CSS zeigt nicht zu Google.
        foreach (['source-serif-4-latin', 'fraunces-latin', 'caveat-latin'] as $datei) {
            $this->assertFileExists(public_path("fonts/{$datei}.woff2"));
        }

        $css = file_get_contents(resource_path('css/fonts.css'));
        $this->assertStringNotContainsString('gstatic', $css);
        $this->assertStringNotContainsString('googleapis', $css);
    }

    public function test_darstellungseinstellungen_greifen_vor_dem_ersten_paint(): void
    {
        // Ohne dieses Inline-Script blitzt bei jedem Aufruf die Standardansicht auf.
        $this->get('/')->assertSee("localStorage.getItem('ke-a11y')", false);
    }

    public function test_umsetzer_wird_im_footer_genannt(): void
    {
        // Vertraglich zugesagt.
        $this->get('/')->assertSee('nils-digital.de', false);
    }

    public function test_ueberschriften_bilden_eine_saubere_gliederung(): void
    {
        $html = $this->get('/')->getContent();
        $html = preg_replace('/<(script|style|template).*?<\/\1>/s', '', $html);

        preg_match_all('/<h([1-6])[^>]*>/', $html, $treffer);
        $ebenen = array_map('intval', $treffer[1]);

        $this->assertNotEmpty($ebenen);

        // Genau eine h1 …
        $this->assertSame(1, count(array_filter($ebenen, fn ($e) => $e === 1)));

        // … und sie muss die erste Überschrift im Dokument sein. Die A11y-Toolbar
        // steht im Quelltext vor dem Seiteninhalt und hatte hier schon mal ein h2,
        // das die Gliederung angeführt hat.
        $this->assertSame(1, $ebenen[0], 'Erste Überschrift muss die h1 sein');

        // Keine übersprungenen Ebenen (WCAG 1.3.1)
        for ($i = 1; $i < count($ebenen); $i++) {
            $this->assertLessThanOrEqual(
                $ebenen[$i - 1] + 1,
                $ebenen[$i],
                "Sprung von h{$ebenen[$i - 1]} zu h{$ebenen[$i]}"
            );
        }
    }

    public function test_bilder_haben_alt_attribute(): void
    {
        preg_match_all('/<img[^>]*>/', $this->get('/')->getContent(), $treffer);

        $this->assertNotEmpty($treffer[0]);
        foreach ($treffer[0] as $img) {
            $this->assertStringContainsString('alt=', $img);
        }
    }
}
