<?php

namespace App\Support;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Bilder und Dokumente, die der Verein im Panel selbst hochlädt.
 *
 * Bis Oktober 2026 gab es dafür nur Pfadfelder: Jedes Bild musste erst bei
 * uns landen und abgelegt werden. Kevin, 21.10.2026: „Der Kunde soll das
 * ganze ja auch komplett selbst pflegen können. Das gilt für die gesamte
 * Seite.“
 *
 * In der Datenbank steht weiter ein Pfad der Website, genau wie vorher
 * („/img/titelbilder/verein.webp“, „/dokumente/2026/07/satzung.pdf“). Neue
 * Dateien kommen nach storage/app/public/medien und heißen
 * „/storage/medien/…“. Alles, was solche Pfade liest (bildadresse(), die
 * Dokumentenliste, die Übersetzung), funktioniert deshalb unverändert.
 *
 * Zwei Stolperfallen, um die sich die Felder hier kümmern:
 *
 *  - Filament wirft beim Laden still jeden Wert weg, den es auf der
 *    Festplatte des Felds nicht findet. Beim nächsten Speichern wäre er
 *    gelöscht. Die Felder lesen deshalb keine Dateiangaben
 *    (fetchFileInformation(false)) und zeigen nur die Vorschau über die
 *    Adresse. Bestehende Pfade überleben so jedes Speichern.
 *  - Ausgeliefert wird über /storage. Fehlt auf dem Server die Verknüpfung
 *    public/storage (`php artisan storage:link`), springt eine Route ein
 *    (routes/web.php). Dauerhaft liegen die Dateien nur, wenn
 *    storage/app/public einen Deploy überlebt (Übergabe-Checkliste, B1).
 */
class Medien
{
    /** Unterordner in storage/app/public. */
    public const ORDNER = 'medien';

    /**
     * Ohne SVG: Eine SVG-Datei kann Skript enthalten und liefe, von unserer
     * Adresse geöffnet, mit den Rechten der Seite.
     */
    public const BILD_TYPEN = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];

    /** Dieselben Formate wie die übernommenen Dokumente (Dokument::ERLAUBT). */
    public const DOKUMENT_TYPEN = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.oasis.opendocument.spreadsheet',
    ];

    /** In KB, wie Filament es erwartet. */
    public const BILD_MAX = 10 * 1024;

    public const DOKUMENT_MAX = 20 * 1024;

    /**
     * Ein Bildfeld. Große Fotos verkleinert schon der Browser vor dem
     * Hochladen auf höchstens $kante Pixel: Ein Handyfoto hat schnell 8 MB,
     * auf der Seite braucht es einen Bruchteil davon.
     */
    public static function bild(string $name, int $kante = 2000): FileUpload
    {
        return FileUpload::make($name)
            ->disk('webroot')
            ->fetchFileInformation(false)
            ->image()
            ->acceptedFileTypes(self::BILD_TYPEN)
            ->maxSize(self::BILD_MAX)
            ->imageResizeMode('contain')
            ->imageResizeTargetWidth((string) $kante)
            ->imageResizeTargetHeight((string) $kante)
            ->imageResizeUpscale(false)
            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => self::speichern($file, 'bilder'));
    }

    /** Ein Dokumentfeld (PDF, Word, Tabellen). */
    public static function dokument(string $name): FileUpload
    {
        return FileUpload::make($name)
            ->disk('webroot')
            ->fetchFileInformation(false)
            ->acceptedFileTypes(self::DOKUMENT_TYPEN)
            ->maxSize(self::DOKUMENT_MAX)
            ->openable()
            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => self::speichern($file, 'dokumente'));
    }

    /**
     * Legt die Datei ab und gibt ihre Adresse zurück.
     *
     * Der Name bleibt lesbar („jahresbericht-2025-k3x9qa.pdf“): Wer ein
     * Dokument herunterlädt, sieht ihn. Die angehängten Zeichen halten ihn
     * eindeutig, und der Jahr-Monat-Ordner wie bei den Dokumenten der
     * Altseite.
     */
    public static function speichern(TemporaryUploadedFile $datei, string $art): string
    {
        $endung = strtolower($datei->guessExtension() ?: $datei->getClientOriginalExtension());
        $name = Str::slug(pathinfo($datei->getClientOriginalName(), PATHINFO_FILENAME)) ?: $art;
        $name = Str::limit($name, 60, '').'-'.Str::lower(Str::random(6)).'.'.$endung;

        $pfad = $datei->storeAs(self::ORDNER.'/'.$art.'/'.now()->format('Y/m'), $name, ['disk' => 'public']);

        return '/storage/'.$pfad;
    }

    /**
     * Wo eine Adresse der Website auf der Festplatte liegt, oder null für
     * fremde Adressen und Ausbruchsversuche („/../.env“).
     *
     * /storage/… liegt auf der Festplatte `public` (storage/app/public), auch
     * wenn die Verknüpfung public/storage fehlt. Alles andere in public/.
     */
    public static function dateipfad(?string $adresse): ?string
    {
        // Nur Pfade der eigenen Seite: „//example.org/a.jpg“ ist eine fremde
        // Adresse, parse_url sähe darin den Pfad „/a.jpg“.
        if (! is_string($adresse) || ! str_starts_with($adresse, '/') || str_starts_with($adresse, '//')) {
            return null;
        }

        $pfad = parse_url($adresse, PHP_URL_PATH);

        if (! is_string($pfad)) {
            return null;
        }

        $pfad = rawurldecode($pfad);

        if (str_contains($pfad, '..') || str_contains($pfad, "\0")) {
            return null;
        }

        return str_starts_with($pfad, '/storage/')
            ? Storage::disk('public')->path(substr($pfad, strlen('/storage/')))
            : public_path(ltrim($pfad, '/'));
    }

    /** Liegt die Datei bei uns? Fremde Adressen: nein. */
    public static function vorhanden(?string $adresse): bool
    {
        $datei = self::dateipfad($adresse);

        return $datei !== null && is_file($datei);
    }
}
