<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    private function termin(array $daten = []): Event
    {
        static $nr = 0;
        $nr++;

        return Event::create(array_merge([
            'slug' => 'termin-'.$nr,
            'titel' => 'Termin '.$nr,
            'beginnt_am' => now()->addWeek(),
            'published_at' => now()->subDay(),
        ], $daten));
    }

    public function test_uebersicht_zeigt_kommende_termine(): void
    {
        $kommend = $this->termin(['titel' => 'Kommender Termin']);
        $vergangen = $this->termin(['titel' => 'Alter Termin', 'beginnt_am' => now()->subMonth()]);

        $this->get('/veranstaltungen')
            ->assertOk()
            ->assertSee($kommend->titel)
            ->assertDontSee($vergangen->titel);
    }

    public function test_vergangene_termine_bleiben_erreichbar(): void
    {
        $vergangen = $this->termin(['titel' => 'Alter Termin', 'beginnt_am' => now()->subMonth()]);

        $this->get('/veranstaltungen?zeitraum=vergangen')
            ->assertOk()
            ->assertSee($vergangen->titel);
    }

    /**
     * Massgeblich ist das Ende, nicht der Beginn: Eine mehrtägige Veranstaltung
     * soll am zweiten Tag noch als laufend gelten.
     */
    public function test_laufende_mehrtaegige_termine_gelten_als_kommend(): void
    {
        $laufend = $this->termin([
            'titel' => 'Laufende Veranstaltung',
            'beginnt_am' => now()->subDay(),
            'endet_am' => now()->addDay(),
        ]);

        $this->assertTrue($laufend->laeuftGerade());
        $this->get('/veranstaltungen')->assertSee($laufend->titel);
    }

    public function test_entwuerfe_sind_nicht_sichtbar(): void
    {
        $entwurf = $this->termin(['published_at' => null]);

        $this->get('/veranstaltungen')->assertDontSee($entwurf->titel);
        $this->get('/veranstaltungen/'.$entwurf->slug)->assertNotFound();
    }

    public function test_bestandstext_der_altseite_bleibt_ueber_dem_kalender(): void
    {
        // /veranstaltungen war eine gepflegte Inhaltsseite (443 Wörter).
        // Der Text soll durch den Kalender nicht verlorengehen.
        $this->seed(\Database\Seeders\AltseiteSeeder::class);

        $this->get('/veranstaltungen')
            ->assertOk()
            ->assertSee('Wissen teilen', false);
    }

    public function test_ical_export_liefert_einen_gueltigen_kalender(): void
    {
        // Die Altseite bietet /events/?ical=1 an — die Möglichkeit soll bleiben.
        $termin = $this->termin(['titel' => 'Gruppentreffen', 'ort' => 'Hamburg']);

        $antwort = $this->get('/veranstaltungen/kalender.ics');

        $antwort->assertOk();
        $antwort->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

        $ics = $antwort->getContent();
        $this->assertStringStartsWith('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('BEGIN:VEVENT', $ics);
        $this->assertStringContainsString('SUMMARY:Gruppentreffen', $ics);
        $this->assertStringContainsString('END:VCALENDAR', $ics);

        // RFC 5545 schreibt CRLF vor — manche Kalenderprogramme sind da streng.
        $this->assertStringContainsString("\r\n", $ics);
    }

    public function test_ical_maskiert_sonderzeichen(): void
    {
        // Unmaskierte Kommas und Semikola zerlegen die Datei.
        $this->termin(['titel' => 'Vortrag: Recht, Fristen; Antraege']);

        $ics = $this->get('/veranstaltungen/kalender.ics')->getContent();

        $this->assertStringContainsString('Recht\, Fristen\; Antraege', $ics);
    }

    public function test_einzelner_termin_laesst_sich_exportieren(): void
    {
        $termin = $this->termin(['titel' => 'Einzeltermin']);

        $ics = $this->get('/veranstaltungen/'.$termin->slug.'/kalender.ics');

        $ics->assertOk();
        $this->assertStringContainsString('SUMMARY:Einzeltermin', $ics->getContent());
    }

    public function test_ganztaegige_termine_werden_als_datum_exportiert(): void
    {
        $this->termin([
            'titel' => 'Ganztagsveranstaltung',
            'ganztaegig' => true,
            'beginnt_am' => now()->addWeek()->startOfDay(),
        ]);

        $ics = $this->get('/veranstaltungen/kalender.ics')->getContent();

        $this->assertStringContainsString('DTSTART;VALUE=DATE:', $ics);
    }

    public function test_termin_bringt_strukturierte_daten_mit(): void
    {
        $termin = $this->termin(['ort' => 'Hamburg']);

        $html = $this->get('/veranstaltungen/'.$termin->slug)->getContent();

        $this->assertStringContainsString('"@type":"Event"', $html);
        $this->assertStringContainsString('"@type":"Place"', $html);
    }

    public function test_zeitraum_wird_lesbar_dargestellt(): void
    {
        $eintaegig = $this->termin([
            'beginnt_am' => now()->addWeek()->setTime(18, 0),
            'endet_am' => now()->addWeek()->setTime(20, 0),
        ]);
        $this->assertStringContainsString('bis 20:00 Uhr', $eintaegig->zeitraum());

        $ganztag = $this->termin([
            'ganztaegig' => true,
            'beginnt_am' => now()->addWeek()->startOfDay(),
        ]);
        $this->assertStringNotContainsString('Uhr', $ganztag->zeitraum());
    }
}
