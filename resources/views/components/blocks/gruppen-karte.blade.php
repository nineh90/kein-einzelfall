@props([
    'gruppe',               // App\Models\Group
    'ebene' => 'h3',        // h3 | h4, je nachdem, ob Zwischenüberschriften darüber stehen
    'auf' => 'cream',       // Fläche des Abschnitts; die Karte hebt sich davon ab
    // Steht der Status schon als Zwischenüberschrift darüber, sagt die Karte
    // ihn nicht noch einmal.
    'status_sichtbar' => false,
])

{{--
    Eine Gruppe als Karte (x-blocks.group-list).

    Alle Karten sehen gleich aus, egal welchen Status die Gruppe hat. Vorher
    trugen offene Gruppen die Hintergrundfarbe und geplante Weiß, und die
    Seite wirkte wie ein zufälliges Muster, ausgerechnet mit den geplanten
    Gruppen als den auffälligsten. Der Status steht jetzt in der Gliederung
    (group-list teilt nach „offen“ und „in Planung“), nicht in der Farbe.

    Gleicher Aufbau in jeder Karte: Merkmale, Name, Kurzbeschreibung, dann
    unten Termin und Knopf. mt-auto schiebt den Fuß ans Kartenende, damit
    Termine und Knöpfe benachbarter Karten auf einer Linie stehen.
--}}
@php $offen = $gruppe->istOffen(); @endphp

<article @class([
    'flex h-full flex-col rounded-card border border-line p-5 lg:p-6',
    'bg-card' => $auf !== 'card',
    'bg-cream' => $auf === 'card',
])>
    @if ($gruppe->kuerzel || $gruppe->online)
        <div class="mb-3 flex flex-wrap items-center gap-2">
            @if ($gruppe->kuerzel)
                <span class="rounded-full bg-green-mist px-2.5 py-0.5
                             font-display text-[0.6875rem] font-semibold text-green-deep">
                    {{ $gruppe->kuerzel }}
                </span>
            @endif

            @if ($gruppe->online)
                <span class="rounded-full border border-line px-2.5 py-0.5
                             text-[0.6875rem] text-ink-soft">Online</span>
            @endif
        </div>
    @endif

    <{{ $ebene }} class="font-display text-lg font-semibold leading-snug text-ink">{{ $gruppe->name }}</{{ $ebene }}>

    @if ($gruppe->teaser)
        <p class="mt-1.5 text-sm leading-relaxed text-ink-soft">{{ $gruppe->teaser }}</p>
    @endif

    <div class="mt-auto pt-4">
        @if ($gruppe->wannUndWo())
            {{-- Als <dl> statt loser Zeile: Screenreader lesen
                 „Termin: Jeden 4. Mittwoch …" als Paar. --}}
            <dl class="flex gap-2 border-t border-line pt-3 text-sm">
                <dt class="shrink-0 text-ink-soft">Termin</dt>
                <dd class="text-ink">{{ $gruppe->wannUndWo() }}</dd>
            </dl>
        @endif

        @if ($offen)
            @if ($gruppe->anmeldung_hinweis)
                <p class="mt-3 text-sm text-ink-soft">{{ $gruppe->anmeldung_hinweis }}</p>
            @endif

            <div class="mt-4">
                <x-ui.button href="/anfragen" variant="ghost" size="sm">
                    Zu dieser Gruppe anfragen
                    <span class="sr-only">– {{ $gruppe->name }}</span>
                </x-ui.button>
            </div>
        @else
            {{-- Kein Anfrage-Knopf: Er weckte Erwartungen, die noch niemand
                 einlösen kann. Der Hinweis aus dem Panel („In Planung –
                 aktuell noch keine Anmeldung möglich“) sagte dasselbe wie die
                 Zwischenüberschrift darüber, deshalb steht hier ein
                 einheitlicher Satz für alle. --}}
            <p class="border-t border-line pt-3 text-sm text-ink-soft">
                @unless ($status_sichtbar)
                    {{ \App\Models\Group::STATUS[$gruppe->status] ?? $gruppe->status }} –
                    noch keine Anmeldung möglich
                @else
                    Noch keine Anmeldung möglich
                @endunless
            </p>
        @endif
    </div>
</article>
