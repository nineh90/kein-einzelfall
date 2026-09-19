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

    /*
     * Jede Variante traegt einen Rahmen — die gefuellten einen durchsichtigen.
     *
     * Ohne das ist ein umrandeter Knopf 2 px hoeher als ein gefuellter daneben,
     * weil der Rahmen zur Hoehe dazukommt. Bei zwei Knoepfen nebeneinander
     * sieht man das sofort, und es sieht nach Unfall aus. Betraf bisher jedes
     * Paar aus primary und ghost.
     */
    $variants = [
        'primary' => 'border border-transparent bg-green text-on-green hover:bg-green-deep',
        'ghost'   => 'border border-ink text-ink hover:bg-ink hover:text-cream',
        'light'   => 'border border-transparent bg-cream text-green-deep hover:bg-card',
        'outline' => 'border border-[#6E8A79] text-on-green hover:bg-[#2B4536]',
        /*
         * Nur fuer den Notausgang. Der Warnton ist der einzige Rotton der
         * Palette und bleibt diesem einen Zweck vorbehalten — sonst verliert er
         * genau die Bedeutung, wegen der er da ist.
         *
         * Bewusst NICHT in PageForm::KNOPF_AUSSEHEN: Im Panel soll niemand
         * versehentlich einen roten Spendenknopf bauen koennen.
         */
        'alert'   => 'border border-alert text-alert hover:bg-alert hover:text-cream',
    ];

    $classes = $base . ' ' . ($sizes[$size] ?? $sizes['base']) . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="{{ $attributes->get('type', 'button') }}" @endif
    {{ $attributes->except('type')->class($classes) }}
>
    {{ $slot }}
</{{ $tag }}>
