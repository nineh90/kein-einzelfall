<?php

use App\Models\Language;

if (! function_exists('sprachlink')) {
    /**
     * Adresse einer benannten Route in der aktuellen (oder einer bestimmten) Sprache.
     *
     * Die öffentlichen Routen sind zweimal registriert: ohne Präfix unter ihrem
     * bisherigen Namen (Standardsprache) und mit Präfix unter „sprache.…“.
     * Dieser Helfer wählt die passende Variante, damit in den Views weiterhin
     * ein Routenname steht und kein zusammengebauter Pfad.
     *
     * `route()` direkt aufzurufen wäre nicht falsch, aber immer deutsch — und
     * das fällt erst auf, wenn jemand auf /ru/ plötzlich auf /aktuelles landet.
     */
    function sprachlink(string $name, array $parameter = [], ?string $locale = null): string
    {
        $sprache = $locale ? (Language::finden($locale) ?? Language::standard()) : Language::aktuell();

        if ($sprache->istStandard()) {
            return route($name, $parameter);
        }

        return route('sprache.'.$name, ['locale' => $sprache->code] + $parameter);
    }
}
