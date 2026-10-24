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
            ['Infoabend', 'Wir sind nicht mehr stumm', 'Skillin me Softly', 'Bürokratie-Labyrinth'],
            $termine->pluck('titel')->all(),
        );
        $this->assertSame('veranstaltung', $termine->first()['art']);

        // Berechnete Treffen nur ab heute: Ob Skillin me Softly am 20.10. schon
        // getagt hat, weiß die Rechnung nicht (startet im November, KEV-230).
        $oktober = Termine::imMonat(CarbonImmutable::parse('2026-10-01'));
        $this->assertFalse($oktober->contains(fn ($t) => $t['zeit']->lt(now())));
        // Gruppen in Planung erscheinen nicht
        $this->assertFalse($termine->contains('titel', 'Seelenfarben'));
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
}
