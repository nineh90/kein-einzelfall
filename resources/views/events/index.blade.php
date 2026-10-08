@extends('layouts.app')

@section('title', __('Veranstaltungen'))
@section('description', __('Termine, Selbsthilfegruppen und Veranstaltungen des KE!N EINZELFALL e.V.'))

{{--
    Aufbau wie bei den Inhaltsseiten: Seitenkopf, dann Abschnitte, die ihren
    Rand selbst mitbringen.

    Vorher lag ein eigener Container mit px-4 um die Seite — und die
    eingebetteten Textbausteine brachten denselben Rand nochmal mit. Dadurch
    sprang der Abstand zum Rand je nach Abschnitt, und die Breite wechselte
    zwischen max-w-4xl und max-w-6xl. Deshalb wirkte die Seite unruhig.

    Regel für alle Übersichtsseiten: aussen kein Rand, jeder Abschnitt setzt
    `px-4 md:px-8 lg:px-10` und `max-w-6xl` selbst.
--}}

@section('content')

    <x-layout.seitenkopf
        :titel="__('Veranstaltungen')"
        :bereich="__('Gruppen & Veranstaltungen')"
        :krumen="[
            ['label' => __('rahmen.start'), 'url' => \App\Models\Language::aktuell()->pfad('/')],
            ['label' => __('Veranstaltungen'), 'url' => null],
        ]"
        :lead="__('Termine unserer Selbsthilfe- und Arbeitsgruppen sowie einzelne Veranstaltungen.')"
        :untertitel="__('Termine unserer Selbsthilfe- und Arbeitsgruppen sowie einzelne Veranstaltungen.')"
        :bild="\App\Support\Titelbilder::fuer('veranstaltungen')" />

    {{-- Bestandstext der Altseite. Die Bausteine bringen ihren eigenen Rand
         mit, deshalb stehen sie ausserhalb jedes weiteren Containers. --}}
    {{-- Flächenwechsel wie auf den Inhaltsseiten; der Seitenkopf darüber ist
         eine Karte. Die Terminliste nimmt die Gegenfläche des letzten
         Bausteins — ihre Karten stehen dann auf der jeweils anderen. --}}
    @php
        $abschnitte = $einleitung
            ? \App\Models\PageBlock::abschnitte($einleitung->blocks, davor: 'card')
            : [];
        $listeAuf = \App\Models\PageBlock::gegenflaeche($abschnitte ? end($abschnitte)['flaeche'] : 'card');
        $karte = $listeAuf === 'card' ? 'bg-cream' : 'bg-card';

        // Datum in der Sprache der Seite: Deutsch „Mittwoch, 14. Oktober 2026,
        // 19:00 Uhr“, Englisch „Wednesday, 14 October 2026, 7:00 pm“.
        $sprache = app()->getLocale();
        $langesDatum = app()->isLocale('en') ? 'l, j F Y, g:i a' : 'l, j. F Y, H:i';
    @endphp

    @if ($einleitung)
        <x-bloecke :abschnitte="$abschnitte" art="artikel" />
    @endif

    <div @class(['px-4 md:px-8 py-8 lg:px-10 lg:py-12', 'bg-card border-y border-line' => $listeAuf === 'card'])>
        <div class="mx-auto max-w-6xl">

            <div class="flex flex-wrap items-center justify-between gap-4">
                @if ($anzahlVergangen > 0)
                    <nav aria-label="{{ __('Zeitraum') }}">
                        <ul class="flex gap-2">
                            <li>
                                <a href="{{ sprachlink('events.index') }}"
                                   @if (! $zeigeVergangene) aria-current="page" @endif
                                   class="inline-block rounded-full border border-line px-4 py-1.5 text-sm no-underline
                                          text-ink-soft {{ $listeAuf === 'card' ? 'hover:bg-cream' : 'hover:bg-card' }}
                                          aria-[current=page]:border-green aria-[current=page]:bg-green
                                          aria-[current=page]:text-on-green">
                                    {{ __('Kommende (:anzahl)', ['anzahl' => $anzahlKommend]) }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ sprachlink('events.index', ['zeitraum' => 'vergangen']) }}"
                                   @if ($zeigeVergangene) aria-current="page" @endif
                                   class="inline-block rounded-full border border-line px-4 py-1.5 text-sm no-underline
                                          text-ink-soft {{ $listeAuf === 'card' ? 'hover:bg-cream' : 'hover:bg-card' }}
                                          aria-[current=page]:border-green aria-[current=page]:bg-green
                                          aria-[current=page]:text-on-green">
                                    {{ __('Vergangene (:anzahl)', ['anzahl' => $anzahlVergangen]) }}
                                </a>
                            </li>
                        </ul>
                    </nav>
                @else
                    <span></span>
                @endif

                @if ($anzahlKommend > 0 || $gruppentermine->isNotEmpty())
                    {{-- Die Altseite bietet einen iCal-Export an; die Möglichkeit
                         soll nicht verloren gehen. --}}
                    <a href="{{ sprachlink('events.ical') }}"
                       class="inline-flex items-center gap-2 rounded-full border border-line px-4 py-2
                              text-sm text-ink-soft no-underline {{ $listeAuf === 'card' ? 'hover:bg-cream' : 'hover:bg-card' }}">
                        <x-ui.icon name="arrow-right" :size="16" />
                        {{ __('Alle Termine in den eigenen Kalender') }}
                    </a>
                @endif
            </div>

            {{-- Regelmässige Gruppentreffen stehen vor den Einzelveranstaltungen:
                 Sie finden am häufigsten statt und werden am häufigsten gesucht. --}}
            @if ($gruppentermine->isNotEmpty())
                <section class="mt-8" aria-labelledby="gruppentermine-titel">
                    <h2 id="gruppentermine-titel" class="mb-1 font-display text-xl font-medium text-green">
                        {{ __('Regelmäßige Gruppentreffen') }}
                    </h2>
                    <p class="mb-4 text-sm text-ink-soft">
                        {{ __('Die nächsten Termine unserer Selbsthilfe- und Arbeitsgruppen.') }}
                    </p>

                    <ul class="flex flex-col gap-2">
                        @foreach ($gruppentermine as $eintrag)
                            @php($gruppe = $eintrag['gruppe'])
                            @php($zeit = $eintrag['zeitpunkt'])
                            <li>
                                <article class="flex items-center gap-4 rounded-card border border-line {{ $karte }} p-3 sm:p-4">
                                    <div class="shrink-0 overflow-hidden rounded-xl border border-line text-center"
                                         aria-hidden="true">
                                        <div class="{{ $listeAuf === 'card' ? 'bg-card' : 'bg-cream' }} px-3 py-1 font-display text-lg font-medium text-ink">
                                            {{ $zeit->format('d') }}
                                        </div>
                                        <div class="px-3 py-0.5 text-[0.625rem] uppercase tracking-[0.1em] text-ink-soft">
                                            {{ $zeit->locale($sprache)->isoFormat('MMM') }}
                                        </div>
                                    </div>

                                    <div class="flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-green-mist px-2.5 py-0.5 text-[0.6875rem] text-green-deep">
                                                {{ __(\App\Models\Group::TYPEN[$gruppe->typ] ?? $gruppe->typ) }}
                                            </span>
                                            @if ($gruppe->online)
                                                <span class="rounded-full border border-line px-2.5 py-0.5 text-[0.6875rem] text-ink-soft">
                                                    {{ __('Online') }}
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="mt-1 font-display text-base font-semibold text-ink">
                                            {{ $gruppe->name }}
                                        </h3>

                                        <p class="mt-0.5 text-sm text-ink-soft">
                                            <time datetime="{{ $zeit->toIso8601String() }}">
                                                {{ __(':zeit Uhr', ['zeit' => $zeit->locale($sprache)->translatedFormat($langesDatum)]) }}
                                            </time>
                                            @if ($gruppe->ort)
                                                · {{ $gruppe->ort }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="hidden shrink-0 sm:block">
                                        <x-ui.button :href="$gruppe->url()" variant="ghost" size="sm">
                                            {{ __('Zur Gruppe') }}
                                            <span class="sr-only">– {{ $gruppe->name }}</span>
                                        </x-ui.button>
                                    </div>
                                </article>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="mt-10" aria-labelledby="einzeltermine-titel">
                <h2 id="einzeltermine-titel" class="mb-4 font-display text-xl font-medium text-green">
                    {{ $zeigeVergangene ? __('Vergangene Veranstaltungen') : __('Einzelne Veranstaltungen') }}
                </h2>

                @if ($termine->isEmpty())
                    <div class="rounded-card border border-line {{ $karte }} px-6 py-10 text-center">
                        <p class="text-ink">
                            {{ $zeigeVergangene
                                ? __('Es sind keine vergangenen Termine hinterlegt.')
                                : __('Zurzeit sind keine einzelnen Termine geplant.') }}
                        </p>
                        @unless ($zeigeVergangene)
                            <p class="mt-2 text-sm text-ink-soft">
                                {{-- Der Link steht als Platzhalter im Satz, damit die
                                     Übersetzung ihn an die passende Stelle setzen kann. --}}
                                {!! __('Schau gern später wieder vorbei — oder :link, wenn du Interesse an einer Gruppe hast.', [
                                    'link' => '<a href="'.e(\App\Models\Language::aktuell()->pfad('/anfragen')).'" class="text-green-deep underline">'.e(__('schreib uns')).'</a>',
                                ]) !!}
                            </p>
                        @endunless
                    </div>
                @else
                    <ul class="flex flex-col gap-3">
                        @foreach ($termine as $termin)
                            <li>
                                <article @class([
                                    'flex gap-4 rounded-card border p-4 sm:p-5', $karte,
                                    'border-line' => ! $termin->laeuftGerade(),
                                    'border-green' => $termin->laeuftGerade(),
                                ])>
                                    {{-- Datums-Kachel. aria-hidden, weil das vollständige
                                         Datum weiter unten im <time> steht — sonst liest
                                         der Screenreader es doppelt. --}}
                                    <div class="shrink-0 overflow-hidden rounded-xl border border-line text-center"
                                         aria-hidden="true">
                                        <div class="{{ $listeAuf === 'card' ? 'bg-card' : 'bg-cream' }} px-3 py-1.5 font-display text-xl font-medium text-ink">
                                            {{ $termin->beginnt_am->format('d') }}
                                        </div>
                                        <div class="px-3 py-1 text-[0.6875rem] uppercase tracking-[0.1em] text-ink-soft">
                                            {{ $termin->beginnt_am->locale($sprache)->isoFormat('MMM') }}
                                        </div>
                                    </div>

                                    <div class="flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if ($termin->art)
                                                <span class="rounded-full bg-green-mist px-2.5 py-0.5 text-[0.6875rem] text-green-deep">
                                                    {{ $termin->art }}
                                                </span>
                                            @endif
                                            @if ($termin->laeuftGerade())
                                                <span class="rounded-full bg-green px-2.5 py-0.5 text-[0.6875rem] text-on-green">
                                                    {{ __('läuft gerade') }}
                                                </span>
                                            @endif
                                            @if ($termin->online)
                                                <span class="rounded-full border border-line px-2.5 py-0.5 text-[0.6875rem] text-ink-soft">
                                                    {{ __('Online') }}
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="mt-1 font-display text-lg font-semibold text-ink">
                                            <a href="{{ sprachlink('events.show', $termin->slug) }}"
                                               class="text-ink no-underline hover:underline">
                                                {{ $termin->titel }}
                                            </a>
                                        </h3>

                                        <p class="mt-1 text-sm text-ink-soft">
                                            <time datetime="{{ $termin->zeitMaschinenlesbar() }}">
                                                {{ $termin->zeitraum() }}
                                            </time>
                                            @if ($termin->ort && ! $termin->online)
                                                · {{ $termin->ort }}
                                            @endif
                                        </p>

                                        @if ($termin->teaser)
                                            <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                                                {{ $termin->teaser }}
                                            </p>
                                        @endif
                                    </div>
                                </article>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">{{ $termine->links() }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection
