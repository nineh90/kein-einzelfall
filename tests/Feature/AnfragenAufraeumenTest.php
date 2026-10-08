<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Art. 5 Abs. 1 lit. e DSGVO: nur so lange speichern wie nötig.
 *
 * Ein Löschkonzept, das an Disziplin hängt, ist keins — deshalb läuft es
 * automatisch und wird hier festgehalten.
 */
class AnfragenAufraeumenTest extends TestCase
{
    use RefreshDatabase;

    private function anfrage(array $daten = []): Inquiry
    {
        return Inquiry::create(array_merge([
            'betreff' => 'Betreff',
            'nachricht' => 'Eine Nachricht mit ausreichend Text.',
        ], $daten));
    }

    public function test_erledigte_anfragen_werden_nach_ablauf_der_frist_geloescht(): void
    {
        $frist = config('anfragen.aufbewahrung_tage_erledigt');

        $alt = $this->anfrage(['status' => 'erledigt', 'erledigt_at' => now()->subDays($frist + 1)]);
        $frisch = $this->anfrage(['status' => 'erledigt', 'erledigt_at' => now()->subDays(1)]);

        $this->artisan('anfragen:aufraeumen')->assertSuccessful();

        $this->assertDatabaseMissing('inquiries', ['id' => $alt->id]);
        $this->assertDatabaseHas('inquiries', ['id' => $frisch->id]);
    }

    public function test_unbearbeitete_anfragen_bekommen_eine_laengere_frist(): void
    {
        // Es wäre schlimm, jemandem die Nachricht zu löschen, bevor sie
        // überhaupt jemand gelesen hat.
        $kurzFrist = config('anfragen.aufbewahrung_tage_erledigt');
        $langFrist = config('anfragen.aufbewahrung_tage_offen');

        $this->assertGreaterThan($kurzFrist, $langFrist);

        $offenMittelalt = $this->anfrage(['status' => 'offen']);
        $offenMittelalt->forceFill(['created_at' => now()->subDays($kurzFrist + 5)])->saveQuietly();

        $offenSehrAlt = $this->anfrage(['status' => 'offen']);
        $offenSehrAlt->forceFill(['created_at' => now()->subDays($langFrist + 1)])->saveQuietly();

        $this->artisan('anfragen:aufraeumen')->assertSuccessful();

        $this->assertDatabaseHas('inquiries', ['id' => $offenMittelalt->id]);
        $this->assertDatabaseMissing('inquiries', ['id' => $offenSehrAlt->id]);
    }

    public function test_probelauf_loescht_nichts(): void
    {
        $frist = config('anfragen.aufbewahrung_tage_erledigt');
        $alt = $this->anfrage(['status' => 'erledigt', 'erledigt_at' => now()->subDays($frist + 1)]);

        $this->artisan('anfragen:aufraeumen --probe')->assertSuccessful();

        $this->assertDatabaseHas('inquiries', ['id' => $alt->id]);
    }

    public function test_aufraeumen_ist_taeglich_eingeplant(): void
    {
        // Ohne Eintrag im Zeitplan läuft das Löschkonzept nie.
        $eingeplant = collect(app(Schedule::class)->events())
            ->contains(fn ($e) => str_contains($e->command ?? '', 'anfragen:aufraeumen'));

        $this->assertTrue($eingeplant, 'anfragen:aufraeumen ist nicht im Zeitplan eingetragen');
    }

    /** Prüfung der Firma (08.10.2026): Stand und Abschlussdatum passen immer zusammen. */
    public function test_abschlussdatum_folgt_dem_stand(): void
    {
        $ohneDatum = $this->anfrage(['status' => 'erledigt', 'erledigt_at' => null]);
        $this->assertNotNull($ohneDatum->erledigt_at);

        $zukunft = $this->anfrage(['status' => 'erledigt', 'erledigt_at' => now()->addYear()]);
        $this->assertFalse($zukunft->erledigt_at->isFuture());

        $wiederOffen = $this->anfrage(['status' => 'erledigt']);
        $wiederOffen->update(['status' => 'offen']);
        $this->assertNull($wiederOffen->fresh()->erledigt_at);
    }

    public function test_altfaelle_ohne_abschlussdatum_werden_trotzdem_geloescht(): void
    {
        $frist = config('anfragen.aufbewahrung_tage_erledigt');
        $alt = $this->anfrage(['status' => 'erledigt']);

        // Der Zustand vor dem 08.10.2026, am Model vorbei.
        \Illuminate\Support\Facades\DB::table('inquiries')->where('id', $alt->id)
            ->update(['erledigt_at' => null, 'updated_at' => now()->subDays($frist + 1)]);

        $this->artisan('anfragen:aufraeumen')->assertSuccessful();

        $this->assertDatabaseMissing('inquiries', ['id' => $alt->id]);
    }
}
