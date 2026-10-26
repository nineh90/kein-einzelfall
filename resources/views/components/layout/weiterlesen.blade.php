@props([
    'seiten' => [],
    'bereich' => null,
    'titel' => null,   // schlägt „Mehr zu „…““, wo ein Bereich anders heissen soll
    'auf' => 'card',   // cream | card — die Gegenfläche des letzten Bausteins
    // Spalten ab lg. 2 auf der Übersicht „Gruppen & Veranstaltungen“ (KEV-225,
    // `spalten` am Bereich in config/navigation.php).
    'spalten' => 3,
    // Ab lg spaltenweise lesen: erst die linke Spalte von oben nach unten,
    // dann die mittlere (KEV-176, `kacheln` am Bereich). In dieser Reihenfolge
    // stehen die Kacheln auch im Quelltext und auf dem Handy.
    'spaltenweise' => false,
])

@php
    // Zeilen für das spaltenweise Raster. Wörtlich, Tailwind kennt nur
    // Klassen, die in den Vorlagen stehen.
    $zeilen = (int) ceil(count($seiten) / 3);
    $zeilenKlasse = [1 => 'lg:grid-rows-1', 2 => 'lg:grid-rows-2', 3 => 'lg:grid-rows-3', 4 => 'lg:grid-rows-4'][$zeilen] ?? null;
@endphp

@if (count($seiten) > 0)
    {{--
        Weiterführung am Seitenende.

        Ohne sie endet jede Unterseite in einer Sackgasse: Wer bei „Satzung"
        gelandet ist, müsste zurück ins Menü, um „Mitgliedschaft" zu finden.
        Die Einträge stammen aus der Navigation — nichts davon ist zusätzlich
        gepflegter Inhalt.
    --}}
    <aside @class([
               'border-t border-line px-4 md:px-8 py-8 lg:px-10 lg:py-10',
               'bg-card' => $auf === 'card',
           ])
           aria-labelledby="weiterlesen-titel">
        <div class="mx-auto max-w-6xl">
            <h2 id="weiterlesen-titel" class="mb-5 font-display text-xl font-medium text-green">
                {{ $titel ?? ($bereich
                    ? __('rahmen.weiterlesen.mehr_zu', ['bereich' => $bereich])
                    : __('rahmen.weiterlesen.auch_interessant')) }}
            </h2>

            <ul @class([
                'grid gap-3 sm:grid-cols-2',
                'lg:grid-cols-3' => $spalten !== 2,
                'lg:grid-flow-col '.$zeilenKlasse => $spaltenweise && $spalten !== 2 && $zeilenKlasse,
            ])>
                @foreach ($seiten as $seite)
                    <li class="flex">
                        @if (! empty($seite['aktuell']))
                            {{-- Die Seite, auf der man ist (KEV-190): an ihrem
                                 Platz im Raster, aber kein Link auf sich selbst. --}}
                            <span aria-current="page"
                                  class="flex flex-1 items-center gap-3 rounded-card border border-green bg-green-mist px-4 py-3.5">
                                <span class="flex-1 text-[0.9375rem] font-medium text-green-deep">{{ $seite['label'] }}</span>
                                <span class="shrink-0 text-xs text-green-deep">{{ __('Du bist hier') }}</span>
                            </span>
                        @else
                        {{-- Die Karten stehen auf der jeweils anderen Fläche,
                             sonst verschwämmen sie mit dem Hintergrund. --}}
                        <a href="{{ $seite['url'] }}"
                           @class(['group flex flex-1 items-center gap-3 rounded-card border border-line',
                                   'px-4 py-3.5 no-underline hover:border-green',
                                   'bg-cream' => $auf === 'card',
                                   'bg-card' => $auf !== 'card'])>
                            <span class="flex-1 text-[0.9375rem] text-ink group-hover:underline">
                                {{ $seite['label'] }}
                            </span>
                            <span class="shrink-0 text-ink-soft transition-transform group-hover:translate-x-0.5"
                                  aria-hidden="true">→</span>
                        </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>
@endif
