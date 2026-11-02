@props([
    'titel' => null,
    'absaetze' => [],
    'hand' => null,
    'reihe' => null,        // Schlüssel aus Event::REIHEN
    'bild' => null,
    'bild_alt' => null,
    // Was schon feststeht, aber noch ohne Datum: [['titel', 'zeitraum', 'text', 'bild'], …]
    'geplant' => [],
    'anker' => null,
    'auf' => 'cream',
])

{{--
    Eine Veranstaltungsreihe auf /veranstaltungen (KEV-317, Taddis Doc
    „3. Veranstaltungen“): links Text, rechts das Bild der Reihe, darunter
    ihre kommenden Termine aus „Veranstaltungen“ (Feld „Reihe“) und was
    schon geplant ist, aber noch kein Datum hat. Die Termine pflegt der
    Verein selbst, hier steht nur, welche Reihe gemeint ist.
--}}
@php
    use App\Models\Event;
    use App\Support\Titelbilder;

    $termine = $reihe
        ? Event::veroeffentlicht()->kommend()->where('reihe', $reihe)->oldest('beginnt_am')->get()
        : collect();

    $karten = $termine->map(fn (Event $e) => [
        'titel' => $e->titel,
        'url' => sprachlink('events.show', $e->slug),
        'wann' => $e->zeitraum(),
        'datetime' => $e->zeitMaschinenlesbar(),
        'ort' => $e->online ? __('Online') : $e->ort,
        'text' => $e->teaser,
        'bild' => $e->titelbild ?: $bild,
    ])->concat(collect((array) $geplant)->filter(fn ($g) => filled($g['titel'] ?? null))->map(fn ($g) => [
        'titel' => $g['titel'],
        'url' => null,
        'wann' => $g['zeitraum'] ?? null,
        'datetime' => null,
        'ort' => null,
        'text' => $g['text'] ?? null,
        'bild' => ($g['bild'] ?? null) ?: $bild,
        'geplant' => true,
    ]));

    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';
    $klein = fn (?string $b) => $b && \App\Support\Medien::vorhanden(preg_replace('/\.webp$/', '-1000.webp', $b))
        ? preg_replace('/\.webp$/', '-1000.webp', $b) : $b;
    // Nach dem Schlüssel der Reihe (#reihe-dialog-wissen): Aus dem Titel
    // würde „ken-einzelfall-…“, das Ausrufezeichen fällt weg.
    $kennung = $anker ?: 'reihe-'.($reihe ?: \Illuminate\Support\Str::slug($titel ?: 'veranstaltungen'));
@endphp

<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
]) aria-labelledby="{{ $kennung }}">
    <div class="mx-auto max-w-6xl">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,65ch)_minmax(0,1fr)] lg:gap-14">
            <div class="max-w-prose">
                <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                @if ($titel)
                    <h2 id="{{ $kennung }}" class="mb-4 font-display text-2xl font-medium text-green lg:text-3xl">
                        {{ ueberschrift($titel) }}
                    </h2>
                @endif
                <div class="flex flex-col gap-4 leading-relaxed text-ink-soft [&_a]:text-green-deep [&_a]:underline
                            [&>p:first-child]:text-[1.0625rem] [&>p:first-child]:text-ink">
                    @foreach (array_filter((array) $absaetze) as $absatz)
                        {{ absatz($absatz) }}
                    @endforeach
                </div>
                @if ($hand)
                    <p class="mt-4 whitespace-pre-line font-hand text-2xl text-green">{{ $hand }}</p>
                @endif
            </div>

            @if ($bild)
                {{-- Schmuck: Name und Text der Reihe stehen daneben. --}}
                <div class="lg:self-start" data-mitlaufen>
                    <img src="{{ bildadresse($klein($bild)) }}" alt="{{ $bild_alt ?? '' }}" loading="lazy" decoding="async"
                         width="1000" height="563"
                         class="aspect-[16/9] w-full rounded-card border border-line object-cover"
                         style="object-position: {{ Titelbilder::fokus($bild) }}">
                </div>
            @endif
        </div>

        @if ($karten->isNotEmpty())
            <h3 class="mb-4 mt-10 font-display text-lg font-medium text-ink">{{ __('Termine dieser Reihe') }}</h3>
            <ul class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($karten as $karte)
                    <li class="row-span-3 grid grid-rows-subgrid gap-y-0">
                        <article class="row-span-3 grid grid-rows-subgrid gap-y-0 overflow-hidden rounded-card border border-line {{ $innen }}">
                            @if ($karte['bild'])
                                <span class="block aspect-[2/1] overflow-hidden border-b border-line">
                                    <img src="{{ bildadresse($klein($karte['bild'])) }}" alt="" loading="lazy" decoding="async"
                                         width="1000" height="563" class="h-full w-full object-cover"
                                         style="object-position: {{ Titelbilder::fokus($karte['bild']) }}">
                                </span>
                            @else
                                <span aria-hidden="true"></span>
                            @endif

                            <div class="px-5 pt-5">
                                <p class="text-sm text-ink-soft">
                                    @if ($karte['datetime'])
                                        <time datetime="{{ $karte['datetime'] }}">{{ $karte['wann'] }}</time>
                                    @elseif ($karte['wann'])
                                        {{ __('Geplant') }}: {{ $karte['wann'] }}
                                    @endif
                                    @if ($karte['ort'])
                                        · {{ $karte['ort'] }}
                                    @endif
                                </p>
                                <h4 class="mt-1 font-display text-lg font-semibold leading-snug text-ink">
                                    @if ($karte['url'])
                                        <a href="{{ $karte['url'] }}" class="text-ink no-underline hover:underline">{{ $karte['titel'] }}</a>
                                    @else
                                        {{ $karte['titel'] }}
                                    @endif
                                </h4>
                                @if ($karte['text'])
                                    <p class="mt-1.5 text-sm leading-relaxed text-ink-soft">{{ $karte['text'] }}</p>
                                @endif
                            </div>

                            <div class="self-end px-5 pb-5 pt-4">
                                @if ($karte['url'])
                                    <x-ui.button :href="$karte['url']" variant="ghost" size="sm">
                                        {{ __('Mehr zur Veranstaltung') }}
                                        <span class="sr-only">– {{ $karte['titel'] }}</span>
                                    </x-ui.button>
                                @else
                                    <p class="border-t border-line pt-3 text-sm text-ink-soft">{{ __('Termin und Anmeldung folgen') }}</p>
                                @endif
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
