<?php

namespace App\Support;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Support\Collection;

/**
 * Die Kacheln des Bausteins „Unterseiten als Kacheln“ (KEV-156).
 *
 * Auf der Übersicht „Raum für Ungesagtes“ stehen Taddis vier Kategorien mit
 * ihrem Bild. Gepflegt wird dafür nichts zusätzlich: Welche Seiten, in welcher
 * Reihenfolge, steht im Menü; Bild und Unterzeile an der jeweiligen Seite.
 * Ändert der Verein dort etwas, ändern sich die Kacheln mit.
 */
class Unterseiten
{
    /**
     * Die Seiten unter dem Bereich, den $seite anführt (sonst die
     * Geschwister von $seite), in der Reihenfolge des Menüs.
     *
     * @return list<array{label: string, url: string, bild: ?string, text: ?string, aufrufe: ?int}>
     */
    public static function fuer(Page $seite): array
    {
        return self::kacheln(Seitenkontext::fuer($seite->slug)->geschwister(PHP_INT_MAX));
    }

    /**
     * Ausgewählte Seiten als Kacheln, in der angegebenen Reihenfolge
     * (KEV-174: Selbsthilfe- und Arbeitsgruppen neben dem Text der
     * Vereinsseite). Angegeben werden die deutschen Slugs; Beschriftung und
     * Adresse kommen wie oben aus dem Menü, in der Sprache der Seite. Was
     * nicht im Menü steht, fällt weg.
     *
     * @param  list<string>  $slugs
     * @return list<array{label: string, url: string, bild: ?string, text: ?string, aufrufe: ?int}>
     */
    public static function seiten(array $slugs): array
    {
        $schluessel = self::adressen()
            ->map(fn (?string $adresse) => self::slug($adresse))
            ->flip();

        $menue = collect(Navigation::haupt())
            ->flatMap(fn (array $punkt) => $punkt['children'] ?? [])
            ->flatMap(fn (array $kind) => [$kind, ...($kind['children'] ?? [])])
            ->keyBy('schluessel');

        return self::kacheln(collect($slugs)
            ->map(fn (string $slug) => $menue[$schluessel[$slug] ?? ''] ?? null)
            ->filter()
            ->values()
            ->all());
    }

    /** @param  list<array<string, mixed>>  $kinder  aufgelöste Menüpunkte */
    private static function kacheln(array $kinder): array
    {
        if ($kinder === []) {
            return [];
        }

        $adressen = self::adressen();

        $slugs = collect($kinder)
            ->mapWithKeys(fn (array $kind) => [$kind['schluessel'] => self::slug($adressen[$kind['schluessel']] ?? null)]);

        $deutsche = Page::veroeffentlicht()
            ->standardfassung()
            ->where('locale', Language::standardCode())
            ->whereIn('slug', $slugs->filter()->values())
            ->get()
            ->keyBy('slug');

        $sprache = Language::aktuell()->code;

        $fassungen = $sprache === Language::standardCode()
            ? collect()
            : Page::veroeffentlicht()
                ->standardfassung()
                ->where('locale', $sprache)
                ->whereIn('uebersetzungs_gruppe', $deutsche->pluck('uebersetzungs_gruppe'))
                ->get()
                ->keyBy('uebersetzungs_gruppe');

        $aufrufe = Aufrufe::fuer($deutsche->values());

        return collect($kinder)
            ->map(function (array $kind) use ($slugs, $deutsche, $fassungen, $aufrufe) {
                $deutsch = $deutsche[$slugs[$kind['schluessel']] ?? ''] ?? null;
                $fassung = $deutsch ? ($fassungen[$deutsch->uebersetzungs_gruppe] ?? $deutsch) : null;

                return [
                    'label' => $kind['label'],
                    'url' => $kind['url'],
                    'bild' => $fassung?->titelbild,
                    'text' => $fassung?->untertitel,
                    // Null: kein Seiten-Datensatz (etwa /aktuelles), nichts gezählt.
                    'aufrufe' => $deutsch ? ($aufrufe[$deutsch->uebersetzungs_gruppe] ?? 0) : null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Slug je Menüpunkt aus der Konfiguration: Die aufgelösten Adressen
     * tragen in Fremdsprachen Präfix und übersetzten Slug. Auch eine Ebene
     * tiefer (KEV-238: die Themen der Öffentlichkeitsarbeit).
     */
    private static function adressen(): Collection
    {
        return collect(config('navigation.main'))
            ->flatMap(fn (array $punkt) => $punkt['children'] ?? [])
            ->flatMap(fn (array $kind) => [$kind, ...($kind['children'] ?? [])])
            ->pluck('url', 'schluessel');
    }

    private static function slug(?string $adresse): ?string
    {
        return $adresse && str_starts_with($adresse, '/') ? trim($adresse, '/') : null;
    }
}
