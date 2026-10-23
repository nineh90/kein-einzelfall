<?php

namespace App\Support;

use App\Models\Language;
use App\Models\Page;

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
        $kinder = Seitenkontext::fuer($seite->slug)->geschwister(PHP_INT_MAX);

        if ($kinder === []) {
            return [];
        }

        // Slug je Menüpunkt aus der Konfiguration: Die aufgelösten Adressen
        // tragen in Fremdsprachen Präfix und übersetzten Slug.
        $adressen = collect(config('navigation.main'))
            ->flatMap(fn (array $punkt) => $punkt['children'] ?? [])
            ->pluck('url', 'schluessel');

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

    private static function slug(?string $adresse): ?string
    {
        return $adresse && str_starts_with($adresse, '/') ? trim($adresse, '/') : null;
    }
}
