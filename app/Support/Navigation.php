<?php

namespace App\Support;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Support\Facades\Lang;

/**
 * Löst die Navigationsstruktur für die aktuelle Sprache auf.
 *
 * Zwei Dinge müssen je Sprache stimmen, nicht nur eines:
 *
 *  - die **Beschriftung** („Verein“ / „About us“) — aus `lang/{code}/navigation.php`
 *  - die **Adresse** (`/verein` / `/en/about-us`) — aus der Übersetzungsgruppe
 *    der Zielseite
 *
 * Nur die Beschriftung zu übersetzen und auf die deutsche Adresse zu verlinken
 * wäre der schlimmere Fehler: Der Menüpunkt hiesse „About us“ und führte auf
 * eine deutsche Seite.
 *
 * Fehlt der Übersetzungsschlüssel, gilt die deutsche Beschriftung aus der
 * Config. Fehlt die übersetzte Seite, gilt die Adresse der Standardfassung
 * unter dem Sprachpräfix — dort greift dann der sichtbare Rückfall.
 * Für Deutsch ist die Ausgabe damit Zeichen für Zeichen die bisherige.
 */
class Navigation
{
    /** Zwischenspeicher je Anfrage: Slug der Standardsprache => Slug dieser Sprache. */
    private const MEMO = 'ke.navigation.slugs';

    public static function haupt(): array
    {
        return self::aufloesen(config('navigation.main'));
    }

    public static function fusszeile(): array
    {
        return collect(config('navigation.footer'))
            ->map(fn (array $gruppe) => self::aufloesen($gruppe))
            ->all();
    }

    public static function mobilLeiste(): array
    {
        return self::aufloesen(config('navigation.mobile_bar'));
    }

    private static function aufloesen(array $eintraege): array
    {
        $sprache = Language::aktuell();
        $karte = $sprache->istStandard() ? [] : self::fassungen($sprache->code);

        return array_map(function (array $eintrag) use ($sprache, $karte) {
            $slug = str_starts_with($eintrag['url'], '/') ? trim($eintrag['url'], '/') : null;
            $fassung = $slug === null ? null : ($karte[$slug] ?? null);

            $eintrag['label'] = self::beschriftung($eintrag, $fassung);
            $eintrag['url'] = self::adresse($eintrag['url'], $sprache, $fassung);

            if (! empty($eintrag['children'])) {
                $eintrag['children'] = self::aufloesen($eintrag['children']);
            }

            return $eintrag;
        }, $eintraege);
    }

    /**
     * Beschriftung eines Menüpunkts, in dieser Reihenfolge:
     *
     *  1. Übersetzungsschlüssel — für Beschriftungen, die *keine* Seitentitel
     *     sind: „Gruppen & Termine“, „Start“, „Anfrage“. Die dürfen wir
     *     übersetzen, sie beschreiben die Bedienung.
     *  2. Titel der übersetzten Seite — für alles andere. „Erwerbsminderungs-
     *     rente“ oder „FSM – Erweitertes Hilfesystem“ sind Fachbegriffe des
     *     Sozialrechts; die erfinden wir nicht, sondern nehmen, was der Verein
     *     in der übersetzten Seite eingetragen hat.
     *  3. Deutsche Beschriftung aus der Config — solange nichts übersetzt ist.
     *     Ein deutscher Menüpunkt ist ehrlicher als ein selbst erfundener.
     */
    private static function beschriftung(array $eintrag, ?array $fassung): string
    {
        $schluessel = $eintrag['schluessel'] ?? null;

        // Ohne Schlüssel (E-Mail-Adresse, Instagram, Facebook, TikTok) gibt es
        // nichts zu übersetzen — Eigennamen bleiben Eigennamen.
        if (! $schluessel) {
            return $eintrag['label'];
        }

        $pfad = 'navigation.'.$schluessel;

        if (Lang::has($pfad)) {
            return __($pfad);
        }

        return $fassung['titel'] ?? $eintrag['label'];
    }

    /**
     * Externe Adressen bleiben unangetastet. Interne bekommen das Sprachpräfix
     * und, wenn es eine Übersetzung gibt, deren eigenen Slug.
     */
    private static function adresse(string $url, Language $sprache, ?array $fassung): string
    {
        if ($sprache->istStandard() || ! str_starts_with($url, '/')) {
            return $url;
        }

        // Feste Adressen (/, /aktuelles, /veranstaltungen) haben keinen
        // Seiten-Datensatz. Sie existieren in jeder Sprache unter demselben Pfad.
        return $sprache->pfad('/'.($fassung['slug'] ?? trim($url, '/')));
    }

    /**
     * Slug der Standardsprache => Slug und Titel in dieser Sprache.
     *
     * Eine Abfrage je Anfrage statt einer je Menüpunkt: Die Hauptnavigation hat
     * 24 Einträge, der Fuß noch einmal fünf.
     *
     * @return array<string, array{slug: string, titel: string}>
     */
    private static function fassungen(string $locale): array
    {
        $schluessel = self::MEMO.'.'.$locale;

        if (app()->bound($schluessel)) {
            return app()->make($schluessel);
        }

        $standard = Language::standardCode();

        $gruppen = Page::veroeffentlicht()
            // Nur die schweren Fassungen: Eine Seite in Leichter Sprache, die
            // sich ins Menue schiebt, waere ein Fehler, den niemand sucht.
            ->standardfassung()
            ->whereIn('locale', [$standard, $locale])
            ->get(['locale', 'slug', 'titel', 'uebersetzungs_gruppe'])
            ->groupBy('uebersetzungs_gruppe');

        $karte = [];

        foreach ($gruppen as $seiten) {
            $von = $seiten->firstWhere('locale', $standard);
            $nach = $seiten->firstWhere('locale', $locale);

            if ($von && $nach) {
                $karte[$von->slug] = ['slug' => $nach->slug, 'titel' => $nach->titel];
            }
        }

        app()->instance($schluessel, $karte);

        return $karte;
    }
}
