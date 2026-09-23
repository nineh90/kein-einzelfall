@props([
    'eyebrow' => null,
    'zitat',
    'notiz' => null,
    'ctas' => [],
    'wasserzeichen' => 'KE!N EINZELFALL e.V.',
    'auf' => 'cream',    // cream | card
])

{{-- Leere Knöpfe aus dem Panel aussortieren, siehe helpers.php --}}
@php $knoepfe = knoepfe($ctas); @endphp

<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    {{-- Unten mehr Luft als oben: Dort liegt der Schriftzug, und er soll eine
         eigene Zone haben statt hinter Text und Knöpfen zu verschwinden. --}}
    <div class="relative mx-auto max-w-6xl overflow-hidden rounded-band bg-green-deep
                px-6 pb-24 pt-8 lg:px-10 lg:pb-28 lg:pt-11">

        {{-- Der Vereinsname als handschriftlicher Hintergrund (KEV-15).

             Vorher stand hier nur „KE!N", rechts aus dem Kasten herauslaufend
             und zur Hälfte hinter den Knöpfen — angedeutet, wie der Verein es
             beschrieb, aber es sah eher nach einem Fehler aus als nach
             Absicht. Jetzt steht der Name vollständig da.

             Unten links und nicht quer hinter allem: Quer über die Mitte
             kreuzte er Zitat und Knöpfe, und beides verlor. Unten hat er eine
             eigene Zone, überlagert nichts und bleibt trotzdem Hintergrund.

             Hell statt dunkel (--color-on-green-hand, dieselbe Farbe wie die
             Übertitel auf Grün): Ein dunkler Schriftzug auf dunklem Grün wirkt
             wie ein Schatten, ein heller wie mit Kreide geschrieben. Die
             niedrige Deckkraft hält ihn hinter dem Zitat zurück.

             Auch auf dem Handy sichtbar, nur kleiner — „komplett lesbar" gilt
             dort genauso. Die Schriftgrösse skaliert mit der Breite, damit der
             Name nie umbricht und nie über den Rand läuft: Abgeschnitten wäre
             er wieder nur angedeutet, und genau das war der Anlass für KEV-15.
             Die 9,5vw sind an der schmalsten Breite gemessen, die wir stützen
             (320px, die Vorgabe aus WCAG 1.4.10) — dort bleibt neben dem
             längsten Namen, den das Panel vorgibt, noch etwas Luft.

             Als CSS-Dekoration und nicht als Textknoten: Als <span> mit Inhalt
             war es echter Text mit zu geringem Kontrast und damit ein
             gemeldeter WCAG-Verstoss. Reine Dekoration ist von 1.4.3 zwar
             ausgenommen, aber das kann eine Maschine nicht wissen — und ein
             Prüfbericht mit einem erklärungsbedürftigen roten Punkt ist bei
             diesem Auftrag das schlechtere Ergebnis. Der Vereinsname steht
             ohnehin im Kopf und im Fuss jeder Seite; als Hintergrund trägt er
             keine Information, die sonst verloren ginge. --}}
        <span aria-hidden="true" data-wasserzeichen
              style="--wasserzeichen: '{{ $wasserzeichen }}'"
              class="pointer-events-none absolute bottom-3 left-6 select-none whitespace-nowrap
                     font-hand text-[clamp(1.75rem,9.5vw,5.25rem)] leading-none
                     text-on-green-hand opacity-25 lg:left-10 lg:bottom-4"></span>

        <div class="relative grid gap-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
            <div>
                @if ($eyebrow)
                    <x-ui.eyebrow auf="dunkel" class="mb-3">{{ $eyebrow }}</x-ui.eyebrow>
                @endif

                <blockquote class="font-display text-xl italic leading-snug text-on-green lg:text-2xl">
                    {{ $zitat }}
                </blockquote>

                @if ($notiz)
                    <p class="mt-3 text-sm text-[#9FB6A6]">{{ $notiz }}</p>
                @endif
            </div>

            @if ($knoepfe)
                {{-- Nebeneinander ab „sm“, untereinander erst wieder in der
                     schmalen rechten Spalte ab „lg“. Dazwischen liefen die
                     Knöpfe über die ganze Bandbreite — 700 px für ein Wort. --}}
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap lg:flex-col">
                    @foreach ($knoepfe as $cta)
                        <x-ui.button :href="$cta['url']" :variant="$cta['variant'] ?? 'light'">
                            {{ $cta['label'] }}
                        </x-ui.button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
