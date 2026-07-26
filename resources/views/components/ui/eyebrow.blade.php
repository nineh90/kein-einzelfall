@props(['auf' => 'hell'])   {{-- hell | dunkel --}}

{{-- Kleine Überzeile mit vorangestelltem Strich. Kein <h*>, weil sie die
     Überschriften-Hierarchie sonst durchbrechen würde. --}}
<p {{ $attributes->class([
    'flex items-center gap-2.5 text-xs font-medium uppercase tracking-[0.14em]',
    'before:h-px before:w-6 before:bg-current before:content-[""]',
    'text-green' => $auf === 'hell',
    'text-on-green-hand' => $auf === 'dunkel',
]) }}>
    {{ $slot }}
</p>
