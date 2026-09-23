@props([
    'bloecke' => [],       // list<PageBlock>, alle vom Typ text
    'auf' => 'cream',      // cream | card
    // Punkte für „Auf dieser Seite“ (anker, titel). Leer = keine Seitenleiste.
    'verzeichnis' => [],
])

{{--
    Mehrere Textabschnitte hintereinander als ein durchgehender Artikel.

    Eine Fläche, keine Linien dazwischen, die Überschriften im Textfluss mit
    viel Luft davor. Vorher war jeder Abschnitt ein eigenes Band, und zwei
    davon untereinander wirkten wie leere Kästen (Abnahme 23.09.2026).

    Ab „lg“ steht links das Verzeichnis „Auf dieser Seite“. Es klebt beim
    Scrollen und markiert den Abschnitt, in dem man gerade ist
    (inhaltsverzeichnis.js). Damit hat die Fläche neben dem Text eine Aufgabe,
    statt leer zu bleiben. Die Spalten teilen sich wie beim Spendenblock (4:8,
    ab „xl“ 5:7), damit die Textkante seitenweit fluchtet.

    Unterhalb von „lg“ gibt es die Seitenleiste nicht; dort steht das
    Verzeichnis wie bisher als Kasten über dem Inhalt (page.blade.php).
--}}
<section @class([
    'px-4 md:px-8 py-10 lg:px-10 lg:py-16',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div @class([
        'mx-auto max-w-6xl',
        'lg:grid lg:grid-cols-[minmax(0,4fr)_minmax(0,8fr)] lg:gap-x-10 xl:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] xl:gap-x-16' => $verzeichnis,
    ])>

        @if ($verzeichnis)
            <nav aria-label="{{ __('rahmen.auf_dieser_seite') }}" class="hidden lg:block" data-verzeichnis>
                <div class="sticky top-28">
                    <p class="mb-4 text-xs font-medium uppercase tracking-[0.14em] text-green">
                        {{ __('rahmen.auf_dieser_seite') }}
                    </p>
                    {{-- Die Linie links ist die Leiste, auf der die Markierung des
                         aktuellen Abschnitts läuft (-ml-px legt sie darüber). --}}
                    <ol class="border-l border-line">
                        @foreach ($verzeichnis as $punkt)
                            <li>
                                <a href="#{{ $punkt['anker'] }}"
                                   class="-ml-px block border-l-2 border-transparent py-1.5 pl-4 text-sm leading-snug
                                          text-ink-soft no-underline transition-colors hover:text-ink
                                          aria-[current=true]:border-green-brand aria-[current=true]:font-medium
                                          aria-[current=true]:text-ink">
                                    {{ $punkt['titel'] }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </nav>
        @endif

        <div class="flex max-w-prose flex-col gap-12 lg:gap-16">
            @foreach ($bloecke as $block)
                @php $t = $block->textAngaben(); @endphp
                <x-blocks.text-inhalt
                    :eyebrow="$t['eyebrow']" :titel="$t['titel']" :anker="$t['anker']"
                    :absaetze="$t['absaetze']" :hand="$t['hand']" :cta="$t['cta']" />
            @endforeach
        </div>
    </div>
</section>
