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
}
