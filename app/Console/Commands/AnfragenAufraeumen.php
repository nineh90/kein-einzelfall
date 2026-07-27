<?php

namespace App\Console\Commands;

use App\Models\Inquiry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Löscht Anfragen, deren Aufbewahrungsfrist abgelaufen ist.
 *
 * Art. 5 Abs. 1 lit. e DSGVO: personenbezogene Daten nur so lange speichern,
 * wie es für den Zweck nötig ist. Der Zweck ist die Bearbeitung der Anfrage —
 * danach gibt es keinen Grund mehr, Angaben zu Straftaten und Gesundheit
 * aufzubewahren.
 *
 * Automatisch statt „machen wir regelmäßig von Hand": Ein Löschkonzept, das an
 * Disziplin hängt, ist keins.
 *
 * Die Frist ist mit dem Verein abzustimmen und steht in config/anfragen.php.
 */
class AnfragenAufraeumen extends Command
{
    protected $signature = 'anfragen:aufraeumen {--probe : Nur anzeigen, nichts löschen}';

    protected $description = 'Anfragen nach Ablauf der Aufbewahrungsfrist löschen';

    public function handle(): int
    {
        $tageErledigt = config('anfragen.aufbewahrung_tage_erledigt');
        $tageOffen = config('anfragen.aufbewahrung_tage_offen');

        // Erledigte Anfragen: Frist läuft ab dem Abschluss.
        $erledigt = Inquiry::where('status', 'erledigt')
            ->whereNotNull('erledigt_at')
            ->where('erledigt_at', '<=', now()->subDays($tageErledigt));

        // Unbearbeitete Anfragen bekommen eine längere Frist ab Eingang —
        // sonst löschen wir jemandem die Nachricht weg, bevor sie gelesen wurde.
        $unerledigt = Inquiry::where('status', '!=', 'erledigt')
            ->where('created_at', '<=', now()->subDays($tageOffen));

        $anzahlErledigt = $erledigt->count();
        $anzahlOffen = $unerledigt->count();

        if ($this->option('probe')) {
            $this->info('Probelauf — es würde gelöscht:');
            $this->line("  {$anzahlErledigt} erledigte (älter als {$tageErledigt} Tage nach Abschluss)");
            $this->line("  {$anzahlOffen} unerledigte (älter als {$tageOffen} Tage)");

            return self::SUCCESS;
        }

        $erledigt->delete();
        $unerledigt->delete();

        $gesamt = $anzahlErledigt + $anzahlOffen;

        if ($gesamt > 0) {
            // Für den Nachweis, dass das Löschkonzept greift — ohne Inhalte
            // oder Kennungen zu protokollieren.
            Log::info('Aufbewahrungsfrist: Anfragen gelöscht', [
                'erledigt' => $anzahlErledigt,
                'unerledigt' => $anzahlOffen,
            ]);
        }

        $this->info("{$gesamt} Anfragen gelöscht ({$anzahlErledigt} erledigt, {$anzahlOffen} unerledigt).");

        return self::SUCCESS;
    }
}
