@props(['stats' => []])   {{-- [['wert'=>'1.000+', 'label'=>'erreichte Menschen'], ...] --}}

{{-- Kennzahlen-Leiste. Mobil horizontal scrollbar; die Liste bekommt deshalb
     tabindex="0", damit sie auch per Tastatur gescrollt werden kann (WCAG 2.1.1). --}}
<section class="border-y border-line px-4 py-5 lg:px-10" aria-label="Der Verein in Zahlen">
    <ul tabindex="0"
        class="mx-auto flex max-w-6xl gap-8 overflow-x-auto lg:justify-start lg:gap-12">
        @foreach ($stats as $stat)
            <li class="shrink-0">
                <p class="font-display text-2xl font-medium text-green-deep">{{ $stat['wert'] }}</p>
                <p class="text-xs text-ink-soft">{{ $stat['label'] }}</p>
            </li>
        @endforeach
    </ul>
</section>
