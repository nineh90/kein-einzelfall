@props([
    'auf' => 'cream',  // cream | card
])

@php
    use App\Models\Frist;

    $sprache = \App\Models\Language::aktuell();

    // Länger tippt niemand, wonach er sucht.
    $suche = mb_substr(trim((string) request()->query('suche', '')), 0, 100);

    $gruppen = Frist::nachBuchstaben();
    $gesamt = $gruppen->flatten(1)->count();
    $passend = $gruppen->map(fn ($fristen) => $fristen->filter->passt($suche));
    $gefunden = $passend->flatten(1)->count();

    $hier = request()->getPathInfo();
    $suchPfad = $sprache->pfad('/suche');
    $anker = fn (string $buchstabe) => 'buchstabe-'.($buchstabe === '#' ? 'zeichen' : Str::lower($buchstabe));

    // Karten stehen auf der jeweils anderen Fläche.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';
@endphp

{{--
    Fristenübersicht (KEV-152), nach Taddis Vorschlag: oben die Suche „Welche
    Frist suchst du?“, darunter A–Z, darunter alle Fristen alphabetisch, jede
    als aufklappbare Karte mit kleinen Schlagworten.

    Die Einträge pflegt der Verein im Panel unter „Fristen“ (App\Models\Frist).

    Suche, Buchstabenleiste und Filtern beim Tippen sind die des Glossars:
    dieselben data-glossar-*-Attribute, dasselbe Skript (glossar.js), ohne
    JavaScript filtert der Server über ?suche=. Mit einer Suche stehen die
    passenden Karten offen, sonst wären die Treffer nur Überschriften.

    Natives <details>: ohne JavaScript bedienbar, Vorlesehilfen kennen es, und
    der Text bleibt im Dokument, die Suche des Browsers findet ihn. Ein Link
    auf /fristen#widerspruch klappt die Karte auf (aufklapper.js).
--}}
<div @class(['px-4 md:px-8 py-8 lg:px-10 lg:py-12', 'bg-card border-y border-line' => $auf === 'card'])>
    <div class="mx-auto max-w-6xl">

        @if ($gruppen->isEmpty())
            <p class="max-w-prose leading-relaxed text-ink-soft">
                {{ __('Hier entsteht eine Übersicht der Fristen. Sie wird nach und nach ergänzt.') }}
            </p>
        @else
            <form role="search" method="get" action="{{ $hier }}" class="mb-8 max-w-xl"
                  data-glossar-suche
                  data-gesamt="{{ $gesamt }}"
                  data-text-alle="{{ __(':gesamt Fristen') }}"
                  data-text-gefiltert="{{ __('Treffer: :anzahl von :gesamt') }}">
                <label for="glossar-suche" class="mb-2 block font-display text-lg text-ink">
                    {{ __('Welche Frist suchst du?') }}
                </label>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <input type="search"
                           id="glossar-suche"
                           name="suche"
                           value="{{ $suche }}"
                           maxlength="100"
                           autocomplete="off"
                           spellcheck="false"
                           aria-describedby="glossar-treffer"
                           class="w-full rounded-full border border-line bg-cream px-5 py-3 text-ink
                                  placeholder:text-ink-soft focus:border-green focus:outline-none">

                    <x-ui.button type="submit" variant="primary" class="shrink-0">{{ __('Suchen') }}</x-ui.button>
                </div>

                <p class="mt-2 text-sm text-ink-soft">
                    <span id="glossar-treffer" aria-live="polite" data-glossar-status>{{ $suche === ''
                        ? __(':gesamt Fristen', ['gesamt' => $gesamt])
                        : __('Treffer: :anzahl von :gesamt', ['anzahl' => $gefunden, 'gesamt' => $gesamt]) }}</span>
                    <a href="{{ $hier }}" data-glossar-zuruecksetzen @if ($suche === '') hidden @endif
                       class="ms-2 text-green-deep underline">{{ __('Alle zeigen') }}</a>
                </p>
            </form>

            <div data-glossar-leer @if ($suche === '' || $gefunden > 0) hidden @endif
                 class="mb-10 max-w-prose leading-relaxed text-ink-soft">
                <p>{{ __('Dazu steht hier noch nichts.') }}</p>
                <p class="mt-2">
                    <a href="{{ $suchPfad }}?q={{ urlencode($suche) }}" data-glossar-websuche
                       data-basis="{{ $suchPfad }}?q="
                       class="text-green-deep underline">{{ __('Auf der ganzen Website suchen') }}</a>
                </p>
            </div>

            <nav aria-label="{{ __('Nach Anfangsbuchstaben springen') }}" class="mb-10">
                <ul class="flex flex-wrap gap-1.5">
                    @foreach ($gruppen->keys() as $buchstabe)
                        <li data-glossar-sprung="{{ $anker($buchstabe) }}" @if ($passend[$buchstabe]->isEmpty()) hidden @endif>
                            <a href="#{{ $anker($buchstabe) }}"
                               class="inline-flex h-9 w-9 items-center justify-center rounded-full
                                      border border-line font-display text-sm text-ink no-underline
                                      hover:border-green hover:bg-green hover:text-on-green">
                                {{ $buchstabe }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="max-w-3xl">
                @foreach ($gruppen as $buchstabe => $fristen)
                    <section class="mb-10" aria-labelledby="{{ $anker($buchstabe) }}"
                             data-glossar-abschnitt @if ($passend[$buchstabe]->isEmpty()) hidden @endif>

                        <h2 id="{{ $anker($buchstabe) }}"
                            class="mb-3 scroll-mt-24 border-b border-line pb-2 font-display text-xl
                                   font-medium text-green-deep">
                            {{ $buchstabe }}
                        </h2>

                        <div class="flex flex-col gap-2">
                            @foreach ($fristen as $frist)
                                @php $trifft = $frist->passt($suche); @endphp
                                <details id="{{ $frist->slug }}"
                                         data-glossar-eintrag
                                         data-kuerzel=""
                                         data-suchtext="{{ $frist->suchtext() }}"
                                         @unless ($trifft) hidden @endunless
                                         @if ($suche !== '' && $trifft) open @endif
                                         class="group scroll-mt-24 rounded-card border border-line {{ $innen }} open:border-green">
                                    <summary class="flex cursor-pointer items-start gap-3 px-5 py-4
                                                    marker:content-none [&::-webkit-details-marker]:hidden">
                                        <span class="flex-1">
                                            <span class="block font-display text-lg font-medium text-ink group-hover:underline">
                                                {{ $frist->titel }}
                                            </span>

                                            @if (filled($frist->frist))
                                                <span class="mt-1 flex items-start gap-1.5 text-[0.9375rem] text-green-deep">
                                                    <span class="mt-0.5 shrink-0"><x-ui.icon name="clock" :size="16" /></span>
                                                    <span>{!! nl2br(e($frist->frist)) !!}</span>
                                                </span>
                                            @endif

                                            {{-- Schlagworte: nur zum Einordnen und Suchen,
                                                 keine Knöpfe. --}}
                                            @if (filled($frist->schlagworte))
                                                <span class="mt-2 flex flex-wrap gap-1.5">
                                                    <span class="sr-only">{{ __('Schlagworte:') }}</span>
                                                    @foreach ($frist->schlagworte as $wort)
                                                        <span class="rounded-full border border-line px-2.5 py-0.5 text-xs text-ink-soft">{{ $wort }}</span>
                                                    @endforeach
                                                </span>
                                            @endif
                                        </span>

                                        <span class="mt-1 shrink-0 text-ink-soft transition-transform group-open:rotate-180">
                                            <x-ui.icon name="chevron-down" :size="20" />
                                        </span>
                                    </summary>

                                    {{-- Aus dem Editor im Panel, wie die Antworten im
                                         Baustein „Fragen und Antworten“. --}}
                                    <div class="border-t border-line px-5 py-4 leading-relaxed text-ink-soft
                                                [&_a]:text-green-deep [&_a]:underline [&_strong]:font-semibold [&_strong]:text-ink
                                                [&_h3]:mb-2 [&_h3]:mt-5 [&_h3]:font-display [&_h3]:text-lg [&_h3]:font-medium [&_h3]:text-ink
                                                [&_li]:mb-1 [&_ol]:mb-3 [&_ol]:list-decimal [&_ol]:pl-5
                                                [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:mb-3 [&_ul]:list-disc [&_ul]:pl-5">
                                        {!! $frist->inhalt !!}
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</div>
