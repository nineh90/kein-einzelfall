<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AnuasSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-162: Projekt „ANUAS“ unter „Projekte“, Taddis Text zur ANUAS-
 * Arbeitsgruppe „Mit-Opferrechte“ mit ihrer Projektdokumentation.
 */
class AnuasTest extends TestCase
{
    use RefreshDatabase;

    public function test_erstes_projekt_unter_projekte(): void
    {
        $projekte = collect(config('navigation.main'))->firstWhere('url', '/projekte');
        $this->assertSame(['/anuas'], array_column($projekte['children'], 'url'));

        $html = $this->get('/anuas')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*ANUAS\s*<\/h1>/u', $html);
        // Ihr Projekttitel als Unterzeile, der Bereich darüber
        $this->assertStringContainsString('Opferschutz aus Betroffenenperspektive weiterdenken', $html);
        $this->assertMatchesRegularExpression('/aria-label="Sie sind hier".*Projekte.*ANUAS/s', $html);
        // Noch kein Bild im Ordner
        $this->assertStringContainsString('src="/img/titelbilder/platzhalter.webp?v=', $html);
        $this->assertStringContainsString('/anuas</loc>', $this->get('/sitemap.xml')->getContent());
    }

    public function test_taddis_text_mit_projektdokumentation(): void
    {
        $html = $this->get('/anuas')->getContent();

        $this->assertMatchesRegularExpression('/<h2[^>]*>\s*Worum geht es\?\s*<\/h2>/u', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>ANUAS-Arbeitsgruppe „Mit-Opferrechte“<\/strong>/u', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>nicht nur über Betroffene gesprochen wird, sondern mit ihnen\.<\/strong>/u', $html);

        // Die Stände, neuester oben, der Stand über der Überschrift
        $staende = ['Stand 18.10.2026', 'Stand Oktober 2026', 'Stand 13.10.2026', 'Stand September 2026', 'Stand 23.09.2025'];
        $vorher = strpos($html, 'Projektdokumentation');
        foreach ($staende as $stand) {
            $hier = strpos($html, $stand);
            $this->assertGreaterThan($vorher, $hier, $stand);
            $vorher = $hier;
        }
        $this->assertMatchesRegularExpression('/Stand 18\.10\.2026.*?<h2[^>]*>\s*Unsere Stellungnahme zum Fachdialog „Mit-Opferrechte“/su', $html);

        // Ihre Aufzählungen als Listen, ihr Fettes fett
        $this->assertStringContainsString('<p>Dazu gehören insbesondere:</p><ul><li>verbindliche Traumasensibilität', $html);
        $this->assertStringContainsString('<li>und eine verlässliche Lotsenfunktion.</li></ul>', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Betroffenenverträglichkeitsprüfung<\/strong>/u', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Wie können Institutionen überprüfen, ob ihre Verfahren für Betroffene tatsächlich funktionieren\?<\/strong>/u', $html);
        $this->assertStringContainsString('des Bundesverbandes ANUAS e.V. zur Situation', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Gemeinsam KE!N EINZELFALL\.<\/strong>/u', $html);

        // Kein Abschnitt eingeklappt, daneben „Auf dieser Seite“
        $this->assertStringNotContainsString('Weiterlesen', $html);
        $this->assertStringContainsString('data-verzeichnis', $html);
        foreach (['Mit-Opfer sind nicht „nur Angehörige“', 'Betroffenenexpertise gehört an den Tisch', 'Unser Ziel'] as $titel) {
            $this->assertMatchesRegularExpression('/<h2[^>]*>\s*'.preg_quote($titel, '/').'\s*<\/h2>/u', $html, $titel);
        }
    }

    /** Die PDFs aus Taddis Ordner gehören zum Projekt „Opferanwalt“ und warten auf Antwort. */
    public function test_ohne_die_opferanwalt_dokumente(): void
    {
        $html = $this->get('/anuas')->getContent();

        $this->assertStringNotContainsString('Opferanwalt', $html);
        $this->assertStringNotContainsString('.pdf', $html);
        $this->assertStringNotContainsString('Hummelsbütteler', $html);
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'anuas')->where('locale', 'de')->firstOrFail();
        $block = $seite->blocks()->orderBy('position')->firstOrFail();
        $block->update(['data' => ['titel' => 'Vom Verein geändert.']]);

        AnuasSeeder::anlegen();
        (require database_path('migrations/2026_10_23_270000_projekt_anuas.php'))->up();

        $this->assertSame(1, Page::where('slug', 'anuas')->where('locale', 'de')->count());
        $this->assertSame(count(AnuasSeeder::abschnitte()), $seite->blocks()->count());
        $this->assertSame('Vom Verein geändert.', $block->fresh()->data['titel']);
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/anuas')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*ANUAS\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Rethinking victim protection from the perspective of those affected', $html);
        $this->assertStringContainsString('As of 18 October 2026', $html);
        $this->assertStringContainsString('Co-victims are not “just relatives”', $html);
        $this->assertStringNotContainsString('Stand 18.10.2026', $html);
    }
}
