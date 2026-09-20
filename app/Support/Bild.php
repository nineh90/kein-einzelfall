<?php

namespace App\Support;

/**
 * Übersetzt die Bild-Adressen der WordPress-Altseite in unsere.
 *
 * Die Altseite hat kaum Bilder: sieben Porträts auf der Teamseite, den
 * QR-Code auf der Spendenseite, das Logo. Sie liegen nach `bilder:holen`
 * unter `/img/altseite/DATEINAME.jpg` — flach, ohne Jahr-Monat-Ordner. Bei
 * Dokumenten halten die Ordner gleichnamige Dateien auseinander (mehrere
 * „Satzung.pdf“); bei einer Handvoll Bilder mit sprechenden Namen wäre das
 * nur Ballast.
 *
 * Porträts werden beim Holen verkleinert und als JPEG abgelegt — die
 * Originale sind bis zu 2.000 Pixel hoch, gezeigt werden sie 80 Pixel gross.
 * Kleine Grafiken (der QR-Code) bleiben, wie sie sind.
 */
class Bild
{
    /** Öffentlicher Ordner, unter dem die Bilder liegen. */
    public const ORDNER = 'img/altseite';

    /** Längste Kante nach dem Verkleinern. Reicht für 80 px Anzeige auf 3x-Displays mit Reserve. */
    public const MAX_KANTE = 800;

    /** Nur diese Endungen werden übernommen. */
    public const ERLAUBT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Dateiname (ohne Ordner und Endung) aus einer Altseiten-Adresse — oder
     * null, wenn sie nicht aus dem Upload-Ordner stammt.
     */
    public static function stamm(string $altUrl): ?string
    {
        $relativ = Dokument::relativerPfad($altUrl);

        if ($relativ === null) {
            return null;
        }

        $endung = strtolower(pathinfo($relativ, PATHINFO_EXTENSION));

        if (! in_array($endung, self::ERLAUBT, true)) {
            return null;
        }

        return pathinfo($relativ, PATHINFO_FILENAME);
    }

    /**
     * Der Pfad, unter dem das geholte Bild liegt — oder null, wenn es (noch)
     * nicht da ist. Die Endung entscheidet `bilder:holen`: verkleinerte Fotos
     * werden JPEG, kleine Grafiken behalten ihre.
     */
    public static function lokal(string $altUrl): ?string
    {
        $stamm = self::stamm($altUrl);

        if ($stamm === null) {
            return null;
        }

        foreach (array_unique(['jpg', strtolower(pathinfo($altUrl, PATHINFO_EXTENSION)), ...self::ERLAUBT]) as $endung) {
            $datei = self::ORDNER.'/'.$stamm.'.'.$endung;

            if (is_file(public_path($datei))) {
                return '/'.$datei;
            }
        }

        return null;
    }
}
