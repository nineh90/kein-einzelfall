<?php

namespace Tests\Feature;

use App\Models\Group;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die regelmässigen Gruppentreffen standen bisher nur als Freitext auf den
 * Gruppenseiten. Wer wissen wollte, wann das nächste Treffen ist, musste die
 * Seite durchsuchen. Jetzt werden sie berechnet und erscheinen im Kalender.
 */
class GruppenterminTest extends TestCase
{
    use RefreshDatabase;

    private function gruppe(array $daten = []): Group
    {
        static $nr = 0;
        $nr++;

        return Group::create(array_merge([
            'slug' => 'gruppe-'.$nr,
            'name' => 'Testgruppe '.$nr,
            'typ' => 'selbsthilfe',
            'status' => 'offen',
            'published_at' => now()->subDay(),
        ], $daten));
    }

    public function test_monatlicher_termin_wird_richtig_berechnet(): void
    {
        // „Jeden 4. Mittwoch im Monat, 19:00 Uhr"
        $gruppe = $this->gruppe([
            'wiederholung' => 'monatlich_nter_wochentag',
            'wochentag' => 3,
            'woche_im_monat' => 4,
            'beginn_zeit' => '19:00',
        ]);

        $termine = $gruppe->naechsteTermine(3, CarbonImmutable::parse('2026-08-01 00:00'));

        $this->assertCount(3, $termine);

        // Vierter Mittwoch im August 2026 ist der 26.
        $this->assertSame('2026-08-26 19:00', $termine[0]->format('Y-m-d H:i'));
        $this->assertSame('2026-09-23 19:00', $termine[1]->format('Y-m-d H:i'));

        foreach ($termine as $termin) {
            $this->assertSame(3, $termin->dayOfWeekIso, 'Muss ein Mittwoch sein');
        }
    }

    public function test_letzter_wochentag_im_monat_funktioniert_auch_bei_vier_wochen(): void
    {
        // 5 bedeutet „der letzte im Monat" — nicht jeder Monat hat fünf.
        $gruppe = $this->gruppe([
            'wiederholung' => 'monatlich_nter_wochentag',
            'wochentag' => 1,
            'woche_im_monat' => 5,
        ]);

        $termine = $gruppe->naechsteTermine(2, CarbonImmutable::parse('2026-02-01'));

        $this->assertNotEmpty($termine);
        // Februar 2026 hat vier Montage, der letzte ist der 23.
        $this->assertSame('2026-02-23', $termine[0]->format('Y-m-d'));
    }

    public function test_woechentlicher_termin_wird_berechnet(): void
    {
        $gruppe = $this->gruppe([
            'wiederholung' => 'woechentlich',
            'wochentag' => 2,
            'beginn_zeit' => '18:00',
        ]);

        $termine = $gruppe->naechsteTermine(3, CarbonImmutable::parse('2026-08-01'));

        $this->assertCount(3, $termine);
        foreach ($termine as $termin) {
            $this->assertSame(2, $termin->dayOfWeekIso);
        }
        // Sieben Tage Abstand. diffInDays liefert einen Fliesskommawert,
        // deshalb nicht auf den Typ prüfen.
        $this->assertEqualsWithDelta(7, $termine[0]->diffInDays($termine[1]), 0.01);
    }

    public function test_gruppen_ohne_rhythmus_liefern_keine_termine(): void
    {
        $gruppe = $this->gruppe(['rhythmus' => 'nach Absprache']);

        $this->assertTrue($gruppe->naechsteTermine()->isEmpty());
    }

    public function test_termine_erscheinen_im_kalender(): void
    {
        // Der eigentliche Zweck: sichtbar, ohne die Gruppenseite zu durchsuchen.
        $this->gruppe([
            'name' => 'Monatlicher Austausch',
            'wiederholung' => 'monatlich_nter_wochentag',
            'wochentag' => 3,
            'woche_im_monat' => 2,
            'beginn_zeit' => '19:00',
        ]);

        $this->get('/veranstaltungen')
            ->assertOk()
            ->assertSee('Regelmässige Gruppentreffen')
            ->assertSee('Monatlicher Austausch');
    }

    public function test_geschlossene_gruppen_erscheinen_nicht_im_kalender(): void
    {
        $this->gruppe([
            'name' => 'Geplante Runde',
            'status' => 'geplant',
            'wiederholung' => 'woechentlich',
            'wochentag' => 1,
        ]);

        $this->get('/veranstaltungen')->assertDontSee('Geplante Runde');
    }

    public function test_gruppentermine_stehen_auch_im_kalender_export(): void
    {
        $this->gruppe([
            'name' => 'Offener Abend',
            'wiederholung' => 'woechentlich',
            'wochentag' => 4,
            'beginn_zeit' => '18:00',
            'dauer_minuten' => 90,
        ]);

        $ics = $this->get('/veranstaltungen/kalender.ics')->getContent();

        $this->assertStringContainsString('SUMMARY:Offener Abend', $ics);
        // Eigene Kennung je Datum, damit beim erneuten Import keine Dubletten
        // entstehen — es gibt keinen Datensatz pro Termin.
        $this->assertMatchesRegularExpression('/UID:gruppe-\d+-\d{8}@/', $ics);
    }

    public function test_kalender_zeigt_keine_vergangenen_gruppentermine(): void
    {
        $this->gruppe([
            'name' => 'Wochenrunde',
            'wiederholung' => 'woechentlich',
            'wochentag' => 1,
        ]);

        // In der Rückschau sind wiederkehrende Termine nicht sinnvoll
        $this->get('/veranstaltungen?zeitraum=vergangen')
            ->assertDontSee('Regelmässige Gruppentreffen');
    }
}
