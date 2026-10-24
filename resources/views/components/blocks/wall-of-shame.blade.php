@props([
    'titel' => null,
    'saetze' => [],
    'bild' => null,
    'schluss' => null,
    'auf' => 'cream',
])

{{--
    Wall of Shame (KEV-157).

    Taddis Idee: eine rissige Wand, davor ein Papierstapel. Jeder Klick auf den
    Stapel zeigt den nächsten Satz und setzt einen Zählstrich an die Wand,
    immer vier und ein Querstrich wie im Logo. Mit jedem Satz wird die Wand
    dunkler und voller, sie soll bedrohlich wirken.

    Aufbau:
    - Die Wand gibt es nur mit JavaScript (resources/js/wall-of-shame.js). Bis
      dahin ist sie `hidden`; ohne Skript wäre der Knopf eine Attrappe.
    - Darunter stehen alle Sätze als Liste zum Aufklappen. Ohne JavaScript ist
      das der Weg, mit Vorlesehilfe oft der bequemere, und die Suche der
      Website findet die Sätze darüber.
    - Erst ein Klick zeigt etwas. Wen diese Sätze treffen, entscheidet selbst,
      ob und wie viele er liest; nichts springt ungefragt ins Bild.
    - Wie weit jemand gelesen hat, merkt sich der Browser 30 Tage (KEV-163).
      Beim Wiederkommen steht auf dem Zettel nur der Stand, der nächste Satz
      kommt wieder erst mit dem Klick. „Von vorn“ setzt ihn zurück.
    - Wächst die Liste, werden die Striche kleiner, damit alle auf die Wand
      passen. Die Wand selbst wächst nicht mit.
    - Feste Farben statt der Seitenfarben: Die Wand ist ein Bild, und der
      Zettel darauf ist Papier, auch im Dunkel- und Kontrastmodus.
--}}
@php
    $saetze = collect($saetze)->map(fn ($satz) => trim((string) $satz))->filter()->values()->all();
    $gesamt = count($saetze);

    $wand = $bild ?: '/img/wall-of-shame/wand.webp';
    $kleinPfad = preg_replace('/\.webp$/', '-1000.webp', $wand);
    $klein = $kleinPfad !== $wand && \App\Support\Medien::vorhanden($kleinPfad) ? $kleinPfad : null;

    $listenFlaeche = $auf === 'card' ? 'bg-cream' : 'bg-card';
    $anker = 'wall-of-shame-'.substr(md5($titel ?? ''), 0, 6);
@endphp

