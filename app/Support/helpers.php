<?php

use App\Models\Language;

if (! function_exists('knoepfe')) {
    /**
     * Nur die Knöpfe, die irgendwohin führen.
     *
     * Seit die Knöpfe im Panel gepflegt werden, entstehen leere: Es reicht, im
     * Wiederholfeld einmal auf „Hinzufügen“ zu tippen und die Felder leer zu
     * lassen. Ungefiltert ergäbe das ein <a href=""> — für die Tastatur ein
     * Stolperstopp, für einen Screenreader ein Link ohne Namen und damit ein
     * echter WCAG-Verstoss (2.4.4).
     *
     * @param  array<int, array<string, mixed>>  $ctas
     * @return array<int, array<string, mixed>>
     */
    function knoepfe(array $ctas): array
    {
        return array_values(array_filter(
            $ctas,
            fn ($cta) => is_array($cta)
                && filled($cta['label'] ?? null)
                && filled($cta['url'] ?? null),
        ));
    }
}

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
    function sprachlink(string $name, mixed $parameter = [], ?string $locale = null): string
    {
        $sprache = $locale ? (Language::finden($locale) ?? Language::standard()) : Language::aktuell();

        if ($sprache->istStandard()) {
            return route($name, $parameter);
        }

        // `route()` nimmt auch einen einzelnen Wert statt eines Arrays
        // (`route('blog.show', $slug)`). Der Helfer muss das genauso können,
        // sonst wäre er an den Aufrufstellen die unangenehmere Variante — und
        // dann schreibt jemand wieder `route()` und die Sprache geht verloren.
        $parameter = is_array($parameter) ? $parameter : [$parameter];

        // Der Sprachcode zuerst und benannt: Laravel füllt benannte Parameter
        // zuerst und verteilt den Rest der Reihe nach.
        return route('sprache.'.$name, ['locale' => $sprache->code] + $parameter);
    }
}

// Ein *markierter* Teil, nur am Wortrand (siehe hervorheben()).
defined('HERVORHEBUNG_MUSTER')
    || define('HERVORHEBUNG_MUSTER', '/(?<![\p{L}\p{N}*])\*(?=\S)([^*]+?)(?<=\S)\*(?![\p{L}\p{N}])/u');

if (! function_exists('hervorheben')) {
    /**
     * *Sternchen* in einem Absatz werden fett, wie beim Fettschreiben in einer
     * Nachricht (KEV-54). Dieselbe Markierung wie beim Aufmacher, nur ohne
     * Handschrift: Im Fliesstext soll ein Satz herausstehen, nicht verzieren.
     *
     * Nur am Wortrand: „Mitarbeiter*innen und Kolleg*innen“ bleibt, wie es
     * ist. Erst maskieren, dann ersetzen, die einzige rohe Ausgabe ist unser
     * eigenes <strong>.
     */
    function hervorheben(string $text): \Illuminate\Support\HtmlString
    {
        return new \Illuminate\Support\HtmlString(preg_replace(
            HERVORHEBUNG_MUSTER,
            '<strong class="font-semibold text-ink">$1</strong>',
            e($text),
        ));
    }

    /** Derselbe Text ohne Sternchen, für Suche und Vorschautexte. */
    function ohne_hervorhebung(string $text): string
    {
        return preg_replace(HERVORHEBUNG_MUSTER, '$1', $text);
    }
}
