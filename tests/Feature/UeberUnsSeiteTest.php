<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TeamMember;
use App\Support\Titelbilder;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\TeamUndGruppenSeeder;
use Database\Seeders\UnterstuetzungsbandSeeder;
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

    /** KEV-186: Taddis neuer Text bei „Herr und Frau Unbekannt“, auch per Migration. */
    public function test_neuer_text_bei_unbekannt(): void
    {
        $this->get(self::PFAD)->assertOk()
            ->assertSee('Auch wenn sie im Hintergrund wirken, sind sie ein wichtiger Teil unserer Arbeit')
            ->assertDontSee('Zusätzlich arbeiten im Hintergrund');

        $seite = Page::where('slug', 'ueber-uns-vorstand-und-team')->where('locale', 'de')->first();
        $block = $seite->blocks()->where('typ', 'text')->get()->first(fn ($b) => ($b->data['titel'] ?? null) === 'Herr und Frau Unbekannt');
        $block->update(['data' => array_replace($block->data, ['absaetze' => [TeamUndGruppenSeeder::HINTERGRUND_TEXT_ALT]])]);

        $migration = require database_path('migrations/2026_10_26_190000_unbekannt_neuer_text.php');
        $migration->up();
        $this->assertSame([TeamUndGruppenSeeder::HINTERGRUND_TEXT], $block->fresh()->data['absaetze']);
    }

    /** KEV-187: Die Kurzfassungen enden nach einem ganzen Satz, nicht mitten im Wort. */
    public function test_kurzfassung_endet_nach_ganzem_satz(): void
    {
        foreach (TeamMember::all() as $person) {
            $this->assertDoesNotMatchRegularExpression('/\p{L}(\.\.\.|…)$/u', (string) $person->kurzprofil, $person->name);
        }

        $unbekannt = TeamMember::where('name', 'Herr und Frau Unbekannt')->first();
        $this->assertStringEndsWith('weil ihre Realität es nicht anders zulässt.', $unbekannt->kurzprofil);
        $this->assertStringStartsWith('<p>Sie leben mit Schutzmechanismen', $unbekannt->profilFortsetzung());

        // Zu langer erster Satz: an der Wortgrenze, mit „…“.
        $this->assertSame('Ein sehr langer Satz ohne Ende…', TeamUndGruppenSeeder::kurzfassung('Ein sehr langer Satz ohne Ende und mehr', 31));

        // Die Migration kürzt einen alten, mitten im Wort abgeschnittenen Stand neu.
        $tanja = TeamMember::where('name', 'Tanja Häfner')->first();
        $richtig = $tanja->kurzprofil;
        $tanja->update(['kurzprofil' => mb_substr($richtig, 0, 120).'...']);
        $migration = require database_path('migrations/2026_10_26_200000_team_kurzfassung_ganze_saetze.php');
        $migration->up();
        $this->assertSame($richtig, $tanja->fresh()->kurzprofil);
    }

    /** KEV-188: Taddis Text mit Absätzen und „– DANKE! Herr und Frau Unbekannt“, auch per Migration. */
    public function test_unbekannt_mit_absaetzen_und_dank(): void
    {
        $person = TeamMember::where('name', 'Herr und Frau Unbekannt')->first();
        $this->assertSame(count(TeamUndGruppenSeeder::UNBEKANNT_ABSAETZE), substr_count($person->profil, '<p>'));

        $html = $this->get(self::PFAD)->assertOk()->getContent();
        $this->assertStringContainsString('– DANKE! Herr und Frau Unbekannt</p>', $html);
        $this->assertStringContainsString('<p>Für mehr Sichtbarkeit, Gehör und Unterstützung.</p>', $html);

        // Stand davor: ein Absatz, ohne Unterschrift.
        $person->update([
            'profil' => '<p>'.implode(' ', TeamUndGruppenSeeder::UNBEKANNT_ABSAETZE).'</p>',
            'signatur' => null,
            'uebersetzungen' => ['en' => ['profil' => '<p>Old.</p>']],
        ]);
        $migration = require database_path('migrations/2026_10_26_210000_unbekannt_absaetze_und_unterschrift.php');
        $migration->up();

        $person = $person->fresh();
        $this->assertSame(TeamUndGruppenSeeder::UNBEKANNT_SIGNATUR, $person->signatur);
        $this->assertStringEndsWith('nicht anders zulässt.', $person->kurzprofil);
        $this->assertSame('THANK YOU! Mr and Mrs Anonymous', $person->uebersetzungen['en']['signatur']);
        $this->assertStringStartsWith('<p>The voices of courage', $person->uebersetzungen['en']['profil']);
    }

    /** KEV-189: Spendenkasten der Startseite statt des grünen Bands, auch per Migration. */
    public function test_spendenkasten_statt_band(): void
    {
        $html = $this->get(self::PFAD)->assertOk()->getContent();
        $this->assertStringContainsString('IBAN kopieren', $html);
        // Ohne Kopf: Im Handy-Menü steht derselbe Satz als Unterzeile von „Spenden“.
        $this->assertStringNotContainsString('Sei Du dabei, jede Unterstützung zählt', substr($html, strpos($html, '<main')));

        $seite = Page::where('slug', 'ueber-uns-vorstand-und-team')->where('locale', 'de')->first();
        $position = $seite->blocks()->where('typ', 'donation_options')->value('position');
        $seite->blocks()->where('typ', 'donation_options')->delete();
        $seite->blocks()->create(['typ' => 'cta_band', 'position' => $position, 'data' => UnterstuetzungsbandSeeder::BAND]);

        $migration = require database_path('migrations/2026_10_26_220000_team_spendenkasten.php');
        $migration->up();
        $this->assertSame('donation_options', $seite->blocks()->get()->sortBy('position')->last()->typ);
        $this->assertFalse($seite->blocks()->where('typ', 'cta_band')->exists());
    }

    /** KEV-190: Unten dasselbe 3 × 3 wie auf der Vereinsseite, die eigene Seite markiert statt verlinkt. */
    public function test_kacheln_wie_auf_der_vereinsseite(): void
    {
        $html = $this->get(self::PFAD)->assertOk()->getContent();
        $aside = substr($html, strpos($html, 'id="weiterlesen-titel"'));
        $aside = substr($aside, 0, strpos($aside, '</aside>'));

        $this->assertSame(9, substr_count($aside, '<li class="flex">'));
        $this->assertStringContainsString('lg:grid-flow-col lg:grid-rows-3', $aside);
        $this->assertMatchesRegularExpression('#<span aria-current="page"[^>]*>\s*<span[^>]*>Vorstand und Team</span>#', $aside);
        $this->assertStringNotContainsString('href="/ueber-uns-vorstand-und-team"', $aside);
        $this->assertStringContainsString('href="/satzung"', $aside);
    }
}
