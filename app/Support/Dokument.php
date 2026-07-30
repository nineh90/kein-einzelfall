<?php

namespace App\Support;

/**
 * Übersetzt die Dokument-Adressen der WordPress-Altseite in unsere.
 *
 * Die Altseite legt alles unter `/wp-content/uploads/JAHR/MONAT/datei.pdf` ab.
 * Der Pfad wandert 1:1 nach `/dokumente/JAHR/MONAT/datei.pdf` — die
 * Jahr-Monat-Ordner bleiben erhalten, obwohl sie hässlich sind:
 *
 *  - Sie halten Dateinamen eindeutig. Im Bestand gibt es mehrere „Satzung.pdf“
 *    aus verschiedenen Jahren; flach abgelegt würden sie sich überschreiben.
 *  - Sie machen die Herkunft jeder Datei nachvollziehbar, solange die Altseite
 *    noch läuft.
 *
 * `/wp-content/` bleibt als Weiterleitung bestehen (siehe routes/web.php).
 * Diese Adressen sind indexiert und extern verlinkt — sie dürfen nicht sterben.
 */
class Dokument
{
    /** Öffentlicher Ordner, unter dem die Dokumente liegen. */
    public const ORDNER = 'dokumente';

    /** Nur diese Endungen werden übernommen. */
    public const ERLAUBT = ['pdf', 'doc', 'docx', 'odt', 'xls', 'xlsx', 'ods'];

    /**
     * Aus einer Altseiten-Adresse den neuen Pfad machen.
     *
     * Adressen, die nicht aus dem Upload-Ordner stammen (externe Links),
     * bleiben unverändert.
     */
    public static function pfad(string $altUrl): string
    {
        $relativ = self::relativerPfad($altUrl);

        return $relativ === null ? $altUrl : '/'.self::ORDNER.'/'.$relativ;
    }

    /**
     * Der Teil hinter `/wp-content/uploads/` — oder null, wenn die Adresse
     * gar nicht von dort stammt.
     */
    public static function relativerPfad(string $altUrl): ?string
    {
        // Auch absolute Adressen der Altseite abdecken.
        $pfad = parse_url($altUrl, PHP_URL_PATH) ?: $altUrl;

        if (! preg_match('#/wp-content/uploads/(.+)$#', $pfad, $treffer)) {
            return null;
        }

        $relativ = ltrim(urldecode($treffer[1]), '/');

        // Kein Ausbrechen aus dem Zielordner — die Liste kommt zwar aus
        // unserem eigenen Manifest, aber ein Pfad aus einer Datei ist eine
        // Eingabe wie jede andere.
        if (str_contains($relativ, '..') || str_starts_with($relativ, '/')) {
            return null;
        }

        return $relativ;
    }

    /** Liegt die Datei bei uns? */
    public static function vorhanden(string $altUrl): bool
    {
        $relativ = self::relativerPfad($altUrl);

        return $relativ !== null && is_file(public_path(self::ORDNER.'/'.$relativ));
    }

    /** Tatsächliche Größe in Bytes, sofern vorhanden. */
    public static function groesse(string $altUrl): ?int
    {
        $relativ = self::relativerPfad($altUrl);

        if ($relativ === null) {
            return null;
        }

        $datei = public_path(self::ORDNER.'/'.$relativ);

        return is_file($datei) ? filesize($datei) : null;
    }
}
