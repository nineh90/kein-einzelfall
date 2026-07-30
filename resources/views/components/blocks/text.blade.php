@props([
    'eyebrow' => null,
    'titel' => null,
    'auf' => 'cream',      // cream | card
    'cta' => null,         // ['label' =>, 'url' =>, 'variant' =>]
    'anker' => null,       // Sprungziel für das Inhaltsverzeichnis
    'absaetze' => [],      // wenn gesetzt, wird ab einer Länge eingeklappt
    'ab_absatz' => 4,      // ab dem wievielten Absatz eingeklappt wird
    'hand' => null,        // handschriftlicher Nachsatz, z. B. ein Leitsatz
])

@php
    // Lange Abschnitte werden nach den ersten Absätzen eingeklappt. Wer alles
    // lesen will, klappt auf; wer sucht, überfliegt die Seite, ohne durch
    // fünfzehn Absätze scrollen zu müssen.
    //
    // Nur wenn die Absätze als Daten übergeben wurden — bei freiem Slot-Inhalt
    // wüssten wir nicht, wo sich sinnvoll trennen lässt.
    $sichtbar = $absaetze;
    $eingeklappt = [];

    if (count($absaetze) > $ab_absatz + 1) {
        $sichtbar = array_slice($absaetze, 0, $ab_absatz);
        $eingeklappt = array_slice($absaetze, $ab_absatz);
    }
@endphp

{{-- Basis-Textblock. Prosa mit begrenzter Zeilenlänge (max-w-prose ≈ 65 Zeichen) —
     lange Zeilen sind für Menschen mit Lese- oder Konzentrationsschwierigkeiten
     deutlich anstrengender. --}}
<section @class([
    'px-4 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div class="mx-auto max-w-6xl">
        <div class="max-w-prose">
            @if ($eyebrow)
                <x-ui.eyebrow class="mb-3">{{ $eyebrow }}</x-ui.eyebrow>
            @endif

            @if ($titel)
                {{-- Kurzer Strich über der Überschrift: markiert den
                     Abschnittsanfang, ohne zusätzliche Linien quer über die Seite.
                     scroll-mt hält die Überschrift beim Anspringen unter dem
                     klebenden Kopfbereich sichtbar. --}}
                <div @if ($anker) id="{{ $anker }}" @endif class="scroll-mt-24">
                    <span aria-hidden="true" class="mb-4 block h-0.5 w-10 rounded-full bg-green-brand"></span>
                    <h2 class="mb-4 font-display text-2xl font-medium text-ink lg:text-3xl">
                        {{ $titel }}
                    </h2>
                </div>
            @endif

            <div class="flex flex-col gap-4 leading-relaxed text-ink-soft
                        [&_a]:text-green-deep [&_a]:underline
                        [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-5
                        [&>p:first-child]:text-[1.0625rem] [&>p:first-child]:text-ink">
                @forelse ($sichtbar as $absatz)
                    <p>{{ $absatz }}</p>
                @empty
                    {{ $slot }}
                @endforelse
            </div>

            @if ($eingeklappt)
                {{-- Natives <details>: ohne JavaScript bedienbar, und der Text
                     bleibt im Dokument — Suchmaschinen und die Seitensuche des
                     Browsers finden ihn trotzdem. --}}
                <details class="group mt-4">
                    <summary class="inline-flex cursor-pointer items-center gap-2 rounded-full border
                                    border-line px-4 py-2 text-sm text-green-deep marker:content-none
                                    hover:bg-card [&::-webkit-details-marker]:hidden">
                        <span class="group-open:hidden">
                            Weiterlesen ({{ count($eingeklappt) }} weitere Absätze)
                        </span>
                        <span class="hidden group-open:inline">Weniger anzeigen</span>
                        <span class="transition-transform group-open:rotate-180">
                            <x-ui.icon name="chevron-down" :size="16" />
                        </span>
                    </summary>

                    <div class="mt-4 flex flex-col gap-4 leading-relaxed text-ink-soft
                                [&_a]:text-green-deep [&_a]:underline">
                        @foreach ($eingeklappt as $absatz)
                            <p>{{ $absatz }}</p>
                        @endforeach
                    </div>
                </details>
            @endif

            {{-- Der handschriftliche Nachsatz ist Teil der Bildsprache des
                 Mockups: ein Leitsatz, der wie mit der Hand danebengeschrieben
                 wirkt. Bewusst als normaler Absatz und nicht als Bild — er
                 gehört zum Text und muss vorlesbar und übersetzbar bleiben. --}}
            @if ($hand)
                <p class="mt-4 font-hand text-2xl text-green">{{ $hand }}</p>
            @endif

            @if ($cta)
                <div class="mt-6">
                    <x-ui.button :href="$cta['url']" :variant="$cta['variant'] ?? 'primary'">
                        {{ $cta['label'] }}
                    </x-ui.button>
                </div>
            @endif
        </div>
    </div>
</section>
