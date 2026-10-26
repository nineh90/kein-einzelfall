@props([
    'seite' => null,     // die Seite, auf der der Baustein steht
    'titel' => null,
    'aufrufe' => false,  // Aufrufe je Kachel zeigen (KEV-156, Taddis Frage nach einem Zähler)
    'auf' => 'cream',    // cream | card
])

@php
    use App\Support\Unterseiten;

    $kacheln = $seite ? Unterseiten::fuer($seite) : [];

    // Kacheln stehen auf der jeweils anderen Fläche, sonst verschwämmen sie
    // mit dem Hintergrund.
    $innen = $auf === 'card' ? 'bg-cream' : 'bg-card';

    $ueberschrift = 'unterseiten-'.($seite?->getKey() ?? 'x');
@endphp

{{--
    Unterseiten als Kacheln (KEV-156).

    Für die Übersicht „Raum für Ungesagtes“: Taddis vier Kategorien, jede mit
    ihrem Titelbild. Was auf den Kacheln steht, kommt aus Menü und Seiten
    (App\Support\Unterseiten), hier wird nichts gepflegt außer der Überschrift
    und dem Schalter für die Aufrufe.

    Die Aufrufe zählt der Server ohne jede Kennung (App\Support\Aufrufe). Steht
    eine Seite noch bei null, fehlt die Zeile, statt „0 Aufrufe“ zu zeigen: Das
    läse sich wie „hier schaut niemand hin“, und genau das Gefühl soll dieser
    Bereich nicht geben.
--}}
@if (count($kacheln) > 0)
<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
]) @if ($titel) aria-labelledby="{{ $ueberschrift }}" @else aria-label="{{ $seite->titel }}" @endif>
    <div class="mx-auto max-w-6xl">
        @if ($titel)
            <h2 id="{{ $ueberschrift }}" class="mb-6 font-display text-2xl font-semibold text-green">{{ $titel }}</h2>
        @endif

        {{-- Eine Spalte auf dem Handy, zwei ab „sm“, ab „lg“ alle vier in
             einer Reihe. minmax(0,1fr) und min-w-0 wie bei den
             Einstiegskarten: Kein langes Wort drückt eine Kachel breiter. --}}
        {{-- Sechs Kacheln (KEV-254, Klartext statt Fachsprache) in zwei
             Dreierreihen statt vier und zwei. --}}
        <ul @class([
            'grid grid-cols-[minmax(0,1fr)] gap-4 sm:grid-cols-2',
            'lg:grid-cols-3' => count($kacheln) > 4 && count($kacheln) % 3 === 0,
            'lg:grid-cols-4' => ! (count($kacheln) > 4 && count($kacheln) % 3 === 0),
        ])>
            @foreach ($kacheln as $kachel)
                <li class="flex min-w-0">
                    <x-ui.seitenkachel :kachel="$kachel" :innen="$innen" :aufrufe="$aufrufe" />
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
