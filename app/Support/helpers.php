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

// Ein Link im Absatz: [Text](/adresse) oder [Text](https://…). Nur eigene
// Pfade und https, kein javascript: und kein http.
defined('LINK_MUSTER')
    || define('LINK_MUSTER', '/\[([^\[\]]+)\]\((\/[^\s()]*|https:\/\/[^\s()]+)\)/u');

// Ein Symbol im Text: {darstellung} oder {notausgang} (KEV-109). Nur diese
// beiden, alles andere in geschweiften Klammern bleibt stehen.
defined('SYMBOL_MUSTER')
    || define('SYMBOL_MUSTER', '/\{(darstellung|notausgang)\}/u');

if (! function_exists('hervorheben')) {
    /**
     * *Sternchen* in einem Absatz werden fett, wie beim Fettschreiben in einer
     * Nachricht (KEV-54). Dieselbe Markierung wie beim Aufmacher, nur ohne
     * Handschrift: Im Fliesstext soll ein Satz herausstehen, nicht verzieren.
     *
     * Nur am Wortrand: „Mitarbeiter*innen und Kolleg*innen“ bleibt, wie es
     * ist. Erst maskieren, dann ersetzen, die einzige rohe Ausgabe ist unser
     * eigenes <strong> und <a>.
     *
     * E-Mail-Adressen im Text werden zum Link (KEV-74, „Schreib uns an
     * arbeitsgruppe@…“). Ein Punkt am Satzende gehört nicht zur Adresse.
     *
     * [Text](/adresse) wird ein Link (08.10.2026): Beim Import der Altseite
     * gingen die Links im Fliesstext verloren („FSM – Fonds sexueller
     * Missbrauch“ auf /wissen, die Termine der Reihe „im Dialog“).
     *
     * {darstellung} und {notausgang} werden das Symbol des jeweiligen Knopfs
     * (KEV-109): In der Triggerwarnung sieht man so gleich, welcher Knopf
     * gemeint ist. Für Vorlesehilfen mit Namen, die Grösse in em, damit es
     * mit der Schrift aus der Darstellung mitwächst.
     */
    function hervorheben(string $text): \Illuminate\Support\HtmlString
    {
        $html = preg_replace(
            HERVORHEBUNG_MUSTER,
            '<strong class="font-semibold text-ink">$1</strong>',
            e($text),
        );

        // Links zuerst beiseitelegen: Eine Adresse wie die eines Teams-Termins
        // enthält ein @ und würde sonst als E-Mail-Adresse verlinkt.
        $links = [];
        $html = preg_replace_callback(LINK_MUSTER, function ($m) use (&$links) {
            $links[] = '<a href="'.$m[2].'" class="text-green-deep underline">'.$m[1].'</a>';

            return "\u{E000}".(count($links) - 1)."\u{E001}";
        }, $html);

        $html = preg_replace(
            '/[\w.+-]+@[\w-]+(?:\.[\w-]+)+/u',
            '<a href="mailto:$0" class="text-green-deep underline">$0</a>',
            $html,
        );

        $html = preg_replace_callback(
            "/\u{E000}(\d+)\u{E001}/u",
            fn ($m) => $links[(int) $m[1]],
            $html,
        );

        // Übersteht das Maskieren unverändert, darum erst hier.
        return new \Illuminate\Support\HtmlString(
            preg_replace_callback(SYMBOL_MUSTER, fn ($m) => text_symbol($m[1]), $html),
        );
    }

    /**
     * Ein Absatz eines Textbausteins als HTML (KEV-152).
     *
     * Zeilen, die mit „- “ beginnen, werden zur Aufzählung, was davor oder
     * danach steht, bleibt Absatz. So lässt sich im Panel eine Liste
     * schreiben, ohne einen eigenen Editor:
     *
     *     Fristen können unter anderem davon abhängen:
     *     - um welches Verfahren es geht,
     *     - welcher Träger zuständig ist.
     *
     * Ohne solche Zeilen ist es genau der Absatz wie vorher.
     */
    function absatz(string $text): \Illuminate\Support\HtmlString
    {
        $html = '';
        $zeilen = [];
        $liste = [];

        $absatz = function () use (&$html, &$zeilen) {
            if ($zeilen !== []) {
                $html .= '<p>'.hervorheben(implode("\n", $zeilen)).'</p>';
                $zeilen = [];
            }
        };
        $aufzaehlung = function () use (&$html, &$liste) {
            if ($liste !== []) {
                $html .= '<ul>'.implode('', $liste).'</ul>';
                $liste = [];
            }
        };

        foreach (preg_split('/\R/u', trim($text)) as $zeile) {
            if (preg_match('/^\s*[-•]\s+(.+)$/u', $zeile, $treffer)) {
                $absatz();
                $liste[] = '<li>'.hervorheben($treffer[1]).'</li>';
            } else {
                $aufzaehlung();
                $zeilen[] = $zeile;
            }
        }

        $absatz();
        $aufzaehlung();

        return new \Illuminate\Support\HtmlString($html);
    }

    /** Das Symbol eines Knopfs, so gestaltet wie der Knopf selbst (KEV-109). */
    function text_symbol(string $name): string
    {
        [$icon, $beschriftung, $aussehen] = match ($name) {
            'darstellung' => ['accessibility', __('Darstellung'), 'border border-line bg-cream text-green'],
            'notausgang' => ['exit', __('rahmen.notausgang.kopf'), 'bg-green text-on-green'],
        };

        return '<span role="img" aria-label="'.e($beschriftung).'" class="mx-0.5 inline-flex h-[1.6em] w-[1.6em] '
            .'items-center justify-center rounded-full align-middle '.$aussehen.'">'
            .\Illuminate\Support\Facades\Blade::render('<x-ui.icon :name="$icon" class="h-[0.95em] w-[0.95em]" />', ['icon' => $icon])
            .'</span>';
    }

    /** Derselbe Text ohne Sternchen, Link-Klammern und Symbole, für Suche und Vorschautexte. */
    function ohne_hervorhebung(string $text): string
    {
        return preg_replace(
            [HERVORHEBUNG_MUSTER, LINK_MUSTER, '/\s*\{(?:darstellung|notausgang)\}/u'],
            ['$1', '$1', ''],
            $text,
        );
    }
}

