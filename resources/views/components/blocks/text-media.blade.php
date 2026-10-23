@props([
    'titel' => null,
    'absaetze' => [],
    'bild' => null,
    'bild_alt' => null,
    'bild_seite' => 'rechts',   // rechts | links
    'cta' => null,
    'anker' => null,            // Sprungziel der Überschrift
    'auf' => 'cream',
])

{{--
    Text mit Bild.

    Erwartet echtes Bildmaterial vom Verein. Bis das vorliegt, erscheint eine
    ruhige Platzhalterfläche statt eines leeren Kastens — so lässt sich die
    Seite schon zusammenstellen, ohne dass sie unfertig aussieht.

    Zum alt-Text: Trägt das Bild eine Aussage, gehört sie hier hinein. Ist es
    reine Dekoration, bleibt das Feld leer — dann überspringen Screenreader es,
    statt einen Dateinamen vorzulesen.
--}}
@php
    // Langer Text (KEV-142, Rückblick): Das Bild steht oben neben der
    // Überschrift und läuft beim Lesen mit, statt mittig neben dem Text zu
    // schweben. Kurzer Text bleibt mittig neben dem Bild. Eingeklappt wird
    // erst ab sechs Absätzen (text-inhalt), darunter bleibt alles sichtbar.
    $lang = count($absaetze) > 4;
@endphp

<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div @class(['mx-auto grid max-w-6xl gap-8 lg:grid-cols-2 lg:gap-12', 'items-center' => ! $lang, 'items-start' => $lang])>

        <div @class(['order-2', 'lg:order-1' => $bild_seite === 'rechts', 'lg:order-2' => $bild_seite === 'links'])>
            {{-- Derselbe Inhaltsteil wie im Textbaustein (KEV-141): lange Texte
                 klappen nach vier Absätzen ein, „- “ wird zur Aufzählung, die
                 Überschrift trägt ihr Sprungziel. Vorher stand hier eine
                 eigene, ärmere Fassung davon. --}}
            <x-blocks.text-inhalt :titel="$titel" :absaetze="$absaetze" :cta="$cta" :anker="$anker">
                {{ $slot }}
            </x-blocks.text-inhalt>
        </div>

        <div @class(['order-1', 'lg:order-2' => $bild_seite === 'rechts', 'lg:order-1' => $bild_seite === 'links',
                     'lg:sticky lg:top-[calc(var(--kopfhoehe,8rem)+1.5rem)]' => $lang])>
            @if ($bild)
                <img src="{{ bildadresse($bild) }}" alt="{{ $bild_alt }}" loading="lazy"
                     class="w-full rounded-card border border-line object-cover">
            @else
                {{-- Platzhalter in der Farbwelt der Seite, nicht als grauer Kasten --}}
                <div aria-hidden="true"
                     class="flex aspect-[4/3] w-full items-end justify-start rounded-card border border-line
                            bg-[linear-gradient(135deg,#EFE4CC_0%,#D9C7A2_60%,#C3AE83_100%)] p-4">
                    <span class="rounded-full bg-cream/90 px-3 py-1 text-xs text-ink-soft">
                        {{ __('Platzhalter — Foto folgt') }}
                    </span>
                </div>
            @endif
        </div>
    </div>
</section>
