@props([
    'titel' => null,
    'einleitung' => null,
    'typ' => 'selbsthilfe',   // selbsthilfe | arbeits
    'auf' => 'cream',
])

@php
    $gruppen = \App\Models\Group::veroeffentlicht()
        ->vomTyp($typ)
        ->orderBy('position')
        ->orderBy('name')
        ->get();
@endphp

@if ($gruppen->isNotEmpty())
    {{--
        Übersicht der Gruppen.

        Bewusst ohne Anmeldefunktion: Wer sich zu einer Selbsthilfegruppe
        anmeldet, offenbart damit Angaben nach Art. 9 DSGVO. Das braucht ein
        eigenes Konzept mit Löschfristen und Zugriffsregelung — hier steht
        nur, wie man Kontakt aufnimmt.
    --}}
    <section @class([
        'px-4 py-8 lg:px-10 lg:py-12',
        'bg-card border-y border-line' => $auf === 'card',
    ]) aria-labelledby="gruppen-{{ $typ }}-titel">
        <div class="mx-auto max-w-6xl">
            @if ($titel)
                <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green"></span>
                <h2 id="gruppen-{{ $typ }}-titel"
                    class="mb-4 font-display text-2xl font-medium text-ink lg:text-3xl">
                    {{ $titel }}
                </h2>
            @endif

            @if ($einleitung)
                <p class="mb-8 max-w-prose leading-relaxed text-ink-soft">{{ $einleitung }}</p>
            @endif

            <ul class="grid gap-4 lg:grid-cols-2">
                @foreach ($gruppen as $gruppe)
                    <li class="flex">
                        <article @class([
                            'flex flex-1 flex-col rounded-card border bg-cream p-5',
                            'border-line' => $gruppe->istOffen(),
                            // Geplante und geschlossene Gruppen treten optisch zurück,
                            // damit klar ist, wo man tatsächlich mitmachen kann.
                            'border-line opacity-75' => ! $gruppe->istOffen(),
                        ])>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                @if ($gruppe->kuerzel)
                                    <span class="rounded-full bg-green-mist px-2.5 py-0.5
                                                 font-display text-[0.6875rem] font-semibold text-green-deep">
                                        {{ $gruppe->kuerzel }}
                                    </span>
                                @endif

                                @unless ($gruppe->istOffen())
                                    <span class="rounded-full border border-line px-2.5 py-0.5
                                                 text-[0.6875rem] text-ink-soft">
                                        {{ \App\Models\Group::STATUS[$gruppe->status] ?? $gruppe->status }}
                                    </span>
                                @endunless

                                @if ($gruppe->online)
                                    <span class="rounded-full border border-line px-2.5 py-0.5
                                                 text-[0.6875rem] text-ink-soft">Online</span>
                                @endif
                            </div>

                            <h3 class="font-display text-lg font-semibold text-ink">{{ $gruppe->name }}</h3>

                            @if ($gruppe->teaser)
                                <p class="mt-1.5 flex-1 text-sm leading-relaxed text-ink-soft">
                                    {{ $gruppe->teaser }}
                                </p>
                            @endif

                            @if ($gruppe->wannUndWo())
                                {{-- Als <dl> statt loser Zeile: Screenreader lesen
                                     „Termin: Jeden 4. Mittwoch …" als Paar. --}}
                                <dl class="mt-3 flex gap-2 border-t border-line pt-3 text-sm">
                                    <dt class="shrink-0 text-ink-soft">Termin</dt>
                                    <dd class="text-ink">{{ $gruppe->wannUndWo() }}</dd>
                                </dl>
                            @endif

                            @if ($gruppe->anmeldung_hinweis)
                                <p class="mt-3 text-sm text-ink-soft">{{ $gruppe->anmeldung_hinweis }}</p>
                            @endif

                            @if ($gruppe->istOffen())
                                <div class="mt-4">
                                    <x-ui.button href="/anfragen" variant="ghost" size="sm">
                                        Zu dieser Gruppe anfragen
                                        <span class="sr-only">– {{ $gruppe->name }}</span>
                                    </x-ui.button>
                                </div>
                            @endif
                        </article>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
