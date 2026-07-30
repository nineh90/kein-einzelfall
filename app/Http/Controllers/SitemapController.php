<?php

namespace App\Http\Controllers;

use App\Models\Event;
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
        ];

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
     * Sie existieren in jeder freigeschalteten Sprache, weil die Route sie in
     * jeder Sprache ausliefert — auch wenn die Inhalte darunter noch deutsch sind.
     *
     * Die Startseite steht hier nicht mehr: Sie ist ein Datensatz und kommt
     * über `seiten()`. Beides zusammen ergäbe denselben Eintrag zweimal. Ihre
     * hreflang-Verweise nennen jetzt ausserdem die Übersetzungen, die es
     * wirklich gibt, statt der Sprachen, die die Route theoretisch ausliefert.
     */
    private function uebersichten(): array
    {
        $sprachen = Language::aktive();

        return collect(['blog.index', 'events.index'])
            ->map(fn (string $route) => $sprachen->map(fn (Language $s) => [
                'url' => url(sprachlink($route, [], $s->code)),
                'geaendert' => null,
                'alternativen' => $sprachen
                    ->mapWithKeys(fn (Language $a) => [$a->code => url(sprachlink($route, [], $a->code))])
                    ->all(),
            ])->all())
            ->flatten(1)
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
}
