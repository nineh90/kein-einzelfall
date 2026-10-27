@props([
    'seiten' => [],      // deutsche Slugs, in dieser Reihenfolge
    'titel' => null,
    'auf' => 'cream',    // cream | card
    // Ohne eigenes Band, wenn er neben einem Textabschnitt steht
    // (text-mit-dokumenten).
    'rahmen' => true,
    // karten | knoepfe. Knöpfe nur mit dem Namen, wie „Mehr zu …“ am
    // Seitenende (KEV-242, rechts neben dem Text auf /wissen).
    'darstellung' => 'karten',
])

@php
    $kacheln = \App\Support\Unterseiten::seiten(array_values(array_filter((array) $seiten)));

    // Kacheln stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';
@endphp

{{--
    Ausgewählte Seiten als Karten mit Titelbild (KEV-174).

    Taddis Wunsch auf der Vereinsseite: rechts neben dem Text Selbsthilfe-
    und Arbeitsgruppen, mit ihren Bildern, wenig Text und dem Link dorthin.
    Gepflegt werden nur die Seiten; Bild, Name und Unterzeile kommen von der
    jeweiligen Seite (App\Support\Unterseiten), wie bei „Unterseiten als
    Kacheln“. Neben einem Text stehen die Karten untereinander, für sich
    allein nebeneinander.
--}}
@if (count($kacheln) > 0)
    @if ($rahmen)
        <section @class([
            'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
            'bg-card border-y border-line' => $auf === 'card',
        ])>
            <div class="mx-auto max-w-6xl">
    @endif

    <div>
        @if ($titel)
            <h2 class="mb-4 font-display text-xl font-medium text-green">{{ $titel }}</h2>
        @endif

        @if ($darstellung === 'knoepfe')
            <ul @class([
                'grid grid-cols-[minmax(0,1fr)] gap-3',
                'sm:grid-cols-2 lg:grid-cols-3' => $rahmen,
            ])>
                @foreach ($kacheln as $kachel)
                    <li class="flex min-w-0">
                        <a href="{{ $kachel['url'] }}"
                           class="group flex flex-1 items-center gap-3 rounded-card border border-line px-4 py-3.5 no-underline hover:border-green {{ $innen }}">
                            <span class="flex-1 text-[0.9375rem] text-ink group-hover:underline">{{ $kachel['label'] }}</span>
                            <span class="shrink-0 text-ink-soft transition-transform group-hover:translate-x-0.5"
                                  aria-hidden="true">→</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <ul @class([
                'grid grid-cols-[minmax(0,1fr)] gap-4 sm:grid-cols-2',
                'lg:grid-cols-1' => ! $rahmen,
                'lg:grid-cols-3' => $rahmen && count($kacheln) >= 3,
            ])>
                @foreach ($kacheln as $kachel)
                    <li class="flex min-w-0">
                        <x-ui.seitenkachel :kachel="$kachel" :innen="$innen" :format="$rahmen ? 'aspect-[3/2]' : 'aspect-[2/1]'" />
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($rahmen)
            </div>
        </section>
    @endif
@endif
