<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\Paragraf109SggSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-161: Projekt „§ 109 SGG Gegengutachten“ unter „Projekte“ mit Taddis
 * Projektdokumentation, dazu für alle Projekte vorerst das Bild der Übersicht.
 */
class Paragraf109SggTest extends TestCase
{
    use RefreshDatabase;

    private const ADRESSE = '/109-sgg-gegengutachten';

    public function test_projekt_unter_projekte_mit_kachel(): void
    {
        $html = $this->get(self::ADRESSE)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*§ 109 SGG Gegengutachten\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Wenn ein Recht an der Suche nach Sachverständigen scheitert', $html);
        $this->assertMatchesRegularExpression('/aria-label="Sie sind hier".*Projekte.*§ 109 SGG Gegengutachten/s', $html);
        $this->assertStringContainsString(self::ADRESSE.'</loc>', $this->get('/sitemap.xml')->getContent());

        // Auf der Übersicht nach ANUAS
        $uebersicht = $this->get('/projekte')->getContent();
        $this->assertGreaterThan(strpos($uebersicht, 'href="/anuas"'), strpos($uebersicht, 'href="'.self::ADRESSE.'"'));
    }

    /** Kevin: „Nutze bei Projekte erst mal überall das gleiche Hero Bild.“ */
    public function test_alle_projekte_mit_dem_bild_der_uebersicht(): void
    {
        foreach (['/projekte', '/anuas', self::ADRESSE] as $adresse) {
            $html = $this->get($adresse)->getContent();
            $this->assertStringContainsString('src="/img/titelbilder/projekte.webp?v=', $html, $adresse);
            $this->assertStringContainsString('object-position: 0% 40%', $html, $adresse);
        }
        $this->assertSame('/img/titelbilder/projekte.webp', Titelbilder::datei('anuas'));
    }

    public function test_taddis_projektdokumentation(): void
    {
        $html = $this->get(self::ADRESSE)->getContent();

        $this->assertMatchesRegularExpression('/<strong[^>]*>§ 109 SGG<\/strong> Betroffenen die Möglichkeit/u', $html);
        $this->assertStringContainsString('<p>Unser Ziel ist es herauszufinden:</p><ul><li>Wo können Betroffene geeignete Sachverständige finden?</li>', $html);

        // Zehn Stände, neuester oben
        $staende = ['Stand 08.09.2026', 'Stand 31.08.2026', 'Stand 21.07.2026', 'Stand 16.07.2026', 'Stand Juni 2026',
            'Stand April 2026', 'Stand 03.04.2026', 'Stand 31.03.2026', 'Stand 30.03.2026', 'Stand 17.10.2025'];
        $vorher = strpos($html, 'Projektdokumentation');
        foreach ($staende as $stand) {
            $hier = strpos($html, $stand);
            $this->assertGreaterThan($vorher, $hier, $stand);
            $vorher = $hier;
        }
        $this->assertMatchesRegularExpression('/<strong[^>]*>Sozialgericht Augsburg<\/strong>/u', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Ein solches Verzeichnis wurde bis dahin nicht erstellt\.<\/strong>/u', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>§ 124 Abs\. 4 Nr\. 5 SGB XIV<\/strong>/u', $html);
        $this->assertMatchesRegularExpression('/„in der Entwicklung“\s*<\/h2>/u', $html);

        // Umgestellt: keine Liste mit „verwiesen.“ allein darunter
        $this->assertStringContainsString('<p>Zur eigenen Suche verweist das BMAS Betroffene weiterhin unter anderem auf:</p><ul><li>Ärztekammern,</li>', $html);
        $this->assertStringNotContainsString('<p>verwiesen.</p>', $html);
        // Korrigiert: Betroffene benennen, das Gericht hört an
        $this->assertMatchesRegularExpression('/<strong[^>]*>§ 109 SGG einen bestimmten Arzt anhören zu lassen<\/strong>/u', $html);

        $this->assertMatchesRegularExpression('/<strong[^>]*>Rechte müssen nicht nur bestehen\. Sie müssen auch erreichbar sein\.<\/strong>/u', $html);
        $this->assertStringNotContainsString('Weiterlesen', $html);
        $this->assertStringContainsString('data-verzeichnis', $html);
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', Paragraf109SggSeeder::SLUG)->where('locale', 'de')->firstOrFail();
        $block = $seite->blocks()->orderBy('position')->firstOrFail();
        $block->update(['data' => ['titel' => 'Vom Verein geändert.']]);
        $seite->update(['titelbild' => '/storage/eigenes.webp']);

        Paragraf109SggSeeder::anlegen();
        (require database_path('migrations/2026_10_23_280000_projekt_109_sgg.php'))->up();

        $this->assertSame(1, Page::where('slug', Paragraf109SggSeeder::SLUG)->where('locale', 'de')->count());
        $this->assertSame(count(Paragraf109SggSeeder::abschnitte()), $seite->blocks()->count());
        $this->assertSame('Vom Verein geändert.', $block->fresh()->data['titel']);
        $this->assertSame('/storage/eigenes.webp', $seite->fresh()->titelbild);
    }

    /** ANUAS trug bis KEV-161 den Platzhalter; die Migration tauscht nur den. */
    public function test_anuas_bekommt_das_projektbild(): void
    {
        Page::where('slug', 'anuas')->update(['titelbild' => Titelbilder::PLATZHALTER]);

        (require database_path('migrations/2026_10_23_280000_projekt_109_sgg.php'))->up();

        $this->assertSame(['/img/titelbilder/projekte.webp'], Page::where('slug', 'anuas')->pluck('titelbild')->unique()->values()->all());
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en'.self::ADRESSE)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*§ 109 SGG counter-expert opinion\s*<\/h1>/u', $html);
        $this->assertStringContainsString('When a right fails because no expert can be found', $html);
        $this->assertStringContainsString('As of 17 October 2025', $html);
        $this->assertStringContainsString('Rights must not only exist. They must also be within reach.', $html);
    }
}
