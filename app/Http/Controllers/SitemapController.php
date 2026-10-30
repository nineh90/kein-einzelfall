<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Group;
use App\Models\OutreachItem;
use App\Models\Language;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Response;

/**
 * sitemap.xml.
 *
 * Erzeugt bei jedem Aufruf neu statt als Datei abgelegt: Der Verein pflegt
 * Seiten im Panel, und eine Sitemap, die jemand nach jeder Änderung von Hand
 * erneuern müsste, ist nach zwei Wochen falsch. Der Aufwand ist eine Handvoll
 * Abfragen — das trägt diese Seitengröße mühelos.
 *
 * Jeder Eintrag führt seine Sprachfassungen über `xhtml:link` mit. Ohne das
 * wertet Google Übersetzungen als doppelten Inhalt, und genau das wollte der
 * Kunde ausdrücklich vermieden wissen.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $eintraege = [
            ...$this->seiten(),
            ...$this->uebersichten(),
            ...$this->beitraege(),
            ...$this->termine(),
            ...$this->gruppen(),
            ...$this->oeffentlichkeitsarbeit(),
        ];

        // Jede Adresse einmal. /veranstaltungen ist zugleich Übersicht und
        // Seite (deren Einleitung im Panel); die Seite steht vorn und gewinnt,
        // sie kennt das Änderungsdatum. /glossar ist seit KEV-155 nur Seite.
        $eintraege = collect($eintraege)->unique('url')->values()->all();

        return response()
            ->view('sitemap', ['eintraege' => $eintraege])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Inhaltsseiten. `noindex` fliegt raus — eine Seite, die nicht in den Index
     * soll, gehört auch nicht in die Sitemap.
     *
     * @return array<int, array{url: string, geaendert: ?string, alternativen: array<string, string>}>
     */
    private function seiten(): array
    {
        $seiten = Page::veroeffentlicht()
            ->where('noindex', false)
            ->get(['id', 'locale', 'fassung', 'slug', 'uebersetzungs_gruppe', 'updated_at']);

        // Nach Übersetzungsgruppe bündeln: alle Sprachfassungen einer Seite
        // verweisen gegenseitig aufeinander.
        $nachGruppe = $seiten
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->groupBy('uebersetzungs_gruppe');

        return $seiten->map(function (Page $seite) use ($nachGruppe) {
            /*
             * Leichte Sprache gehört in die Sitemap — sie soll gefunden werden —
             * aber ohne alternate-Verweise: Sie ist Deutsch, nur anders
             * geschrieben. Ein Private-Use-Subtag wie `de-x-leicht` würde von
             * Google als Fehler gemeldet statt verstanden.
             */
            $geschwister = $seite->istLeichteSprache()
                ? collect()
                : ($nachGruppe[$seite->uebersetzungs_gruppe] ?? collect());

            return [
                'url' => url($seite->pfad()),
                'geaendert' => $seite->updated_at?->toAtomString(),
                'alternativen' => $geschwister
                    ->mapWithKeys(fn (Page $s) => [$s->locale => url($s->pfad())])
                    ->all(),
            ];
        })->all();
    }

    /**
     * Feste Übersichten ohne eigenen Datensatz.
     *
     * Nur auf Deutsch (SEO-Prüfung 28.10.2026): Die Route liefert sie zwar in
     * jeder Sprache aus, die anderen sind aber maschinell übersetzt und tragen
     * noindex (layouts/app), wie die übrigen englischen Seiten. Und Aktuelles
     * erst, wenn es Beiträge gibt; eine leere Liste ist kein Suchtreffer.
     *
     * Die Startseite steht hier nicht mehr: Sie ist ein Datensatz und kommt
     * über `seiten()`. Beides zusammen ergäbe denselben Eintrag zweimal. Ihre
     * hreflang-Verweise nennen jetzt ausserdem die Übersetzungen, die es
     * wirklich gibt, statt der Sprachen, die die Route theoretisch ausliefert.
     */
    private function uebersichten(): array
    {
        $standard = Language::standardCode();

        return collect(['blog.index', 'events.index'])
            ->reject(fn (string $route) => $route === 'blog.index' && ! Post::veroeffentlicht()->exists())
            ->map(fn (string $route) => [
                'url' => url(sprachlink($route, [], $standard)),
                'geaendert' => null,
                'alternativen' => [],
            ])
            ->values()
            ->all();
    }

    private function beitraege(): array
    {
        return Post::veroeffentlicht()
            ->get(['slug', 'updated_at'])
            ->map(fn (Post $beitrag) => [
                'url' => url(sprachlink('blog.show', ['slug' => $beitrag->slug], Language::standardCode())),
                'geaendert' => $beitrag->updated_at?->toAtomString(),
                'alternativen' => [],
            ])
            ->all();
    }

    private function termine(): array
    {
        return Event::veroeffentlicht()
            ->get(['slug', 'updated_at'])
            ->map(fn (Event $termin) => [
                'url' => url(sprachlink('events.show', ['slug' => $termin->slug], Language::standardCode())),
                'geaendert' => $termin->updated_at?->toAtomString(),
                'alternativen' => [],
            ])
            ->all();
    }

    /** Die Seiten der Selbsthilfegruppen (KEV-73) und Arbeitsgruppen (KEV-84). */
    private function gruppen(): array
    {
        return $this->inAllenSprachen(Group::veroeffentlicht()->get());
    }

    /** Die Seiten der Messen, Petitionen, Presseartikel (KEV-134). */
    private function oeffentlichkeitsarbeit(): array
    {
        return $this->inAllenSprachen(OutreachItem::veroeffentlicht()->get());
    }

    /**
     * Gruppen und Öffentlichkeitsarbeit sind übersetzt und stehen seit
     * 30.10.2026 in jeder Sprache im Index: je Sprache ein Eintrag, alle mit
     * denselben alternate-Verweisen.
     *
     * @param  \Illuminate\Support\Collection<int, Group|OutreachItem>  $eintraege
     */
    private function inAllenSprachen($eintraege): array
    {
        $sprachen = Language::aktive()->pluck('code');

        return $eintraege->flatMap(function ($eintrag) use ($sprachen) {
            $alternativen = $sprachen->mapWithKeys(fn (string $code) => [$code => url($eintrag->url($code))])->all();

            return collect($alternativen)->map(fn (string $url) => [
                'url' => $url,
                'geaendert' => $eintrag->updated_at?->toAtomString(),
                'alternativen' => count($alternativen) > 1 ? $alternativen : [],
            ])->values();
        })->all();
    }
}
