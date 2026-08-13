<?php

namespace App\Support;

use App\Models\Language;
use App\Models\Page;

/**
 * Der vorgeschaltete Hinweis auf belastende Inhalte.
 *
 * Ausdrücklicher Wunsch des Vereins (Strukturpapier, erste Zeile): „Am Anfang
 * muss als erstes eine Erklärung mit Trigger-Warnung erscheinen, die man
 * schliessen kann, so dass sie nächstes Mal noch mal kommt, oder aber auch
 * auswählen kann ‚diese Meldung nicht mehr anzeigen‘.“
 *
 * Der Inhalt kommt aus einer normalen Seite (`Page::TRIGGER_SLUG`) — mit allem,
 * was Seiten können: Bausteine, Sprachfassungen, Leichte Sprache, Pflege im
 * Panel. Diese Klasse sucht nur die richtige Fassung heraus.
 *
 * Fehlt die Seite oder ist sie unveröffentlicht, entfällt der Hinweis
 * ersatzlos. Das ist der Ausschalter: Der Verein kann ihn im Panel abschalten,
 * ohne dass jemand deployen muss.
 */
class Triggerwarnung
{
    /**
     * Die Warnung in der aktiven Sprache, sonst in der Rückfallsprache.
     *
     * Der Rückfall ist hier wichtiger als anderswo: Ein Hinweis auf belastende
     * Inhalte, der einer russischsprachigen Besucherin nur deshalb nicht
     * angezeigt wird, weil ihn niemand übersetzt hat, verfehlt genau den Zweck,
     * für den er da ist. Lieber auf Deutsch als gar nicht — die Sprache wird
     * am Element ausgezeichnet, damit Vorlesehilfen richtig sprechen.
     */
    public static function fuer(Language $sprache): ?Page
    {
        $seite = self::laden($sprache->code);

        if ($seite) {
            return $seite;
        }

        $rueckfall = $sprache->fallback();

        return $rueckfall ? self::laden($rueckfall->code) : null;
    }

    private static function laden(string $locale): ?Page
    {
        return Page::veroeffentlicht()
            ->with('blocks')
            ->where('locale', $locale)
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->where('slug', Page::TRIGGER_SLUG)
            ->first();
    }
}
