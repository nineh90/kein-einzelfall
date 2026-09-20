<?php

namespace App\Console\Commands;

use App\Support\Bild;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Lädt die Bilder aus dem Abzug der Altseite nach public/img/altseite/.
 *
 *   php artisan bilder:holen            fehlende holen
 *   php artisan bilder:holen --pruefen  nichts laden, nur Bericht
 *
 * Grundlage ist docs/altseite-inhalt.json: Jeder Block, in dem auf der
 * Altseite ein Bild stand, trägt dort `bild.src`. Was hier liegt, verknüpft
 * der TeamUndGruppenSeeder mit den Personen — er setzt nur Pfade zu Dateien,
 * die es gibt.
 *
 * Fotos werden auf Bild::MAX_KANTE verkleinert und als JPEG gespeichert.
 * Ein Porträt mit 2.000 Pixeln Höhe für einen 80-Pixel-Kreis wäre für jede
 * Besucherin ein Megabyte, das nichts zeigt.
 */
class BilderHolen extends Command
{
    protected $signature = 'bilder:holen
                            {--pruefen : Nichts laden, nur berichten}
                            {--basis=https://kein-einzelfall.de : Wo die Altseite liegt}';

    protected $description = 'Lädt die Bilder der Altseite nach public/img/altseite/';

    public function handle(): int
    {
        $inhalt = json_decode((string) @file_get_contents(base_path('docs/altseite-inhalt.json')), true);

        if (! $inhalt) {
            $this->error('docs/altseite-inhalt.json fehlt oder ist unlesbar — erst `php artisan altseite:holen`.');

            return self::FAILURE;
        }

        // Der Bericht kommt ohne gd aus — nur das Verkleinern braucht es.
        if (! $this->option('pruefen') && ! function_exists('imagecreatefromstring')) {
            $this->error('Die PHP-Erweiterung gd fehlt — ohne sie lassen sich die Fotos nicht verkleinern. '
                .'Bericht ohne Laden: `bilder:holen --pruefen`.');

            return self::FAILURE;
        }

        // Ein Bild kann auf mehreren Seiten stehen; geholt wird es einmal.
        $bilder = [];
        foreach ($inhalt as $pfad => $seite) {
            foreach ($seite['bloecke'] ?? [] as $block) {
                if (isset($block['bild']['src'])) {
                    $bilder[$block['bild']['src']] ??= ['seite' => $pfad, 'alt' => $block['bild']['alt'] ?? ''];
                }
            }
        }

        $this->info(count($bilder).' Bilder im Abzug.');

        $geholt = $vorhanden = $fehler = 0;

        foreach ($bilder as $src => $info) {
            $stamm = Bild::stamm($src);

            if ($stamm === null) {
                $this->warn("  übersprungen (keine Upload-Adresse oder fremder Dateityp): {$src}");
                $fehler++;

                continue;
            }

            if ($lokal = Bild::lokal($src)) {
                $this->line("  vorhanden: {$lokal}  ({$info['seite']})");
                $vorhanden++;

                continue;
            }

            if ($this->option('pruefen')) {
                $this->line("  fehlt: {$src}  ({$info['seite']})");
                $fehler++;

                continue;
            }

            try {
                $antwort = Http::withHeaders(['User-Agent' => 'NilsDigital-Migration/1.0'])
                    ->timeout(60)->retry(2, 500)
                    ->get(rtrim($this->option('basis'), '/').$src);
            } catch (\Throwable $e) {
                $this->error("  Fehler bei {$src}: {$e->getMessage()}");
                $fehler++;

                continue;
            }

            if (! $antwort->successful()) {
                $this->error("  HTTP {$antwort->status()} bei {$src}");
                $fehler++;

                continue;
            }

            $ziel = $this->ablegen($antwort->body(), $stamm, strtolower(pathinfo($src, PATHINFO_EXTENSION)));

            if ($ziel === null) {
                $this->error("  keine Bilddatei erhalten: {$src}");
                $fehler++;

                continue;
            }

            $this->line(sprintf('  geholt: %s  (%d KB, %s)', $ziel, filesize(public_path($ziel)) / 1024, $info['seite']));
            $geholt++;
        }

        $this->newLine();
        $this->info("{$geholt} geholt, {$vorhanden} bereits vorhanden, {$fehler} Fehler.");

        if ($geholt > 0) {
            $this->comment('Die Fotos den Personen zuordnen: `php artisan db:seed --class=TeamUndGruppenSeeder` '
                .'— auf bestehenden Datenbanken übernimmt das die Migration.');
        }

        return $fehler > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Speichert das Bild — verkleinert als JPEG, wenn es gross ist; sonst
     * unverändert. Gibt den öffentlichen Pfad zurück, null bei Unlesbarem.
     */
    private function ablegen(string $daten, string $stamm, string $endung): ?string
    {
        $quelle = @imagecreatefromstring($daten);

        if ($quelle === false) {
            return null;
        }

        // Handyfotos liegen oft quer gespeichert und tragen die Drehung nur
        // in den EXIF-Daten. GD ignoriert das — ohne diesen Schritt läge ein
        // Porträt auf der Seite.
        if ($endung !== 'png' && function_exists('exif_read_data')) {
            $exif = @exif_read_data('data://image/jpeg;base64,'.base64_encode($daten));
            $gedreht = match ($exif['Orientation'] ?? 1) {
                3 => imagerotate($quelle, 180, 0),
                6 => imagerotate($quelle, -90, 0),
                8 => imagerotate($quelle, 90, 0),
                default => null,
            };
            if ($gedreht) {
                imagedestroy($quelle);
                $quelle = $gedreht;
            }
        }

        $ordner = public_path(Bild::ORDNER);
        if (! is_dir($ordner)) {
            mkdir($ordner, 0755, true);
        }

        $breite = imagesx($quelle);
        $hoehe = imagesy($quelle);
        $laengste = max($breite, $hoehe);

        if ($laengste <= Bild::MAX_KANTE) {
            // Kleine Grafik (QR-Code, Logo): so lassen, wie sie ist. Ein
            // erneutes Kodieren machte nur einen QR-Code unscharf.
            $datei = Bild::ORDNER.'/'.$stamm.'.'.($endung === 'jpeg' ? 'jpg' : $endung);
            file_put_contents(public_path($datei), $daten);
            imagedestroy($quelle);

            return '/'.$datei;
        }

        $faktor = Bild::MAX_KANTE / $laengste;
        $neuBreite = (int) round($breite * $faktor);
        $neuHoehe = (int) round($hoehe * $faktor);

        $ziel = imagecreatetruecolor($neuBreite, $neuHoehe);

        // Weiss unterlegen: JPEG kennt keine Transparenz, und ein PNG-Porträt
        // mit durchsichtigem Rand würde sonst schwarz.
        imagefill($ziel, 0, 0, imagecolorallocate($ziel, 255, 255, 255));
        imagecopyresampled($ziel, $quelle, 0, 0, 0, 0, $neuBreite, $neuHoehe, $breite, $hoehe);

        $datei = Bild::ORDNER.'/'.$stamm.'.jpg';
        imagejpeg($ziel, public_path($datei), 85);

        imagedestroy($quelle);
        imagedestroy($ziel);

        return '/'.$datei;
    }
}
