<?php

namespace App\Models\Concerns;

use App\Models\Language;
use Filament\Facades\Filament;

/**
 * Sprachfassungen einzelner Felder in einer JSON-Spalte `uebersetzungen`:
 * {"en": {"teaser": "…", "beschreibung": "…"}} (08.10.2026).
 *
 * Für Modelle ohne eigene Zeile je Sprache (Gruppen, Team). Gelesen wird auf
 * Seiten einer anderen Sprache die Übersetzung, wenn es eine gibt, sonst das
 * deutsche Original. Gespeichert wird nur das Original: Die Felder selbst
 * bleiben deutsch, und im Verwaltungsbereich sieht man immer sie.
 *
 * Welche Felder: `protected array $uebersetzbar` im Model. Nichts, wonach
 * gefiltert oder verglichen wird (Team-`bereich`, Gruppen-`typ`).
 */
trait Uebersetzbar
{
    public function getAttributeValue($key)
    {
        $wert = parent::getAttributeValue($key);

        if (! in_array($key, $this->uebersetzbar ?? [], true)) {
            return $wert;
        }

        // Ohne laufende Anwendung (reine Unit-Tests) gibt es keine Sprache.
        if (! app()->bound('translator')) {
            return $wert;
        }

        $sprache = app()->getLocale();

        if ($sprache === Language::standardCode() || Filament::isServing()) {
            return $wert;
        }

        $fassung = (parent::getAttributeValue('uebersetzungen') ?? [])[$sprache][$key] ?? null;

        return filled($fassung) ? $fassung : $wert;
    }
}
