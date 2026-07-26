@props(['name', 'size' => 20])

@php
    // Pfade im Lucide-/Feather-Stil, stroke-width 1.8 — konsistent zum Mockup.
    $paths = [
        'accessibility' => '<circle cx="12" cy="4" r="1.6" fill="currentColor" stroke="none"/><path d="M5 8h14M12 8v5M8 21l3-8M16 21l-3-8M7 12l5 1 5-1"/>',
        'exit'          => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'home'          => '<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 0 0 1 1h3v-6h6v6h3a1 1 0 0 0 1-1V10"/>',
        'users'         => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/>',
        'message'       => '<path d="M4 4h16v12H7l-3 3V4z"/>',
        'heart'         => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1L12 21l7.7-7.6 1.1-1a5.5 5.5 0 0 0 0-7.8z"/>',
        'chevron-down'  => '<path d="m6 9 6 6 6-6"/>',
        'arrow-right'   => '<path d="M5 12h14M12 5l7 7-7 7"/>',
        'menu'          => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close'         => '<path d="M18 6 6 18M6 6l12 12"/>',
        'lock'          => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'shield'        => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    ];
@endphp

<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
     {{-- Icons sind hier durchgehend dekorativ: der Accessible Name kommt immer aus
          dem Textlabel oder einem aria-label am Elternelement. --}}
     aria-hidden="true" focusable="false"
     {{ $attributes }}>
    {!! $paths[$name] ?? '' !!}
</svg>
