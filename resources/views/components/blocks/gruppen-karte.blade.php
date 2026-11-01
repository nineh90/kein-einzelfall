@props([
    'gruppe',               // App\Models\Group
    'ebene' => 'h3',        // h3 | h4, je nachdem, ob Zwischenüberschriften darüber stehen
    'auf' => 'cream',       // Fläche des Abschnitts; die Karte hebt sich davon ab
    // Steht der Status schon als Zwischenüberschrift darüber, sagt die Karte
    // ihn nicht noch einmal.
    'status_sichtbar' => false,
    // KEV-309: Den Status gar nicht nennen, nur dass die Anmeldung zu ist.
    'status_nennen' => true,
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

    Seit KEV-280 (Nils) oben das Bild der Gruppenseite, randlos wie bei den
    Seitenkacheln und ausgerichtet wie im Seitenkopf. Seit KEV-305 steht der
    Name im Bild.
--}}
@php
    $offen = $gruppe->istOffen();
    $bild = $gruppe->titelbild;
    $klein = $bild ? preg_replace('/\.webp$/', '-1000.webp', $bild) : null;
    $klein = $klein && $klein !== $bild && \App\Support\Medien::vorhanden($klein) ? $klein : $bild;
@endphp

<article id="{{ $gruppe->slug }}" @class([
    'flex h-full flex-col overflow-hidden rounded-card border border-line',
    'bg-card' => $auf !== 'card',
    'bg-cream' => $auf === 'card',
])>
    {{-- KEV-305 (Taddi): Der Name steht im Bild, rechts im hellen Teil, wie
         bei den Karten auf /verein (KEV-293). Ein heller Verlauf dahinter
         hält ihn lesbar, auch wo das Bild rechts nicht ganz ausläuft
         (Skillin me Softly). Ohne Bild bleibt er unter dem Bild-Platz. --}}
    @if ($bild)
        <span class="relative block aspect-[2/1] overflow-hidden border-b border-line">
            <img src="{{ bildadresse($klein) }}" alt="" loading="lazy" decoding="async"
                 width="1000" height="563"
                 class="h-full w-full object-cover"
                 style="object-position: {{ \App\Support\Titelbilder::fokus($bild) }}">
            <span aria-hidden="true" class="absolute inset-y-0 right-0 w-3/5 bg-gradient-to-l from-cream/85 via-cream/50 to-transparent"></span>
            <span class="absolute inset-y-0 right-0 flex w-3/5 items-center justify-end p-4 text-right lg:p-5">
                <{{ $ebene }} class="hyphens-auto [hyphenate-limit-chars:14_6_6] font-display text-xl font-semibold leading-tight text-ink">{{ $gruppe->name }}</{{ $ebene }}>
            </span>
        </span>
    @endif

    <div class="flex flex-1 flex-col p-5 lg:p-6">
    @php
        // KEV-308 (Taddi): Bei Selbsthilfegruppen statt „Online“ (steht im
        // Text der Seite) für wen die Gruppe ist. Arbeitsgruppen wie bisher.
        $schilder = $gruppe->typ === 'selbsthilfe' ? $gruppe->zielgruppenSchilder() : [];
        $onlineSchild = $gruppe->online && $gruppe->typ !== 'selbsthilfe';
    @endphp
    @if ($gruppe->kuerzel || $onlineSchild || $schilder)
        <div class="mb-3 flex flex-wrap items-center gap-2">
            @if ($gruppe->kuerzel)
                <span class="rounded-full bg-green-mist px-2.5 py-0.5
                             font-display text-[0.6875rem] font-semibold text-green-deep">
                    {{ $gruppe->kuerzel }}
                </span>
            @endif

            @if ($onlineSchild)
                <span class="rounded-full border border-line px-2.5 py-0.5
                             text-[0.6875rem] text-ink-soft">{{ __('Online') }}</span>
            @endif

            @foreach ($schilder as $schild)
                <span class="rounded-full border border-line px-2.5 py-0.5
                             text-[0.6875rem] text-ink-soft">{{ __($schild) }}</span>
            @endforeach
        </div>
    @endif

    @unless ($bild)
        <{{ $ebene }} class="font-display text-lg font-semibold leading-snug text-ink">{{ $gruppe->name }}</{{ $ebene }}>
    @endunless

    @if ($gruppe->teaser)
        <p class="mt-1.5 text-sm leading-relaxed text-ink-soft">{{ $gruppe->teaser }}</p>
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
                    <dt class="shrink-0 text-ink-soft">{{ __('Termin') }}</dt>
                    <dd class="text-ink">{{ $gruppe->wannUndWo() }}</dd>
                </div>
                @if ($naechster = $gruppe->naechsterTermin())
                    <div class="flex gap-2">
                        <dt class="shrink-0 text-ink-soft">{{ __('Nächster Termin') }}</dt>
                        <dd class="font-semibold text-ink">
                            <time datetime="{{ $naechster->toIso8601String() }}">{{ $naechster->locale(app()->getLocale())->isoFormat(app()->isLocale('en') ? 'ddd, D MMM YYYY' : 'dd, D.M.YYYY') }}</time>
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
                {{-- KEV-307: „Zurzeit“ statt „Noch“ (Taddi). --}}
                @if (! $status_sichtbar && $status_nennen)
                    {{ __(':status – zurzeit keine Anmeldung möglich', ['status' => __(\App\Models\Group::STATUS[$gruppe->status] ?? $gruppe->status)]) }}
                @else
                    {{ __('Zurzeit keine Anmeldung möglich') }}
                @endif
            </p>
        @endunless

        {{-- Jede Gruppe hat ihre eigene Seite, auch die in Planung: Taddis
             Text steht dort schon. Selbsthilfegruppen seit KEV-73,
             Arbeitsgruppen seit KEV-84; vorher klappte der AG-Text in der
             Karte auf, und der Mail-Knopf stand hier. Beides steht jetzt
             auf der AG-Seite. Seit KEV-233 steht bei offenen AGs auch hier
             wieder „Anmelden“: Taddi vermisste es auf /arbeitsgruppen. --}}
        <div class="mt-4 flex flex-wrap gap-2">
            @if ($offen && $gruppe->typ === 'arbeits')
                <x-ui.button :href="\App\Models\Group::agAnmeldung(trim($gruppe->kuerzel.': '.$gruppe->name, ': '))" size="sm">
                    {{ __('Anmelden') }}
                    <span class="sr-only">– {{ $gruppe->name }}</span>
                </x-ui.button>
            @endif

            <x-ui.button :href="$gruppe->url()" variant="ghost" size="sm">
                {{ $gruppe->typ === 'arbeits' ? __('Mehr zur AG') : __('Mehr zur Gruppe') }}
                <span class="sr-only">– {{ $gruppe->name }}</span>
            </x-ui.button>
        </div>
    </div>
    </div>
</article>
