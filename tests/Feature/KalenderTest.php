<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Group;
use App\Models\Page;
use App\Support\Termine;
use Carbon\CarbonImmutable;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-224: Termin-Kalender rechts neben der Einleitung von
 * /gruppen-und-veranstaltungen, mit Veranstaltungen und Gruppentreffen.
 */
class KalenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
        // Auf den Tag des Tickets, damit die Termine feststehen. Alles davor veröffentlicht.
        Group::query()->update(['published_at' => '2026-01-01 00:00:00']);
        Page::query()->update(['published_at' => '2026-01-01 00:00:00']);
        $this->travelTo(CarbonImmutable::parse('2026-10-24 12:00', 'Europe/Berlin'));
    }

    public function test_kalender_neben_der_einleitung(): void
    {
        $seite = Page::where('slug', 'gruppen-und-veranstaltungen')->where('locale', 'de')->firstOrFail();
        $this->assertSame(['text', 'kalender'], $seite->blocks()->orderBy('position')->pluck('typ')->take(2)->all());

        $html = $this->get('/gruppen-und-veranstaltungen')->assertOk()->getContent();

        // Ein Abschnitt: Einleitung links, Kalender rechts
        $this->assertMatchesRegularExpression('#lg:grid-cols-\\[minmax\\(0,65ch\\)_minmax\\(0,1fr\\)\\].*?Wo Menschen zusammenkommen.*?id="kalender"#s', $html);
        $this->assertStringContainsString('Oktober 2026', $html);
        // Bürokratie-Labyrinth trifft sich am 4. Mittwoch, 28.10., 19 Uhr
        $this->assertStringContainsString('datetime="2026-10-28T19:00:00+01:00"', $html);
        $this->assertStringContainsString('Bürokratie-Labyrinth', $html);
        $this->assertStringContainsString('href="/veranstaltungen/kalender.ics"', preg_replace('#http://localhost#', '', $html));
        // Das Raster ist für Vorlesehilfen ausgeblendet, die Liste trägt die Termine
        $this->assertStringContainsString('<div aria-hidden="true" class="grid grid-cols-7', $html);
    }

    public function test_monate_blaettern_mit_grenzen(): void
    {
        $this->get('/gruppen-und-veranstaltungen?monat=2026-11')->assertOk()
            ->assertSee('November 2026')
            // Wir sind nicht mehr stumm (2. Mi), Skillin me Softly (3. Di), Bürokratie-Labyrinth (4. Mi)
            ->assertSee('datetime="2026-11-11T19:00:00+01:00"', false)
            ->assertSee('datetime="2026-11-17T11:00:00+01:00"', false)
            ->assertSee('datetime="2026-11-25T19:00:00+01:00"', false)
            ->assertSee('?monat=2026-10#kalender', false);

        // Nicht in die Vergangenheit, höchstens elf Monate voraus, Unsinn wird ignoriert
        $this->get('/gruppen-und-veranstaltungen?monat=2025-01')->assertSee('Oktober 2026')->assertDontSee('Vorheriger Monat', false);
        $this->get('/gruppen-und-veranstaltungen?monat=2030-01')->assertSee('September 2027')->assertDontSee('Nächster Monat', false);
        $this->get('/gruppen-und-veranstaltungen?monat=abc')->assertOk()->assertSee('Oktober 2026');
    }

    public function test_termine_aus_veranstaltungen_und_gruppen(): void
    {
        Event::create([
            'slug' => 'infoabend', 'titel' => 'Infoabend', 'beginnt_am' => '2026-11-05 18:00',
            'published_at' => now()->subDay(),
        ]);

        $termine = Termine::imMonat(CarbonImmutable::parse('2026-11-01'));
        $this->assertSame(
            ['Infoabend', 'Seelenfarben', 'Wir sind nicht mehr stumm', 'Skillin me Softly', 'Bürokratie-Labyrinth'],
            $termine->pluck('titel')->all(),
        );
        $this->assertSame('veranstaltung', $termine->first()['art']);

        // Berechnete Treffen nur ab heute: Ob Skillin me Softly am 20.10. schon
        // getagt hat, weiß die Rechnung nicht (startet im November, KEV-230).
        $oktober = Termine::imMonat(CarbonImmutable::parse('2026-10-01'));
        $this->assertFalse($oktober->contains(fn ($t) => $t['zeit']->lt(now())));
        // Gruppen in Planung erscheinen nicht
        $this->assertFalse($termine->contains('titel', 'Zwischen den Zeilen'));
    }

    public function test_englisch(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $this->get('/en/gruppen-und-veranstaltungen')->assertOk()
            ->assertSee('October 2026')
            ->assertSee('Dates')
            ->assertSee('Add dates to your own calendar')
            ->assertSee('Self-help group');
    }

    /** KEV-227, KEV-281 (Nils): Jede Selbsthilfegruppe hat ihren eigenen Kalender. */
    public function test_kalender_auf_der_gruppenseite_nur_mit_ihren_terminen(): void
    {
        $gruppe = Group::where('slug', 'wir-sind-nicht-mehr-stumm')->firstOrFail();

        $termine = Termine::imMonat(CarbonImmutable::parse('2026-11-01'), $gruppe);
        $this->assertSame(['Wir sind nicht mehr stumm'], $termine->pluck('titel')->unique()->all());

        $html = $this->get($gruppe->url())->assertOk()->getContent();
        preg_match('#<section id="kalender".*?</section>#s', $html, $kalender);
        $this->assertStringContainsString('Nächste Termine', $kalender[0]);
        // Die nächsten drei Treffen am 2. Mittwoch, ohne Link auf die Seite selbst
        foreach (['2026-11-11', '2026-12-09', '2027-01-13'] as $tag) {
            $this->assertStringContainsString('datetime="'.$tag.'T19:00:00+01:00"', $kalender[0]);
        }
        $this->assertStringNotContainsString('Bürokratie-Labyrinth', $kalender[0]);
        $this->assertStringNotContainsString('href="'.$gruppe->url().'"', $kalender[0]);
        $this->assertStringContainsString('Alle Termine des Vereins', $kalender[0]);
        // Vor der Anmeldung
        $this->assertLessThan(strpos($html, 'id="anmeldung"'), strpos($html, 'id="kalender"'));

        // Geplante Gruppen haben noch keine Termine und keinen Kalender
        $geplant = Group::where('slug', 'schreibwerkstatt')->firstOrFail();
        $this->get($geplant->url())->assertOk()->assertDontSee('id="kalender"', false);
    }

    /** Ohne gewählten Monat öffnet der Kalender den Monat mit dem nächsten Termin. */
    public function test_ohne_wahl_der_monat_mit_dem_naechsten_termin(): void
    {
        // Am 29.10. steht im Oktober nichts mehr an (letztes Treffen am 28.)
        $this->travelTo(CarbonImmutable::parse('2026-10-29 12:00', 'Europe/Berlin'));

        $this->assertSame('2026-11', Termine::monatMitNaechstemTermin()->format('Y-m'));
        $this->get('/gruppen-und-veranstaltungen')->assertSee('November 2026')->assertDontSee('In diesem Monat stehen keine weiteren Termine an.');

        // Gewählt bleibt gewählt
        $this->get('/gruppen-und-veranstaltungen?monat=2026-10')->assertSee('Oktober 2026');
    }

    /** KEV-281 (Nils): Auf der Startseite und unter Veranstaltungen alle Termine gebündelt. */
    public function test_alle_termine_auf_startseite_und_veranstaltungen(): void
    {
        $startseite = Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->firstOrFail();
        $typen = $startseite->blocks()->orderBy('position')->pluck('typ')->all();
        $this->assertSame(array_search('hilfe_box', $typen) - 1, array_search('kalender', $typen));

        foreach (['/', '/veranstaltungen'] as $pfad) {
            $html = $this->get($pfad)->assertOk()->getContent();
            preg_match('#<section id="kalender".*?</section>#s', $html, $kalender);
            $this->assertNotEmpty($kalender, $pfad);
            // Raster und Liste nebeneinander, mit allen Gruppen
            $this->assertStringContainsString('lg:grid-cols-[minmax(0,26rem)_minmax(0,1fr)]', $kalender[0], $pfad);
            $this->assertStringContainsString('Bürokratie-Labyrinth', $kalender[0], $pfad);
        }

        // Auf der Startseite ohne Strich, wie alle Abschnitte dort (KEV-170)
        $this->assertStringContainsString('Unsere nächsten Termine', $this->get('/')->getContent());
        // Bei den vergangenen Veranstaltungen kein Kalender
        $this->get('/veranstaltungen?zeitraum=vergangen')->assertDontSee('id="kalender"', false);

        // Bestehende Startseiten bekommen ihn per Migration, einmal
        $startseite->blocks()->where('typ', 'kalender')->delete();
        $migration = require database_path('migrations/2026_10_29_150000_startseite_termin_kalender.php');
        $migration->up();
        $migration->up();
        $this->assertSame(1, $startseite->blocks()->where('typ', 'kalender')->count());
        $typen = $startseite->blocks()->orderBy('position')->pluck('typ')->all();
        $this->assertSame(array_search('hilfe_box', $typen) - 1, array_search('kalender', $typen));
    }
}
