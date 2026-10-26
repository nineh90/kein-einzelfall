@props([
    'eyebrow' => null,
    'titel' => null,
    'auf' => 'cream',      // cream | card
    'cta' => null,         // ['label' =>, 'url' =>, 'variant' =>]
    'anker' => null,       // Sprungziel für das Inhaltsverzeichnis
    'absaetze' => [],      // wenn gesetzt, wird ab einer Länge eingeklappt
    'ab_absatz' => 4,      // ab dem wievielten Absatz eingeklappt wird
    'hand' => null,        // handschriftlicher Nachsatz, z. B. ein Leitsatz
    'mittig' => false,     // zentriert, siehe text-inhalt
    // Großes Symbol rechts neben dem Text, siehe PageBlock::SYMBOLE (KEV-184).
    'symbol' => null,
])

@php
    $symbol = array_key_exists((string) $symbol, \App\Models\PageBlock::SYMBOLE) ? $symbol : null;
@endphp

{{-- Basis-Textblock. Prosa mit begrenzter Zeilenlänge (max-w-prose ≈ 65 Zeichen) —
     lange Zeilen sind für Menschen mit Lese- oder Konzentrationsschwierigkeiten
     deutlich anstrengender.

     Gilt für einen einzelnen Textabschnitt. Folgen mehrere aufeinander, fasst
     die Seite sie zu x-blocks.artikel bzw. x-blocks.nebeneinander zusammen. --}}
<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div @class([
        'mx-auto max-w-6xl',
        'md:grid md:grid-cols-[minmax(0,65ch)_minmax(0,1fr)] md:items-center md:gap-10' => $symbol,
    ])>
        <x-blocks.text-inhalt class="max-w-prose"
            :eyebrow="$eyebrow" :titel="$titel" :cta="$cta" :anker="$anker"
            :absaetze="$absaetze" :ab_absatz="$ab_absatz" :hand="$hand" :mittig="$mittig">
            {{ $slot }}
        </x-blocks.text-inhalt>

        {{-- Schmuck, auf der freien Fläche rechts (KEV-184, KEV-185: Taddi
             wollte neben „Team“ und „Herr und Frau Unbekannt“ je ein
             Symbol). Auf dem Handy weg: Dort stünde es nur zwischen Text
             und Karten. --}}
        @if ($symbol)
            <div class="hidden justify-center text-green-brand md:flex">
                <x-ui.icon :name="$symbol" :size="160" class="h-32 w-32 [stroke-width:0.8] lg:h-40 lg:w-40" />
            </div>
        @endif
    </div>
</section>
