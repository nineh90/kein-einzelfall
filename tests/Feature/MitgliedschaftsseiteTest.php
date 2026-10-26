<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\MitgliedschaftsseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Seite „Mitgliedschaft“ nach Taddis Tickets KEV-196 bis KEV-203.
 */
class MitgliedschaftsseiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    private function seite(): Page
    {
        return Page::where('slug', 'mitgliedschaft')->where('locale', 'de')->firstOrFail();
    }

    private function abschnitt(string $titel): ?\App\Models\PageBlock
    {
        return $this->seite()->blocks()->where('typ', 'text')->get()->first(fn ($b) => ($b->data['titel'] ?? null) === $titel);
    }

    /** KEV-199: „Mitglied werden“ mit Taddis Text zur anonymen Mitgliedschaft, ganz zu sehen, mit Knopf zum Antrag. */
    public function test_mitglied_werden_mit_anonymer_mitgliedschaft(): void
    {
        $html = $this->get('/mitgliedschaft')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h2[^>]*>\\s*Mitglied werden\\s*</h2>#', $html);
        $this->assertStringContainsString('Deshalb kannst du bei uns auch anonym Mitglied werden.', $html);
        $this->assertStringContainsString('href="mailto:verwaltung@kein-einzelfall.de"', $html);
        $this->assertStringContainsString('dass sie hinter unserer Arbeit stehen.', $html);
        $this->assertMatchesRegularExpression('#href="'.preg_quote(MitgliedschaftsseiteSeeder::ANTRAG_PDF, '#').'"[^>]*>\\s*Mitgliedsantrag \\(PDF\\)#', $html);

        // Stand der Demo davor: der Text von KEV-94.
        $block = $this->abschnitt(MitgliedschaftsseiteSeeder::ANTRAG_TITEL);
        $block->update(['data' => ['titel' => 'Antrag auf Mitgliedschaft', 'absaetze' => AltseiteSeeder::NEUE_TEXTE['mitgliedschaft']['Antrag auf Mitgliedschaft']]]);

        $migration = require database_path('migrations/2026_10_26_250000_mitgliedschaft_mitglied_werden.php');
        $migration->up();

        $data = $block->fresh()->data;
        $this->assertSame(MitgliedschaftsseiteSeeder::ANTRAG_TITEL, $data['titel']);
        $this->assertSame(MitgliedschaftsseiteSeeder::ANTRAG_ABSAETZE, $data['absaetze']);
        $this->assertTrue($data['alles_zeigen']);
    }

    /**
     * KEV-198 mit KEV-196, KEV-197 und KEV-201: Ausfüllhilfe als eigener Abschnitt; Reihenfolge
     * und Verzeichnis „Wir brauchen dich, Mitglied werden, Ausfüllhilfe, Beitrags- und
     * Mitgliederordnung, Dokumente“.
     */
    public function test_ausfuellhilfe_und_reihenfolge(): void
    {
        $titel = $this->seite()->blocks()->orderBy('position')->get()->map(fn ($b) => $b->data['titel'] ?? null)->all();
        $this->assertSame(['Wir brauchen dich!', 'Mitglied werden', 'Ausfüllhilfe zum Mitgliedsantrag',
            'Beitrags- und Mitgliederordnung', 'Dokumente zum Herunterladen'], $titel);

        $html = $this->get('/mitgliedschaft')->assertOk()->getContent();
        $nav = substr($html, strpos($html, 'data-verzeichnis'));
        $nav = substr($nav, 0, strpos($nav, '</nav>'));
        preg_match_all('#href="\#([^"]+)"#', $nav, $treffer);
        $this->assertSame(['abschnitt-wir-brauchen-dich', 'abschnitt-mitglied-werden', 'abschnitt-ausfullhilfe-zum-mitgliedsantrag',
            'abschnitt-beitrags-und-mitgliederordnung', 'dl-dokumente-zum-herunterladen'], $treffer[1]);

        $this->assertMatchesRegularExpression('#href="'.preg_quote(MitgliedschaftsseiteSeeder::AUSFUELLHILFE_PDF, '#').'"[^>]*>\s*Ausfüllhilfe \(PDF\)#', $html);

        // Die Migration fügt sie nur einmal ein.
        $this->abschnitt('Ausfüllhilfe zum Mitgliedsantrag')->delete();
        $migration = require database_path('migrations/2026_10_26_260000_mitgliedschaft_ausfuellhilfe.php');
        $migration->up();
        $migration->up();
        $titel = $this->seite()->blocks()->orderBy('position')->get()->map(fn ($b) => $b->data['titel'] ?? null)->all();
        $this->assertSame('Ausfüllhilfe zum Mitgliedsantrag', $titel[2]);
        $this->assertCount(5, $titel);
    }

    /** KEV-200: Unter der Beitrags- und Mitgliederordnung ein Knopf zum PDF. */
    public function test_ordnung_hat_einen_knopf(): void
    {
        $this->assertMatchesRegularExpression('#href="'.preg_quote(MitgliedschaftsseiteSeeder::ORDNUNG_PDF, '#').'"[^>]*>\s*Beitrags- und Mitgliederordnung \(PDF\)#',
            $this->get('/mitgliedschaft')->assertOk()->getContent());

        $block = $this->abschnitt('Beitrags- und Mitgliederordnung');
        $block->update(['data' => array_diff_key($block->data, ['cta' => 1])]);
        $migration = require database_path('migrations/2026_10_26_270000_mitgliedschaft_ordnung_knopf.php');
        $migration->up();
        $this->assertSame(MitgliedschaftsseiteSeeder::ORDNUNG_KNOPF, $block->fresh()->data['cta']);
    }

    /** KEV-203: Rechts neben den Dokumenten das Ausweis-Symbol im Kreis, auch per Migration. */
    public function test_ausweis_symbol_neben_den_dokumenten(): void
    {
        $this->assertMatchesRegularExpression(
            '#aria-labelledby="dl-dokumente-zum-herunterladen"(?:(?!</section>).)*<rect x="10.8" y="6.4" width="2.4"#s',
            $this->get('/mitgliedschaft')->assertOk()->getContent());

        $block = $this->seite()->blocks()->where('typ', 'download_list')->first();
        $block->update(['data' => array_diff_key($block->data, ['symbol' => 1])]);
        $migration = require database_path('migrations/2026_10_26_280000_mitgliedschaft_symbol.php');
        $migration->up();
        $this->assertSame('circle-id-card', $block->fresh()->data['symbol']);
    }
}
