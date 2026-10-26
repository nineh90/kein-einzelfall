@props(['name'])

{{-- Großes Symbol auf der freien Fläche rechts neben einem Abschnitt
     (KEV-184, KEV-185, KEV-203), siehe PageBlock::SYMBOLE. Schmuck, auf
     dem Handy weg: Dort stünde es nur zwischen den Abschnitten. --}}
<div class="hidden justify-center text-green-brand md:flex">
    <x-ui.icon :name="$name" :size="160" class="h-32 w-32 [stroke-width:0.8] lg:h-40 lg:w-40" />
</div>
