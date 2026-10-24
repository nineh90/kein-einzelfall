<?php

namespace App\Console\Commands;

use App\Models\GroupRegistration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Löscht Anmeldungen zu Selbsthilfegruppen nach Ablauf der Frist (KEV-226).
 *
 * Wie AnfragenAufraeumen: Art. 5 Abs. 1 lit. e DSGVO, automatisch statt von
 * Hand. Eine Anmeldung ist eine Gesundheitsangabe; ist sie bearbeitet,
 * braucht es sie nur noch kurz. Fristen in config/anmeldungen.php.
 */
class AnmeldungenAufraeumen extends Command
{
    protected $signature = 'anmeldungen:aufraeumen {--probe : Nur anzeigen, nichts löschen}';

    protected $description = 'Anmeldungen zu Selbsthilfegruppen nach Ablauf der Frist löschen';

    public function handle(): int
    {
        $tageBearbeitet = config('anmeldungen.aufbewahrung_tage_bearbeitet');
        $tageOffen = config('anmeldungen.aufbewahrung_tage_offen');

        $bearbeitet = GroupRegistration::where('status', '!=', 'offen')
            ->where('bearbeitet_at', '<=', now()->subDays($tageBearbeitet));
        $offen = GroupRegistration::offen()->where('created_at', '<=', now()->subDays($tageOffen));

        $anzahlBearbeitet = $bearbeitet->count();
        $anzahlOffen = $offen->count();

        if ($this->option('probe')) {
            $this->info('Probelauf — es würde gelöscht:');
            $this->line("  {$anzahlBearbeitet} bearbeitete (älter als {$tageBearbeitet} Tage nach Freigabe oder Ablehnung)");
            $this->line("  {$anzahlOffen} offene (älter als {$tageOffen} Tage)");

            return self::SUCCESS;
        }

        $bearbeitet->delete();
        $offen->delete();

        $gesamt = $anzahlBearbeitet + $anzahlOffen;

        if ($gesamt > 0) {
            Log::info('Aufbewahrungsfrist: Gruppenanmeldungen gelöscht', [
                'bearbeitet' => $anzahlBearbeitet,
                'offen' => $anzahlOffen,
            ]);
        }

        $this->info("{$gesamt} Anmeldungen gelöscht ({$anzahlBearbeitet} bearbeitet, {$anzahlOffen} offen).");

        return self::SUCCESS;
    }
}
