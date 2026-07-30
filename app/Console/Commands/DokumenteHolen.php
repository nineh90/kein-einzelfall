<?php

namespace App\Console\Commands;

use App\Support\Dokument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Holt die Dokumente der Altseite auf unseren Server.
 *
 *   php artisan dokumente:holen           nur die verlinkten (Standard)
 *   php artisan dokumente:holen --alle    den kompletten Bestand
 *   php artisan dokumente:holen --pruefen nichts laden, nur Bericht
 *
 * Warum nur die verlinkten als Standard: Der Medienbestand enthält 121
 * Dateien, verlinkt sind 31. Unter den übrigen 90 sind laut Übergabe-Checkliste
 * (A4) auch Behörden-Schriftwechsel und Stellungnahmen, bei denen erst geprüft
 * werden muss, ob personenbezogene Daten darin stehen. Etwas zu übernehmen,
 * das niemand angefordert hat, ist bei dieser Zielgruppe das falsche
 * Standardverhalten.
 *
 * Der Lauf ist wiederholbar: Was in der richtigen Grösse schon liegt, wird
 * übersprungen. Ein abgebrochener Lauf lässt sich also einfach neu starten.
 */
class DokumenteHolen extends Command
{
    protected $signature = 'dokumente:holen
        {--alle : Den kompletten Medienbestand holen, nicht nur die verlinkten}
        {--pruefen : Nichts herunterladen, nur berichten was fehlt}
        {--basis=https://kein-einzelfall.de : Herkunft der Dateien}';

    protected $description = 'Lädt die Dokumente der Altseite nach public/dokumente/';

    public function handle(): int
    {
        $manifest = json_decode(
            file_get_contents(base_path('docs/dokumente-manifest.json')),
            true
        );

        if (! $manifest) {
            $this->error('docs/dokumente-manifest.json fehlt oder ist unlesbar.');

            return self::FAILURE;
        }

        $auswahl = $this->option('alle')
            ? $manifest
            : array_values(array_filter($manifest, fn ($d) => ! empty($d['verlinkt_auf'])));

        $this->info(sprintf(
            '%d von %d Dokumenten%s.',
            count($auswahl),
            count($manifest),
            $this->option('alle') ? '' : ' (nur verlinkte — mit --alle den ganzen Bestand)'
        ));

        $geholt = $uebersprungen = $fehler = 0;
        $bytes = 0;

        foreach ($auswahl as $dok) {
            $relativ = Dokument::relativerPfad($dok['alt_url']);

            if ($relativ === null) {
                $this->warn("  übersprungen (keine Upload-Adresse): {$dok['alt_url']}");
                $uebersprungen++;

                continue;
            }

            $endung = strtolower(pathinfo($relativ, PATHINFO_EXTENSION));

            if (! in_array($endung, Dokument::ERLAUBT, true)) {
                $this->warn("  übersprungen (Dateityp {$endung}): {$relativ}");
                $uebersprungen++;

                continue;
            }

            $ziel = public_path(Dokument::ORDNER.'/'.$relativ);

            // Schon da und vollständig? Dann nichts tun.
            if (is_file($ziel) && filesize($ziel) === $dok['bytes']) {
                $uebersprungen++;

                continue;
            }

            if ($this->option('pruefen')) {
                $this->line("  fehlt: {$relativ}");
                $fehler++;

                continue;
            }

            $quelle = rtrim($this->option('basis'), '/').$dok['alt_url'];

            try {
                $antwort = Http::timeout(60)->retry(2, 500)->get($quelle);
            } catch (\Throwable $e) {
                $this->error("  Fehler bei {$relativ}: {$e->getMessage()}");
                $fehler++;

                continue;
            }

            if (! $antwort->successful()) {
                $this->error("  HTTP {$antwort->status()} bei {$relativ}");
                $fehler++;

                continue;
            }

            $inhalt = $antwort->body();

            // Grösse gegen das Manifest prüfen. Eine Abweichung heisst: Die
            // Datei auf der Altseite ist nicht mehr die, die wir katalogisiert
            // haben — das gehört gesehen und nicht stillschweigend übernommen.
            if (strlen($inhalt) !== $dok['bytes']) {
                $this->warn(sprintf(
                    '  Grösse weicht ab bei %s: erwartet %d, bekommen %d Bytes',
                    $relativ,
                    $dok['bytes'],
                    strlen($inhalt)
                ));
            }

            // Eine WordPress-Fehlerseite ist auch eine 200-Antwort.
            if ($endung === 'pdf' && ! str_starts_with($inhalt, '%PDF')) {
                $this->error("  keine PDF-Datei erhalten: {$relativ}");
                $fehler++;

                continue;
            }

            if (! is_dir(dirname($ziel))) {
                mkdir(dirname($ziel), 0755, true);
            }

            file_put_contents($ziel, $inhalt);

            $geholt++;
            $bytes += strlen($inhalt);
        }

        $this->newLine();
        $this->info(sprintf(
            '%d geholt (%.1f MB), %d bereits vorhanden, %d Fehler.',
            $geholt,
            $bytes / 1048576,
            $uebersprungen,
            $fehler
        ));

        if ($geholt > 0) {
            $this->comment('Die Verweise in den Seiten setzt der AltseiteSeeder — '
                .'`php artisan db:seed --class=AltseiteSeeder`.');
        }

        return $fehler > 0 ? self::FAILURE : self::SUCCESS;
    }
}
