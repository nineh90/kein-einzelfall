@extends('layouts.app')

@section('title', 'Veranstaltungen')
@section('description', 'Termine, Selbsthilfegruppen und Veranstaltungen des KE!N EINZELFALL e.V.')

@section('content')
<div class="px-4 py-8 lg:px-10 lg:py-12">
    <div class="mx-auto max-w-4xl">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <h1 class="font-display text-[1.75rem] font-medium text-ink lg:text-4xl">
                Veranstaltungen
            </h1>

            @if ($anzahlKommend > 0)
                {{-- Die Altseite bietet einen iCal-Export an; die Möglichkeit
                     soll nicht verloren gehen. --}}
                <a href="{{ route('events.ical') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-line px-4 py-2
                          text-sm text-ink-soft no-underline hover:bg-card">
                    <x-ui.icon name="arrow-right" :size="16" />
                    Alle Termine in den eigenen Kalender
                </a>
            @endif
        </div>

        {{-- Bestandstext der Altseite. Wird über das Panel gepflegt wie jede
             andere Seite auch. --}}
        @if ($einleitung)
            @foreach ($einleitung->blocks as $block)
                <x-block :block="$block" />
            @endforeach
        @endif

        @if ($anzahlVergangen > 0)
            <nav aria-label="Zeitraum" class="mt-6">
                <ul class="flex gap-2">
                    <li>
                        <a href="{{ route('events.index') }}"
                           @if (! $zeigeVergangene) aria-current="page" @endif
                           class="inline-block rounded-full border border-line px-4 py-1.5 text-sm no-underline
                                  text-ink-soft hover:bg-card
                                  aria-[current=page]:border-green aria-[current=page]:bg-green
                                  aria-[current=page]:text-on-green">
                            Kommende ({{ $anzahlKommend }})
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('events.index', ['zeitraum' => 'vergangen']) }}"
                           @if ($zeigeVergangene) aria-current="page" @endif
                           class="inline-block rounded-full border border-line px-4 py-1.5 text-sm no-underline
                                  text-ink-soft hover:bg-card
                                  aria-[current=page]:border-green aria-[current=page]:bg-green
                                  aria-[current=page]:text-on-green">
                            Vergangene ({{ $anzahlVergangen }})
                        </a>
                    </li>
                </ul>
            </nav>
        @endif

        {{-- Regelmässige Gruppentreffen. Stehen bewusst vor den
             Einzelveranstaltungen: Sie sind das, was am häufigsten stattfindet
             und wonach am häufigsten gefragt wird. --}}
        @if ($gruppentermine->isNotEmpty())
            <section class="mt-8" aria-labelledby="gruppentermine-titel">
                <h2 id="gruppentermine-titel" class="mb-1 font-display text-xl font-medium text-ink">
                    Regelmässige Gruppentreffen
                </h2>
                <p class="mb-4 text-sm text-ink-soft">
                    Die nächsten Termine unserer Selbsthilfe- und Arbeitsgruppen.
                </p>

                <ul class="flex flex-col gap-2">
                    @foreach ($gruppentermine as $eintrag)
                        @php($gruppe = $eintrag['gruppe'])
                        @php($zeit = $eintrag['zeitpunkt'])
                        <li>
                            <article class="flex items-center gap-4 rounded-card border border-line bg-card p-3 sm:p-4">
                                <div class="shrink-0 overflow-hidden rounded-xl border border-line text-center"
                                     aria-hidden="true">
                                    <div class="bg-cream px-3 py-1 font-display text-lg font-medium text-ink">
                                        {{ $zeit->format('d') }}
                                    </div>
                                    <div class="px-3 py-0.5 text-[0.625rem] uppercase tracking-[0.1em] text-ink-soft">
                                        {{ $zeit->locale('de')->isoFormat('MMM') }}
                                    </div>
                                </div>

                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-green-mist px-2.5 py-0.5 text-[0.6875rem] text-green-deep">
                                            {{ \App\Models\Group::TYPEN[$gruppe->typ] ?? $gruppe->typ }}
                                        </span>
                                        @if ($gruppe->online)
                                            <span class="rounded-full border border-line px-2.5 py-0.5 text-[0.6875rem] text-ink-soft">
                                                Online
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="mt-1 font-display text-base font-semibold text-ink">
                                        {{ $gruppe->name }}
                                    </h3>

                                    <p class="mt-0.5 text-sm text-ink-soft">
                                        <time datetime="{{ $zeit->toIso8601String() }}">
                                            {{ $zeit->locale('de')->isoFormat('dddd, D. MMMM YYYY, HH:mm') }} Uhr
                                        </time>
                                        @if ($gruppe->ort)
                                            · {{ $gruppe->ort }}
                                        @endif
                                    </p>
                                </div>

                                <div class="hidden shrink-0 sm:block">
                                    <x-ui.button href="/selbsthilfegruppen" variant="ghost" size="sm">
                                        Zur Gruppe
                                        <span class="sr-only">– {{ $gruppe->name }}</span>
                                    </x-ui.button>
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ul>
            </section>

            <h2 id="einzeltermine-titel" class="mb-1 mt-10 font-display text-xl font-medium text-ink">
                Einzelne Veranstaltungen
            </h2>
        @endif

        @if ($termine->isEmpty())
            <div class="mt-8 rounded-card border border-line bg-card px-6 py-10 text-center">
                <p class="text-ink">
                    {{ $zeigeVergangene
                        ? 'Es sind keine vergangenen Termine hinterlegt.'
                        : 'Zurzeit sind keine Termine geplant.' }}
                </p>
                @unless ($zeigeVergangene)
                    <p class="mt-2 text-sm text-ink-soft">
                        Schau gern später wieder vorbei — oder
                        <a href="/anfragen" class="text-green-deep underline">schreib uns</a>,
                        wenn du Interesse an einer Gruppe hast.
                    </p>
                @endunless
            </div>
        @else
            <ul class="mt-8 flex flex-col gap-3">
                @foreach ($termine as $termin)
                    <li>
                        <article @class([
                            'flex gap-4 rounded-card border bg-card p-4 sm:p-5',
                            'border-line' => ! $termin->laeuftGerade(),
                            'border-green' => $termin->laeuftGerade(),
                        ])>
                            {{-- Datums-Kachel wie im Mockup. aria-hidden, weil das
                                 vollständige Datum weiter unten im <time> steht —
                                 sonst liest der Screenreader es doppelt. --}}
                            <div class="shrink-0 overflow-hidden rounded-xl border border-line text-center"
                                 aria-hidden="true">
                                <div class="bg-cream px-3 py-1.5 font-display text-xl font-medium text-ink">
                                    {{ $termin->beginnt_am->format('d') }}
                                </div>
                                <div class="px-3 py-1 text-[0.6875rem] uppercase tracking-[0.1em] text-ink-soft">
                                    {{ $termin->beginnt_am->translatedFormat('M') }}
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
                                            läuft gerade
                                        </span>
                                    @endif
                                    @if ($termin->online)
                                        <span class="rounded-full border border-line px-2.5 py-0.5 text-[0.6875rem] text-ink-soft">
                                            Online
                                        </span>
                                    @endif
                                </div>

                                <h3 class="mt-1 font-display text-lg font-semibold text-ink">
                                    <a href="{{ route('events.show', $termin->slug) }}"
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
    </div>
</div>
@endsection
