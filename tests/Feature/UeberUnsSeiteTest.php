<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TeamMember;
use App\Support\Titelbilder;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Seite „Über uns – Vorstand und Team“ nach Taddis Tickets KEV-179 bis
 * KEV-191.
 */
class UeberUnsSeiteTest extends TestCase
{
    use RefreshDatabase;

    private const PFAD = '/ueber-uns-vorstand-und-team';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    /** KEV-181: Das Logo liegt als eigenes Element auf dem Bild, wie bei Verein. */
    public function test_logo_liegt_auf_dem_titelbild(): void
    {
        $this->assertTrue(Titelbilder::logoDarauf(Titelbilder::datei('ueber-uns-vorstand-und-team')));
        Page::where('slug', 'ueber-uns-vorstand-und-team')->update(['titelbild' => Titelbilder::datei('ueber-uns-vorstand-und-team')]);

        $this->get(self::PFAD)->assertOk()->assertSee('data-kopf-logo', false);
    }

    /** KEV-179: Der Titel der Seite bleibt lang, überall sonst „Vorstand und Team“. */
    public function test_ueberall_ausser_im_titel_vorstand_und_team(): void
    {
        $html = $this->get(self::PFAD)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h1[^>]*>\s*Über uns – Vorstand und Team\s*</h1>#u', $html);
        $this->assertStringContainsString('aria-current="page" class="text-ink">Vorstand und Team<', $html);
        $this->assertSame(1, substr_count(preg_replace('#<head>.*</head>#s', '', $html), 'Über uns – Vorstand und Team'));

        // Im Menü und in den Kacheln anderer Seiten kurz.
        $satzung = $this->get('/satzung')->assertOk()->getContent();
        $this->assertStringNotContainsString('Über uns – Vorstand und Team', $satzung);
        $this->assertMatchesRegularExpression('#href="/ueber-uns-vorstand-und-team"[^>]*>\s*Vorstand und Team\s*<#', $satzung);
    }

    /** KEV-183: Unter jedem Text rechtsbündig „– Name“, oder die eigene Unterschrift aus dem Panel. */
    public function test_unterschrift_unter_jedem_text(): void
    {
        $html = $this->get(self::PFAD)->assertOk()->getContent();

        foreach (TeamMember::veroeffentlicht()->where('signatur', null)->pluck('name') as $name) {
            $this->assertStringContainsString('<p class="text-right text-sm text-ink">– '.e($name).'</p>', $html, $name);
        }

        TeamMember::where('name', 'Tatjana Belmar')->update(['signatur' => 'Eure Tatjana']);
        $this->get(self::PFAD)->assertSee('– Eure Tatjana</p>', false)->assertDontSee('– Tatjana Belmar</p>', false);
    }

    /** KEV-184: Rechts neben „Team“ das Symbol mit den Menschen im Kreis, auch per Migration. */
    public function test_symbol_neben_team(): void
    {
        $html = $this->get(self::PFAD)->assertOk()->getContent();
        $this->assertMatchesRegularExpression(
            '#<h2[^>]*>\s*Team\s*</h2>(?:(?!</section>).)*<circle cx="17" cy="10.4" r="1.8"/>#s', $html);

        $seite = Page::where('slug', 'ueber-uns-vorstand-und-team')->where('locale', 'de')->first();
        $block = $seite->blocks()->where('typ', 'text')->get()->first(fn ($b) => ($b->data['titel'] ?? null) === 'Team');
        $daten = $block->data;
        unset($daten['symbol']);
        $block->update(['data' => $daten]);

        $migration = require database_path('migrations/2026_10_26_170000_team_symbol.php');
        $migration->up();
        $this->assertSame('circle-users', $block->fresh()->data['symbol']);
    }

    /** KEV-185: „Herr und Frau Unbekannt“ linksbündig, rechts das Fragezeichen im Kreis. */
    public function test_unbekannt_linksbuendig_mit_fragezeichen(): void
    {
        $seite = Page::where('slug', 'ueber-uns-vorstand-und-team')->where('locale', 'de')->first();
        $block = $seite->blocks()->where('typ', 'text')->get()->first(fn ($b) => ($b->data['titel'] ?? null) === 'Herr und Frau Unbekannt');
        $this->assertSame('circle-help', $block->data['symbol']);
        $this->assertArrayNotHasKey('mittig', $block->data);

        $this->assertMatchesRegularExpression(
            '#<h2[^>]*>\s*Herr und Frau Unbekannt\s*</h2>(?:(?!</section>).)*<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>#s',
            $this->get(self::PFAD)->assertOk()->getContent());

        // Stand davor: mittig, ohne Symbol.
        $block->update(['data' => array_diff_key($block->data, ['symbol' => 1]) + ['mittig' => true]]);
        $migration = require database_path('migrations/2026_10_26_180000_unbekannt_symbol.php');
        $migration->up();
        $this->assertSame('circle-help', $block->fresh()->data['symbol']);
        $this->assertArrayNotHasKey('mittig', $block->fresh()->data);
    }
}
