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

<article id="{{ $gruppe->slug }}" @class([
    'flex h-full scroll-mt-24 flex-col rounded-card border border-line p-5 lg:p-6',
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

    {{-- Der ausführliche Text aufklappbar, wie bei den Teamkarten: Acht AGs
         mit je vier, fünf Absätzen erschlügen die Seite (KEV-74). Natives
         <details>, also auch ohne JavaScript lesbar. --}}
    {{-- Nur bei AGs: Selbsthilfegruppen haben dafür ihre eigene Seite (KEV-73). --}}
    @if ($gruppe->typ === 'arbeits' && filled(strip_tags((string) $gruppe->beschreibung)))
        <details class="group/mehr mt-3">
            <summary class="inline-flex cursor-pointer items-center gap-1.5 text-sm text-green-deep
                            marker:content-none [&::-webkit-details-marker]:hidden">
                <span class="group-open/mehr:hidden">Mehr über die AG lesen</span>
                <span class="hidden group-open/mehr:inline">Weniger anzeigen</span>
                <span class="sr-only">– {{ $gruppe->name }}</span>
                <span class="transition-transform group-open/mehr:rotate-180">
                    <x-ui.icon name="chevron-down" :size="16" />
                </span>
            </summary>

            <div class="mt-3 text-sm leading-relaxed text-ink-soft
                        [&_a]:whitespace-nowrap [&_a]:text-green-deep [&_a]:underline [&_p]:mb-3 [&_p:last-child]:mb-0">
                {!! $gruppe->beschreibung !!}
            </div>

            @if ($gruppe->schlusssatz)
                <p class="mt-4 font-hand text-xl leading-snug text-green">{{ $gruppe->schlusssatz }}</p>
            @endif
        </details>
    @endif

    <div class="mt-auto pt-4">
        {{-- Ohne Zeit und Ort nicht nur „Termin: online“: Das sagt das
             Schild oben schon (die AGs haben keinen festen Termin). Gruppen,
             die noch nicht laufen, nennen keinen Rhythmus: Er läse sich wie
             eine Einladung (KEV-73). --}}
        @if ($offen && ($gruppe->rhythmus || $gruppe->uhrzeit || $gruppe->ort))
            {{-- Als <dl> statt loser Zeile: Screenreader lesen
                 „Termin: Jeden 4. Mittwoch …" als Paar. --}}
            <dl class="flex flex-col gap-1.5 border-t border-line pt-3 text-sm">
                <div class="flex gap-2">
                    <dt class="shrink-0 text-ink-soft">Termin</dt>
                    <dd class="text-ink">{{ $gruppe->wannUndWo() }}</dd>
                </div>
                @if ($naechster = $gruppe->naechsterTermin())
                    <div class="flex gap-2">
                        <dt class="shrink-0 text-ink-soft">Nächster Termin</dt>
                        <dd class="font-semibold text-ink">
                            <time datetime="{{ $naechster->toIso8601String() }}">{{ $naechster->locale('de')->isoFormat('dd, D.M.YYYY') }}</time>
                        </dd>
                    </div>
                @endif
            </dl>
        @endif

        @if ($offen && $gruppe->anmeldung_hinweis)
            <p class="mt-3 text-sm text-ink-soft">{{ $gruppe->anmeldung_hinweis }}</p>
        @endif

        @unless ($offen)
            {{-- Kein Anmelde-Knopf: Er weckte Erwartungen, die noch niemand
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
        @endunless

        @if ($gruppe->typ === 'selbsthilfe')
            {{-- Jede Selbsthilfegruppe hat ihre eigene Seite (KEV-73), auch
                 die in Planung: Taddis Text steht dort schon. --}}
            <div class="mt-4">
                <x-ui.button :href="$gruppe->url()" variant="ghost" size="sm">
                    Mehr zur Gruppe
                    <span class="sr-only">– {{ $gruppe->name }}</span>
                </x-ui.button>
            </div>
        @elseif ($offen)
            {{-- Die AGs laufen über ihr eigenes Postfach, so steht es
                 in jedem AG-Text (KEV-74). Der Betreff nennt die AG. --}}
            <div class="mt-4">
                <x-ui.button :href="'mailto:'.\App\Models\Group::AG_ADRESSE.'?subject='.rawurlencode(trim($gruppe->kuerzel.': '.$gruppe->name, ': '))"
                             variant="ghost" size="sm">
                    Per E-Mail mitmachen
                    <span class="sr-only">– {{ $gruppe->name }}</span>
                </x-ui.button>
            </div>
        @endif
    </div>
</article>