if (! function_exists('ueberschrift')) {
    /**
     * Eine Abschnittsüberschrift mit dem Vereinsnamen am Stück (KEV-232).
     *
     * „KE!N EINZELFALL“ wird nie auseinandergerissen; vorher stand „KE!N“ am
     * Zeilenende und „EINZELFALL e.V.“ allein darunter. Endet die Überschrift
     * auf „KE!N EINZELFALL e.V.“, steht der Name in einer eigenen Zeile:
     * „Die Selbsthilfegruppen des“ / „KE!N EINZELFALL e.V.“ (Wunsch von Taddi).
     * inline-block statt nowrap: Passt der Name bei grosser Schrift auf einem
     * schmalen Bildschirm nicht in eine Zeile, bricht er doch um, statt über
     * den Rand zu laufen. Erst maskieren, dann ersetzen.
     */
    function ueberschrift(string $text): \Illuminate\Support\HtmlString
    {
        $html = preg_replace('/KE!N EINZELFALL(?: e\.V\.)?/u', '<span class="inline-block">$0</span>', e($text));
        $html = preg_replace('/(?<=\S)\s+(<span class="inline-block">KE!N EINZELFALL e\.V\.<\/span>)$/u', '<br>$1', $html);

        return new \Illuminate\Support\HtmlString($html);
    }
}

if (! function_exists('bildadresse')) {
    /**
     * Adresse eines Bildes aus public/, mit Fingerabdruck seines Inhalts.
     *
     * Ein neues Bild ersetzt das alte meist unter demselben Namen
     * (titelbilder/verein.webp). Für den Browser ist das dieselbe Datei, er
     * zeigt die alte aus seinem Cache, und das tagelang: Ohne Cache-Control
     * schätzt er die Haltbarkeit selbst. Mit `?v=…` aus dem Inhalt bekommt
     * jedes neue Bild eine neue Adresse und ist beim nächsten Aufruf da, ohne
     * dass jemand neu laden muss. Wie bei CSS und JS, denen Vite den Hash in
     * den Namen schreibt.
     *
     * Aus dem Inhalt, nicht aus dem Änderungsdatum: Das setzt jeder frische
     * Checkout und jeder Docker-Build neu, die Adresse wechselte dann bei
     * jedem Deploy, auch für unveränderte Bilder.
     *
     * Fremde Adressen (https://…) und fehlende Dateien bleiben, wie sie sind.
     */
    function bildadresse(?string $pfad): ?string
    {
        static $gemerkt = [];

        if (blank($pfad) || ! str_starts_with($pfad, '/') || str_starts_with($pfad, '//')) {
            return $pfad;
        }

        return $gemerkt[$pfad] ??= (function () use ($pfad) {
            // Medien::dateipfad: Hochgeladenes (/storage/…) liegt in
            // storage/app/public, auch ohne Verknüpfung in public/.
            $datei = \App\Support\Medien::dateipfad($pfad);

            return $datei !== null && is_file($datei)
                ? $pfad.(str_contains($pfad, '?') ? '&' : '?').'v='.substr(hash_file('xxh3', $datei), 0, 10)
                : $pfad;
        })();
    }
}
