@props([
    'titel' => null,
    'text' => null,
    'art' => 'hinweis',   // hinweis | frist | wichtig
    'auf' => 'cream',     // cream | card — die Fläche des Abschnitts davor
    'link' => null,       // ['label'=>, 'url'=>] — weiterführender Verweis
])

@php
    // Bewusst zurückhaltend eingefärbt. Grelle Warnfarben erzeugen bei Menschen
    // in belastenden Situationen Druck — die Botschaft soll auffallen, nicht
    // alarmieren. Deshalb trägt die Aussage ein Symbol und ein Rand, nicht
    // eine grosse rote Fläche.
    // Der Kasten steht auf der jeweils anderen Fläche als der Abschnitt.
    $kasten = $auf === 'card' ? 'bg-cream' : 'bg-card';

    // Verweis ohne Beschriftung oder Ziel aussortieren, siehe helpers.php.
    $verweis = knoepfe([$link])[0] ?? null;

    // Ein Ziel ausserhalb der Seite kündigen wir an: Ein Sprung in einen neuen
    // Tab ohne Vorwarnung kostet mit Screenreader oder Tastatur spürbar
    // Orientierung. `str_starts_with` statt einer Prüfung auf den eigenen
    // Hostnamen — im Panel steht entweder ein Pfad oder eine volle Adresse.
    $nachDraussen = $verweis && str_starts_with($verweis['url'], 'http');

    $stil = match ($art) {
        'frist' => ['icon' => 'lock', 'rahmen' => 'border-alert', 'flaeche' => $kasten,
                    'akzent' => 'text-alert', 'standardtitel' => 'Auf die Frist achten'],
        'wichtig' => ['icon' => 'shield', 'rahmen' => 'border-green', 'flaeche' => 'bg-green-mist',
                      'akzent' => 'text-green-deep', 'standardtitel' => 'Wichtig'],
        default => ['icon' => 'message', 'rahmen' => 'border-line', 'flaeche' => $kasten,
                    'akzent' => 'text-green-deep', 'standardtitel' => 'Gut zu wissen'],
    };
@endphp

{{--
    Hervorgehobener Hinweis.

    Gedacht für das, was im Fliesstext untergeht, aber teuer werden kann:
    Widerspruchsfristen, notwendige Nachweise, häufige Missverständnisse.
--}}
{{-- Der Hinweis gehört zum Abschnitt davor und teilt dessen Fläche, statt
     einen neuen Abschnitt aufzumachen. Auf der Karte rückt er deshalb um
     einen Pixel nach oben und deckt die untere Linie des Abschnitts ab —
     die Karte läuft ohne Naht weiter und schließt erst unter dem Hinweis. --}}
{{-- data-anschliessend: Dieser Abschnitt ist keiner. Er gehört zum Text
     darüber und teilt bewusst dessen Fläche — der Flächenwechsel überspringt
     ihn deshalb, und der Test, der ihn bewacht, ebenso. --}}
<section data-anschliessend
         @class(['px-4 py-4 lg:px-10', 'relative -mt-px border-b border-line bg-card' => $auf === 'card'])>
    <div class="mx-auto max-w-6xl">
        <aside class="max-w-prose rounded-card border-2 {{ $stil['rahmen'] }} {{ $stil['flaeche'] }} px-5 py-4">
            <div class="flex gap-3">
                <span class="mt-0.5 shrink-0 {{ $stil['akzent'] }}">
                    <x-ui.icon :name="$stil['icon']" :size="20" />
                </span>

                <div class="flex-1">
                    <p class="font-display text-base font-medium {{ $stil['akzent'] }}">
                        {{ $titel ?: $stil['standardtitel'] }}
                    </p>

                    @if ($text)
                        <p class="mt-1.5 leading-relaxed text-ink">{{ $text }}</p>
                    @endif

                    @if ($verweis)
                        {{-- Link und kein Knopf: Der Kasten ist ein Hinweis am
                             Rand, nicht die Handlung, um die es auf der Seite
                             geht. Ein Knopf zöge mehr Aufmerksamkeit auf sich
                             als der Text, zu dem er gehört. --}}
                        <p class="mt-3">
                            <a href="{{ $verweis['url'] }}"
                               class="text-green-deep underline"
                               @if ($nachDraussen) target="_blank" rel="noopener noreferrer" @endif>
                                {{ $verweis['label'] }}
                                @if ($nachDraussen)
                                    <span class="sr-only">{{ __('rahmen.neuer_tab') }}</span>
                                @endif
                            </a>
                        </p>
                    @endif

                    {{ $slot }}
                </div>
            </div>
        </aside>
    </div>
</section>
