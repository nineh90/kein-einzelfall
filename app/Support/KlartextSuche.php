<?php

namespace App\Support;

use App\Models\GlossaryTerm;
use App\Models\Language;
use App\Models\Page;

/**
 * Suche über alle Kategorien unter „Klartext statt Fachsprache“ (KEV-254).
 *
 * Taddis Wunsch: Kacheln für die Kategorien, „aber gleichzeitig eine
 * Suchmaske, die auf alle Kategorien zugreift“. Durchsucht werden die
 * Glossar-Einträge jeder Kategorie und ihre typischen Sätze (KEV-262).
 *
 * Welche Seiten Kategorien sind, steht im Menü (Unterpunkte von „Klartext
 * statt Fachsprache“). Kommt dort eine Seite dazu, wird sie mit durchsucht.
 */
class KlartextSuche
{
    public const SLUG = 'klartext-statt-fachsprache';

    /** Länger tippt niemand einen Begriff oder Satzanfang. */
    public const HOECHSTLAENGE = 100;

    /**
     * Die Kategorien als deutsche Seiten, in der Reihenfolge des Menüs.
     *
     * @return list<Page>
     */
    public static function kategorien(): array
    {
        $klartext = collect(config('navigation.main'))
            ->flatMap(fn (array $punkt) => $punkt['children'] ?? [])
            ->firstWhere('url', '/'.self::SLUG);

        $slugs = collect($klartext['children'] ?? [])
            ->map(fn (array $kind) => trim($kind['url'], '/'))
            ->all();

        $seiten = Page::veroeffentlicht()
            ->standardfassung()
            ->where('locale', Language::standardCode())
            ->whereIn('slug', $slugs)
            ->with('blocks')
            ->get()
            ->keyBy('slug');

        return collect($slugs)->map(fn ($slug) => $seiten[$slug] ?? null)->filter()->values()->all();
    }

    /**
     * Treffer je Kategorie, nur Kategorien mit Treffern.
     *
     * @return list<array{titel: string, url: string, begriffe: list<GlossaryTerm>, saetze: list<array{satz: string, bedeutung: string}>}>
     */
    public static function suchen(string $suche): array
    {
        $gesucht = GlossaryTerm::vereinfacht($suche);

        if ($gesucht === '') {
            return [];
        }

        $sprache = Language::aktuell();
        $gruppen = [];

        foreach (self::kategorien() as $deutsch) {
            $seite = self::fassung($deutsch, $sprache);

            $begriffe = self::begriffe($deutsch, $sprache)->filter->passt($suche)->values()->all();

            // Die Sätze stehen als Baustein auf der Seite, in jeder Sprache
            // mit eigener Übersetzung der Erklärung.
            $saetze = collect($seite->blocks->firstWhere('typ', 'typische_saetze')?->data['formulierungen'] ?? [])
                ->filter(fn ($eintrag) => str_contains(
                    GlossaryTerm::vereinfacht(($eintrag['satz'] ?? '').' '.($eintrag['bedeutung'] ?? '')),
                    $gesucht,
                ))
                ->values()
                ->all();

            if ($begriffe === [] && $saetze === []) {
                continue;
            }

            $gruppen[] = [
                'titel' => $seite->titel,
                'url' => $seite->pfad(),
                'begriffe' => $begriffe,
                'saetze' => $saetze,
            ];
        }

        return $gruppen;
    }

    /** Die Seite in der aktuellen Sprache, sonst die deutsche. */
    private static function fassung(Page $deutsch, Language $sprache): Page
    {
        if ($sprache->istStandard()) {
            return $deutsch;
        }

        return Page::veroeffentlicht()
            ->standardfassung()
            ->where('locale', $sprache->code)
            ->where('uebersetzungs_gruppe', $deutsch->uebersetzungs_gruppe)
            ->with('blocks')
            ->first() ?? $deutsch;
    }

    /**
     * Die Einträge einer Kategorie in der aktuellen Sprache. Gibt es dort
     * keine, die deutschen, wie im Glossar selbst.
     *
     * @return \Illuminate\Support\Collection<int, GlossaryTerm>
     */
    private static function begriffe(Page $deutsch, Language $sprache): \Illuminate\Support\Collection
    {
        foreach (array_unique([$sprache->code, Language::standardCode()]) as $code) {
            $eintraege = GlossaryTerm::veroeffentlicht()->where('locale', $code)->aufSeite($deutsch)->get();

            if ($eintraege->isNotEmpty()) {
                return $eintraege->sortBy(fn ($e) => \Illuminate\Support\Str::ascii($e->kuerzel ?: $e->begriff), SORT_NATURAL | SORT_FLAG_CASE)->values();
            }
        }

        return collect();
    }
}
