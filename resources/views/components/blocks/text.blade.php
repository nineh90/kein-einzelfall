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

{{-- Basis-Textblock. Prosa mit begrenzter Zeilenlänge (max-w-prose ≈ 65 Zeichen) —
     lange Zeilen sind für Menschen mit Lese- oder Konzentrationsschwierigkeiten
     deutlich anstrengender.

     Gilt für einen einzelnen Textabschnitt. Folgen mehrere aufeinander, fasst
     die Seite sie zu x-blocks.artikel bzw. x-blocks.nebeneinander zusammen. --}}
<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div class="mx-auto max-w-6xl">
        <x-blocks.text-inhalt class="max-w-prose"
            :eyebrow="$eyebrow" :titel="$titel" :cta="$cta" :anker="$anker"
            :absaetze="$absaetze" :ab_absatz="$ab_absatz" :hand="$hand">
            {{ $slot }}
        </x-blocks.text-inhalt>
    </div>
</section>
