@props([
    'seiten' => [],      // deutsche Slugs, in dieser Reihenfolge
    'titel' => null,
    'auf' => 'cream',    // cream | card
    // Ohne eigenes Band, wenn er neben einem Textabschnitt steht
    // (text-mit-dokumenten).
    'rahmen' => true,
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
    </div>

    @if ($rahmen)
            </div>
        </section>
    @endif
@endif
