<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Wie oft eine Seite aufgerufen wurde (KEV-156).
 *
 * Taddis Frage zum „Raum für Ungesagtes“: „Gibts die Möglichkeit, dass man
 * sehen kann, wie oft jedes einzelne angeklickt wird?“ Die Zahl steht im Panel
 * in der Seitenliste und, wo der Verein es einschaltet, auf den Kacheln des
 * Bausteins „Unterseiten als Kacheln“.
 *
 * Gezählt wird auf dem Server und nur die Zahl: keine Adresse, keine Uhrzeit,
 * kein Cookie, nichts im Browser. Daraus lässt sich über niemanden etwas
 * ablesen, und es braucht keine Einwilligung. Das ist der Unterschied zu
 * einem Besucherzähler mit Kennung, und für diese Website der einzig
 * vertretbare.
 *
 * Nicht gezählt werden Suchmaschinen und andere Programme (soweit sie sich zu
 * erkennen geben), Vorab-Ladevorgänge des Browsers und angemeldete
 * Vereinsmitglieder, die ihre Seiten im Panel prüfen. Wer neu lädt, zählt
 * neu: Ohne Kennung lässt sich ein zweiter Aufruf nicht vom ersten
 * unterscheiden. Die Zahl ist deshalb ein Richtwert, keine Besucherzahl.
 */
class Aufrufe
{
    /** Kennungen von Suchmaschinen, Vorschaudiensten und Werkzeugen. */
    private const PROGRAMME = '/bot|crawl|spider|slurp|preview|facebookexternalhit|embedly|monitor|'
        .'lighthouse|headless|curl|wget|python|java\/|go-http|okhttp|axios|node-fetch|scrapy|httpclient/i';

    public static function zaehlen(Page $seite, Request $anfrage): void
    {
        if (! self::zaehlt($anfrage)) {
            return;
        }

        // Am Query-Builder vorbei: Eloquent setzte sonst `updated_at`, und die
        // Sitemap meldete jede gelesene Seite als geändert.
        DB::table('pages')->where('id', $seite->getKey())->increment('aufrufe');
    }

    public static function zaehlt(Request $anfrage): bool
    {
        if (! $anfrage->isMethod('GET')) {
            return false;
        }

        // Vorab geladene Seiten (Chrome, Firefox) hat noch niemand gesehen.
        $zweck = strtolower($anfrage->header('Sec-Purpose', '').$anfrage->header('Purpose', '').$anfrage->header('X-Moz', ''));
        if (str_contains($zweck, 'prefetch') || str_contains($zweck, 'prerender')) {
            return false;
        }

        $kennung = (string) $anfrage->userAgent();
        if ($kennung === '' || preg_match(self::PROGRAMME, $kennung)) {
            return false;
        }

        return ! auth()->check();
    }

    /**
     * Aufrufe je Seite, über alle Sprachen zusammen: /wall-of-shame und
     * /en/wall-of-shame sind dieselbe Kategorie. Zusammengefasst über die
     * Übersetzungsgruppe, nicht über den Slug, der je Sprache ein anderer sein
     * darf.
     *
     * @param  iterable<Page>  $seiten
     * @return array<string, int> Übersetzungsgruppe → Aufrufe
     */
    public static function fuer(iterable $seiten): array
    {
        $gruppen = collect($seiten)->pluck('uebersetzungs_gruppe')->filter()->unique()->values();

        if ($gruppen->isEmpty()) {
            return [];
        }

        return Page::query()
            ->whereIn('uebersetzungs_gruppe', $gruppen)
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->groupBy('uebersetzungs_gruppe')
            ->selectRaw('uebersetzungs_gruppe, SUM(aufrufe) AS summe')
            ->pluck('summe', 'uebersetzungs_gruppe')
            ->map(fn ($summe) => (int) $summe)
            ->all();
    }
}