@if ($gesamt > 0)
    <section @class(['px-4 md:px-8 py-8 lg:px-10 lg:py-12', 'bg-card border-y border-line' => $auf === 'card'])
             aria-labelledby="{{ $anker }}"
             data-wall-of-shame
             data-text-zaehler="{{ __(':nr von :gesamt') }}"
             data-text-weiter="{{ __('Nächsten Satz lesen') }}"
             data-text-schluss="{{ __('Weiter') }}"
             data-text-neu="{{ __('Noch einmal von vorn') }}"
             data-text-ende="{{ __('Das waren alle :gesamt Sätze.') }}"
             data-text-fortsetzen="{{ __('Du hast schon :nr von :gesamt Sätzen gelesen. Ein Klick auf den Papierstapel zeigt den nächsten.') }}">
        <div class="mx-auto max-w-6xl">
            <h2 id="{{ $anker }}" @class([
                'mb-6 font-display text-2xl font-medium text-green lg:text-3xl' => $titel,
                'sr-only' => ! $titel,
            ])>{{ $titel ?: __('Die Sätze an der Wand') }}</h2>

            <div data-wand hidden
                 class="relative isolate aspect-[4/5] overflow-hidden rounded-card bg-[#b9a283]
                        sm:aspect-[4/3] lg:aspect-[16/9]">
                <img src="{{ bildadresse($wand) }}"
                     @if ($klein) srcset="{{ bildadresse($klein) }} 1000w, {{ bildadresse($wand) }} 1800w"
                     sizes="(min-width: 1152px) 1072px, 100vw" @endif
                     alt="" loading="lazy" decoding="async"
                     class="absolute inset-0 -z-10 h-full w-full object-cover object-bottom">

                {{-- Wird mit jedem Satz dunkler. --}}
                <div data-wand-schatten aria-hidden="true"
                     class="pointer-events-none absolute inset-0 bg-[#140c07] opacity-0 transition-opacity duration-700"></div>

                {{-- Die Zählstriche, gezeichnet von wall-of-shame.js. --}}
                <svg data-wand-striche aria-hidden="true" focusable="false"
                     class="pointer-events-none absolute inset-0 h-full w-full"></svg>

                {{-- Der Zettel mit dem Satz. aria-live: Vorlesehilfen lesen
                     jeden neuen Satz vor, ohne dass der Fokus den Knopf verlässt. --}}
                <figure data-wand-zettel
                        class="absolute left-1/2 top-[6%] w-[min(90%,34rem)] -translate-x-1/2 rotate-[-1deg]
                               rounded-sm bg-[#f4ecdf] px-5 py-4 text-[#271e18] shadow-[0_8px_24px_rgba(0,0,0,0.35)]
                               transition-transform duration-500 sm:px-7 sm:py-5">
                    <div class="mb-1.5 flex items-baseline justify-between gap-3">
                        <p data-wand-zaehler class="text-xs font-medium uppercase tracking-[0.12em] text-[#6b5a4c]">
                            {{ __(':nr von :gesamt', ['nr' => 0, 'gesamt' => $gesamt]) }}
                        </p>
                        {{-- Setzt den gemerkten Lesestand zurück, ohne bis zum Schluss zu klicken. --}}
                        <button type="button" data-wand-von-vorn hidden
                                class="-my-2 cursor-pointer px-1 py-2 text-xs font-medium text-[#6b5a4c] underline
                                       underline-offset-2 hover:text-[#271e18] focus-visible:outline-2
                                       focus-visible:outline-offset-2 focus-visible:outline-[#271e18]">
                            {{ __('Von vorn beginnen') }}
                        </button>
                    </div>
                    <blockquote aria-live="polite">
                        <p data-wand-satz class="font-display text-lg leading-snug sm:text-xl lg:text-2xl">
                            {{ __('Ein Klick auf den Papierstapel zeigt den ersten Satz. Mit jedem Satz kommt ein Strich an die Wand.') }}
                        </p>
                    </blockquote>
                </figure>

                {{-- Der Knopf liegt auf dem Papierstapel im Bild. --}}
                <button type="button" data-wand-stapel
                        class="group absolute bottom-0 left-1/2 flex h-[24%] w-[min(94%,32rem)] -translate-x-1/2
                               cursor-pointer items-end justify-center pb-[4%] sm:w-[46%]
                               focus-visible:outline-none">
                    <span data-wand-knopf
                          class="rounded-full bg-[#1d1611]/90 px-5 py-2.5 text-sm font-medium text-white shadow-lg
                                 group-hover:bg-[#1d1611] group-focus-visible:outline-3 group-focus-visible:outline-offset-2
                                 group-focus-visible:outline-white sm:text-base">
                        {{ __('Nächsten Satz lesen') }}
                    </span>
                </button>
            </div>

            <details data-wand-liste class="mt-6 rounded-card border border-line {{ $listenFlaeche }}">
                <summary class="flex cursor-pointer items-center justify-between gap-3 px-5 py-4 font-medium text-ink
                                marker:content-none [&::-webkit-details-marker]:hidden">
                    {{ __('Alle :gesamt Sätze als Liste', ['gesamt' => $gesamt]) }}
                    <x-ui.icon name="chevron-down" :size="20" class="shrink-0 text-ink-soft" />
                </summary>

                <ol class="max-w-prose space-y-2.5 border-t border-line px-5 py-5 leading-relaxed text-ink">
                    @foreach ($saetze as $satz)
                        <li data-satz="{{ $satz }}">„{{ $satz }}“</li>
                    @endforeach
                </ol>

                @if ($schluss)
                    <p data-wand-schluss class="max-w-prose px-5 pb-5 font-semibold text-ink">{{ $schluss }}</p>
                @endif
            </details>
        </div>
    </section>
@endif
