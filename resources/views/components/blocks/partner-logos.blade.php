@props([
    'titel' => null,
    'einleitung' => null,
    'partner' => [],   // [['name' =>, 'rolle' =>, 'url' =>, 'logo' =>, 'logo_alt' =>], ...]
    'auf' => 'cream',  // cream | card
])

@php
    // Kästen stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // auf der Karte mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';
@endphp

@php
    /*
     * Einträge ohne Namen fliegen raus. Der Name ist das Einzige, was hier
     * wirklich gebraucht wird — ein Logo ohne Namen wäre für eine Vorlesehilfe
     * ein leerer Kasten, und ein leerer Eintrag ein Loch im Raster.
     */
    $partner = collect($partner)
        ->filter(fn ($p) => trim($p['name'] ?? '') !== '')
        ->values();
@endphp

@if ($partner->isNotEmpty())
{{--
    Kooperationen, Netzwerke, Förderer, Schirmherrschaften, Botschafter.

    Ein Baustein für vier Kategorien des Strukturpapiers (1.9, 1.10, 1.11, 7.1):
    Sie unterscheiden sich im Text darüber, nicht in der Darstellung. Vier
    beinahe gleiche Bausteine wären vier Stellen, an denen später etwas
    auseinanderläuft.

    Barrierefreiheits-Punkte:
    - Das Logo ist dekorativ (alt=""), der Name steht als Text daneben. Ein
      Logo mit alt="Aktion Mensch Logo" liest sich vorgelesen als „Aktion Mensch
      Logo Link Aktion Mensch" — die Doppelung stört genau die Menschen, für die
      der Alternativtext gedacht ist.
    - Fehlt ein Logo, trägt der Name allein. Der Verein soll Partner eintragen
      können, bevor er eine Bilddatei hat.
    - Verweise nach draußen sind als solche gekennzeichnet.
--}}
<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])
         @if ($titel) aria-labelledby="partner-{{ Str::slug($titel) }}" @else aria-label="{{ __('rahmen.partner.bereich') }}" @endif>
    <div class="mx-auto max-w-6xl">

        @if ($titel || $einleitung)
            <div class="max-w-prose">
                @if ($titel)
                    <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                    <h2 id="partner-{{ Str::slug($titel) }}"
                        class="mb-4 font-display text-2xl font-medium text-ink lg:text-3xl">
                        {{ $titel }}
                    </h2>
                @endif

                @if ($einleitung)
                    <p class="mb-8 leading-relaxed text-ink-soft">{{ $einleitung }}</p>
                @endif
            </div>
        @endif

        {{-- Als Liste ausgezeichnet: Eine Vorlesehilfe sagt damit „Liste mit
             6 Einträgen" an, statt sechs Links ohne Zusammenhang vorzulesen. --}}
        <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($partner as $p)
                @php
                    $extern = (bool) preg_match('#^https?://#i', $p['url'] ?? '');
                    $hatZiel = trim($p['url'] ?? '') !== '';
                @endphp
                <li>
                    {{-- Mit Ziel ein Link, ohne Ziel eine Karte. Ein <a> ohne
                         href ist für die Tastatur nicht erreichbar und
                         verspricht trotzdem einen Klick. --}}
                    <{{ $hatZiel ? 'a' : 'div' }}
                        @if ($hatZiel)
                            href="{{ $p['url'] }}"
                            @if ($extern) rel="noreferrer noopener" @endif
                        @endif
                        class="flex h-full flex-col items-center justify-center gap-3 rounded-card
                               border border-line {{ $innen }} px-4 py-6 text-center no-underline
                               @if ($hatZiel) hover:border-green-brand hover:bg-green-mist @endif">

                        @if (! empty($p['logo']))
                            {{-- Feste Höhe, Breite nach Bedarf: Logos kommen in
                                 allen Seitenverhältnissen, und ein Raster, in dem
                                 eines doppelt so groß wirkt wie das nächste,
                                 sieht nach Rangfolge aus, wo keine gemeint ist. --}}
                            <img src="{{ $p['logo'] }}"
                                 alt="{{ $p['logo_alt'] ?? '' }}"
                                 loading="lazy"
                                 class="h-12 w-auto max-w-full object-contain">
                        @endif

                        <span class="flex flex-col gap-0.5">
                            <span class="text-[0.9375rem] font-medium text-ink">{{ $p['name'] }}</span>

                            @if (! empty($p['rolle']))
                                <span class="text-xs text-ink-soft">{{ $p['rolle'] }}</span>
                            @endif

                            @if ($extern)
                                <span class="sr-only">{{ __('rahmen.partner.fremde_seite') }}</span>
                            @endif
                        </span>
                    </{{ $hatZiel ? 'a' : 'div' }}>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
