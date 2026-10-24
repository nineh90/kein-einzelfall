<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Group;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Alle Termine des Vereins in einem Monat, für den Kalender (KEV-224):
 * Veranstaltungen und die Treffen der offenen Gruppen mit festem Rhythmus.
 *
 * Dieselben Quellen wie /veranstaltungen (EventController). Die Gruppentreffen
 * werden berechnet, nicht gespeichert, und sind damit nie veraltet. Gepflegt
 * wird nichts zusätzlich.
 */
class Termine
{
    /**
     * @return Collection<int, array{zeit: CarbonImmutable, titel: string, url: string, art: string, ganztaegig: bool}>
     */
    public static function imMonat(CarbonImmutable $monat): Collection
    {
        $start = $monat->startOfMonth();
        $ende = $monat->endOfMonth();

        $veranstaltungen = Event::veroeffentlicht()
            ->whereBetween('beginnt_am', [$start, $ende])
            ->orderBy('beginnt_am')
            ->get()
            ->map(fn (Event $e) => [
                'zeit' => CarbonImmutable::instance($e->beginnt_am),
                'titel' => $e->titel,
                'url' => sprachlink('events.show', ['slug' => $e->slug]),
                'art' => 'veranstaltung',
                'ganztaegig' => (bool) $e->ganztaegig,
            ]);

        $gruppentreffen = Group::veroeffentlicht()
            ->where('status', 'offen')
            ->whereNot('wiederholung', 'keine')
            ->get()
            // Berechnete Treffen nur ab jetzt: Ob eins vorige Woche wirklich
            // stattfand, weiß die Rechnung nicht (Skillin me Softly startet
            // erst im November, KEV-230).
            ->flatMap(fn (Group $g) => $g->naechsteTermine(6, $start->max(CarbonImmutable::now())->subSecond())
                ->filter(fn (CarbonImmutable $t) => $t->between($start, $ende))
                ->map(fn (CarbonImmutable $t) => [
                    'zeit' => $t,
                    'titel' => $g->name,
                    'url' => $g->url(),
                    'art' => $g->typ === 'arbeits' ? 'arbeitsgruppe' : 'selbsthilfegruppe',
                    'ganztaegig' => false,
                ]));

        return $veranstaltungen->concat($gruppentreffen)->sortBy('zeit')->values();
    }
}
