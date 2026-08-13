<?php

namespace App\Support;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Throwable;

/**
 * QR-Code für eine SEPA-Überweisung („Girocode“ nach EPC069-12).
 *
 * Wunsch aus der Besprechung vom 02.08.2026: Wer spenden möchte, soll die IBAN
 * nicht abtippen müssen. Fast jede Banking-App in Deutschland liest diesen Code
 * und füllt damit das Überweisungsformular aus.
 *
 * Erzeugt wird lokal und ohne jeden Fremddienst — es gibt genug Anbieter, die
 * so einen Code „kostenlos" als Bild ausliefern und dabei mitlesen, wer ihn
 * ansieht. Auf einer Seite mit dieser Zielgruppe wäre das die falsche
 * Bequemlichkeit. Die Bibliothek liegt ohnehin schon im Projekt (Filament
 * nutzt sie für die Zwei-Faktor-Anmeldung); sie steht seitdem ausdrücklich in
 * der composer.json, damit sie nicht mit einem Filament-Update verschwindet.
 *
 * Ausgegeben wird SVG und kein PNG: Der Code bleibt bei jeder Zoomstufe scharf,
 * und wer die Schrift auf 200 % stellt, braucht ihn auch grösser.
 */
class Girocode
{
    /**
     * Der EPC-Datensatz erlaubt höchstens 331 Bytes. Länger heisst: Der Code
     * wird zwar erzeugt, aber keine Banking-App akzeptiert ihn mehr.
     */
    private const MAX_BYTES = 331;

    /**
     * @return string|null SVG-Markup, oder null wenn die Angaben nicht reichen
     */
    public static function svg(
        string $empfaenger,
        string $iban,
        ?string $bic = null,
        ?string $verwendungszweck = null,
    ): ?string {
        $nutzlast = self::nutzlast($empfaenger, $iban, $bic, $verwendungszweck);

        if ($nutzlast === null) {
            return null;
        }

        try {
            $optionen = new QROptions([
                'outputInterface' => QRMarkupSVG::class,
                // EPC069-12 schreibt Fehlerkorrektur M vor. Nicht verhandelbar:
                // Bei einer anderen Stufe verweigern Banking-Apps den Code.
                'eccLevel' => EccLevel::M,
                'outputBase64' => false,
                'addQuietzone' => true,
                'quietzoneSize' => 2,
                'svgAddXmlHeader' => false,
                'cssClass' => 'girocode',
                // Nur die dunklen Module zeichnen, und ohne fill-Attribut:
                // Die Farbe kommt aus dem CSS (siehe app.css). Sonst stünde ein
                // hartes #000 im Markup, das keine Einstellung mehr erreicht.
                'drawLightModules' => false,
                'svgUseFillAttributes' => false,
            ]);

            return (new QRCode($optionen))->render($nutzlast);
        } catch (Throwable $e) {
            // Ein fehlender QR-Code ist ein Komfortverlust; eine Spendenseite,
            // die deswegen mit einem Fehler abbricht, ist ein Ausfall.
            report($e);

            return null;
        }
    }

    /**
     * Der Datensatz nach EPC069-12, Fassung 002.
     *
     * Die Reihenfolge der Zeilen ist die Spezifikation — sie ist nicht
     * dokumentiert, sie *ist* das Format. Leere Zeilen in der Mitte müssen
     * stehen bleiben, sonst rutscht alles darunter eine Zeile hoch.
     */
    private static function nutzlast(
        string $empfaenger,
        string $iban,
        ?string $bic,
        ?string $verwendungszweck,
    ): ?string {
        $iban = strtoupper(preg_replace('/\s+/', '', $iban) ?? '');
        $empfaenger = trim($empfaenger);

        // Grobe Plausibilität. Ein Code auf eine unvollständige IBAN führte
        // eine Spende ins Leere — lieber gar keiner.
        if ($empfaenger === '' || ! preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{10,30}$/', $iban)) {
            return null;
        }

        $zeilen = [
            'BCD',                                  //  1 Kennung
            '002',                                  //  2 Fassung
            '1',                                    //  3 Zeichensatz: 1 = UTF-8
            'SCT',                                  //  4 SEPA Credit Transfer
            strtoupper(trim((string) $bic)),        //  5 BIC — in Fassung 002 im SEPA-Raum optional
            mb_substr($empfaenger, 0, 70),          //  6 Empfänger
            $iban,                                  //  7 IBAN
            '',                                     //  8 Betrag — bewusst leer: den bestimmt die spendende Person
            '',                                     //  9 Zweckcode
            '',                                     // 10 strukturierte Referenz
            mb_substr(trim((string) $verwendungszweck), 0, 140),   // 11 Verwendungszweck
        ];

        $nutzlast = implode("\n", $zeilen);

        // Nachlaufende Leerzeilen dürfen entfallen und sparen genau die Bytes,
        // an denen es bei langen Vereinsnamen knapp wird.
        $nutzlast = rtrim($nutzlast, "\n");

        return strlen($nutzlast) <= self::MAX_BYTES ? $nutzlast : null;
    }
}
