@props([
    'bloecke' => [],       // list<PageBlock>, alle vom Typ text, jeder kurz
    'auf' => 'cream',      // cream | card
])

{{--
    Kurze Textabschnitte auf der Startseite als gleichwertige Spalten.

    „Vereinsarbeit“ und „Mitglieder“ sind zwei Teaser mit Überschrift, zwei
    Sätzen und einem Knopf. Untereinander als eigene Bänder wirkten sie wie
    zwei leere Kästen ohne Bild (Abnahme 23.09.2026). Nebeneinander auf einer
    Fläche, getrennt durch eine feine Linie, ist es ein Paar. Die Knöpfe
    stehen unten auf einer Linie, auch wenn die Texte verschieden lang sind.

    Ab „md“ nebeneinander, bei drei Abschnitten ab „lg“ dreispaltig. Auf dem
    Handy untereinander, mit einer Linie dazwischen.
--}}
<section @class([
    'px-4 md:px-8 py-10 lg:px-10 lg:py-16',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div @class([
        'mx-auto grid max-w-6xl grid-cols-[minmax(0,1fr)] md:grid-cols-2',
        'lg:grid-cols-3' => count($bloecke) >= 3,
    ])>
        @foreach ($bloecke as $block)
            @php
                $t = $block->textAngaben();
                $spalte = \Illuminate\Support\Arr::toCssClasses([
                    'md:px-8 lg:px-12',
                    'md:pl-0 lg:pl-0' => $loop->first,
                    'mt-10 border-t border-line pt-10 md:mt-0 md:border-l md:border-t-0 md:pt-0' => ! $loop->first,
                    'md:pr-0 lg:pr-0' => $loop->last,
                ]);
            @endphp
            <x-blocks.text-inhalt :fuellen="true" :class="$spalte"
                :eyebrow="$t['eyebrow']" :titel="$t['titel']" :anker="$t['anker']"
                :absaetze="$t['absaetze']" :hand="$t['hand']" :cta="$t['cta']" />
        @endforeach
    </div>
</section>
