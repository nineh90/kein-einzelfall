@props([
    'variant' => 'primary',   // primary | ghost | light | outline
    'href' => null,
    'size' => 'base',         // base | sm
])

@php
    // Navigation gehoert in ein <a>, Aktionen in ein <button>. Das Mockup nutzt
    // ueberall <button> — das bricht Screenreader-Erwartung, Mittelklick und "in
    // neuem Tab oeffnen". Hier entscheidet die Anwesenheit von href.
    $tag = $href ? 'a' : 'button';

    $base = 'inline-flex items-center justify-center gap-2 rounded-full font-medium
             transition-colors duration-150 no-underline';

    $sizes = [
        'base' => 'px-6 py-3 text-base',
        'sm'   => 'px-4 py-2 text-sm',
    ];

    $variants = [
        'primary' => 'bg-green text-on-green hover:bg-green-deep',
        'ghost'   => 'border border-ink text-ink hover:bg-ink hover:text-cream',
        'light'   => 'bg-cream text-green-deep hover:bg-card',
        'outline' => 'border border-[#6E8A79] text-on-green hover:bg-[#2B4536]',
    ];

    $classes = $base . ' ' . ($sizes[$size] ?? $sizes['base']) . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="{{ $attributes->get('type', 'button') }}" @endif
    {{ $attributes->except('type')->class($classes) }}
>
    {{ $slot }}
</{{ $tag }}>
