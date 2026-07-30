<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Die Farbpalette gegen WCAG 2.1 AA nachrechnen.
 *
 * Hintergrund: Der Verein hat als Corporate-Farbe #009640 vorgegeben
 * (RGB 0/150/64, CMYK 100/0/57/41). Gemessen liegt dieser Ton als Text auf dem
 * Seitenhintergrund bei 3,28:1 und als Knopffläche mit heller Schrift bei
 * 3,86:1 — WCAG 2.1 AA verlangt 4,5:1. Er ist also als Marken- und
 * Dekorationsfarbe verwendbar, nicht aber für Text und Knöpfe.
 *
 * Deshalb sind `green` und `green-deep` abgedunkelte Varianten *desselben*
 * Farbtons. Dieser Test hält beides zusammen fest: dass die CI-Farbe unverändert
 * in der Palette steht, und dass keine Kombination unter ihren Grenzwert rutscht.
 *
 * Ohne diesen Test wäre die Palette eine Zahlenreihe, an der jemand später
 * „nur ein bisschen“ dreht — und Kontrastfehler sieht man beim Hinschauen nicht.
 */
class FarbkontrastTest extends TestCase
{
    /** Die vom Verein vorgegebene Corporate-Farbe. Darf sich nicht ändern. */
    private const CI_GRUEN = '#009640';

    /** Relative Leuchtdichte nach WCAG 2.1, Definition „relative luminance“. */
    private function leuchtdichte(string $hex): float
    {
        $hex = ltrim($hex, '#');

        $kanal = function (int $wert): float {
            $c = $wert / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };

        return 0.2126 * $kanal((int) hexdec(substr($hex, 0, 2)))
            + 0.7152 * $kanal((int) hexdec(substr($hex, 2, 2)))
            + 0.0722 * $kanal((int) hexdec(substr($hex, 4, 2)));
    }

    private function kontrast(string $a, string $b): float
    {
        $la = $this->leuchtdichte($a);
        $lb = $this->leuchtdichte($b);

        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }

    /** Die Farbwerte aus resources/css/app.css lesen — nicht hier doppelt pflegen. */
    private function palette(): array
    {
        $css = file_get_contents(resource_path('css/app.css'));

        preg_match_all('/--color-([a-z-]+):\s*(#[0-9A-Fa-f]{6})/', $css, $treffer, PREG_SET_ORDER);

        $palette = [];
        foreach ($treffer as $t) {
            $palette[$t[1]] = strtoupper($t[2]);
        }

        return $palette;
    }

    public function test_die_vorgegebene_vereinsfarbe_steht_unveraendert_in_der_palette(): void
    {
        $palette = $this->palette();

        $this->assertArrayHasKey('green-brand', $palette);
        $this->assertSame(
            self::CI_GRUEN,
            $palette['green-brand'],
            'Die Corporate-Farbe des Vereins darf nicht verändert werden.'
        );
    }

    public function test_jede_verwendete_farbkombination_erfuellt_wcag_aa(): void
    {
        $p = $this->palette();

        // [Beschreibung, Vordergrund, Hintergrund, Mindestkontrast]
        //
        // 4.5 = Fliesstext (WCAG 1.4.3)
        // 3.0 = grosse Schrift, Icons, Rahmen, Bedienelemente (1.4.3 / 1.4.11)
        $kombinationen = [
            ['Fliesstext auf Seitenhintergrund', $p['ink'], $p['cream'], 4.5],
            ['Fliesstext auf Karte', $p['ink'], $p['card'], 4.5],
            ['Sekundaertext auf Seitenhintergrund', $p['ink-soft'], $p['cream'], 4.5],
            ['Link auf Seitenhintergrund', $p['green-deep'], $p['cream'], 4.5],
            ['Link auf Karte', $p['green-deep'], $p['card'], 4.5],
            ['Text in Akzentgruen auf Seitenhintergrund', $p['green'], $p['cream'], 4.5],
            ['Primaerknopf: Schrift auf Flaeche', $p['on-green'], $p['green'], 4.5],
            ['Fusszeile: Schrift auf Flaeche', $p['on-green'], $p['green-deep'], 4.5],
            ['Fusszeile: Sekundaerschrift auf Flaeche', $p['on-green-soft'], $p['green-deep'], 4.5],
            ['Badge: Fliesstext auf hellgruen', $p['ink'], $p['green-mist'], 4.5],
            ['Badge: Akzenttext auf hellgruen', $p['green-deep'], $p['green-mist'], 4.5],
            ['Notausgang: Schrift auf Warnfarbe', $p['card'], $p['alert'], 4.5],

            // Die CI-Farbe selbst wird nur dort eingesetzt, wo 3:1 genuegt:
            // Zierlinien, Icons, Rahmen, Logo.
            ['CI-Gruen als Zierlinie auf Seitenhintergrund', $p['green-brand'], $p['cream'], 3.0],
            ['CI-Gruen als Zierlinie auf Karte', $p['green-brand'], $p['card'], 3.0],
            ['Fokusring auf Seitenhintergrund', $p['green'], $p['cream'], 3.0],
            ['Linien auf Seitenhintergrund', $p['line'], $p['cream'], 1.0],
        ];

        foreach ($kombinationen as [$was, $vg, $bg, $ziel]) {
            $wert = $this->kontrast($vg, $bg);

            $this->assertGreaterThanOrEqual(
                $ziel,
                round($wert, 2),
                sprintf('%s: %s auf %s ergibt %.2f:1, gefordert sind %.1f:1.', $was, $vg, $bg, $wert, $ziel)
            );
        }
    }

    public function test_die_ci_farbe_taugt_nicht_fuer_text_und_ist_deshalb_abgedunkelt(): void
    {
        $p = $this->palette();

        // Der Beleg fuer die Entscheidung. Faellt dieser Test, hat jemand die
        // CI-Farbe direkt fuer Text oder Knoepfe eingesetzt — dann ist die
        // Begruendung im Farbkommentar hinfaellig und gehoert geprueft.
        $this->assertLessThan(
            4.5,
            $this->kontrast($p['green-brand'], $p['cream']),
            'Wenn die CI-Farbe plötzlich als Fliesstext taugt, stimmt etwas nicht.'
        );

        // Und die abgedunkelten Varianten muessen es koennen.
        $this->assertGreaterThanOrEqual(4.5, $this->kontrast($p['green'], $p['cream']));
        $this->assertGreaterThanOrEqual(4.5, $this->kontrast($p['green-deep'], $p['cream']));
    }
}
