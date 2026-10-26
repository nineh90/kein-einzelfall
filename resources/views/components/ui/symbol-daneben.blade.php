@props(['name'])

{{-- Großes Symbol auf der freien Fläche rechts neben einem Abschnitt
     (KEV-184, KEV-185, KEV-203), siehe PageBlock::SYMBOLE. Schmuck, auf
     dem Handy weg: Dort stünde es nur zwischen den Abschnitten. --}}
<div class="hidden items-center justify-center gap-4 text-green-brand md:flex lg:gap-6">
    @if ($name === 'red-flag-und-haken')
        {{-- KEV-208: „Red Flags & Green Lights“ als Bild. Kleiner als ein
             einzelnes Symbol, damit beide nebeneinander passen. --}}
        <x-ui.icon name="circle-red-flag" :size="128" class="h-24 w-24 [stroke-width:0.8] lg:h-32 lg:w-32" />
        <span class="font-display text-4xl text-green lg:text-5xl">&amp;</span>
        <x-ui.icon name="circle-check-filled" :size="128" class="h-24 w-24 [stroke-width:0.8] lg:h-32 lg:w-32" />
    @else
        <x-ui.icon :name="$name" :size="160" class="h-32 w-32 [stroke-width:0.8] lg:h-40 lg:w-40" />
    @endif
</div>
