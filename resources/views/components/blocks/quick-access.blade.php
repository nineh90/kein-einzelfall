@props([
    'titel' => 'Wie wir helfen',
    'sub' => null,
    'karten' => [],   // [['icon'=>, 'titel'=>, 'text'=>, 'url'=>, 'link'=>], ...]
])

<section class="px-4 py-8 lg:px-10 lg:py-12" aria-labelledby="qa-titel">
    <div class="mx-auto max-w-6xl">
        <x-ui.section-head :titel="$titel" :sub="$sub" />

        {{-- Mobil zweispaltig wie im Mockup: die vier Einstiege sollen ohne
             Scrollen erfassbar sein.

             Unterhalb von 380 px aber einspaltig: Bei 320 px — der Untergrenze
             aus WCAG 1.4.10 — liefen zwei Spalten um 22 px ueber und erzwangen
             waagerechtes Scrollen. Gemessen mit axe-core, nicht geschaetzt. --}}
        <ul class="grid grid-cols-1 gap-3 min-[380px]:grid-cols-2 lg:grid-cols-4 lg:gap-4">
            @foreach ($karten as $karte)
                <li class="flex">
                    {{-- Die ganze Karte ist der Link — größere Trefferfläche,
                         nur ein Tab-Stopp statt zwei. --}}
                    <a href="{{ $karte['url'] }}"
                       class="group flex flex-1 flex-col rounded-card border border-line bg-card
                              p-4 no-underline hover:border-green lg:p-5">
                        <span class="mb-3 text-green">
                            <x-ui.icon :name="$karte['icon']" :size="28" />
                        </span>
                        <span class="font-display text-base font-semibold text-ink group-hover:underline">
                            {{ $karte['titel'] }}
                        </span>
                        @if (!empty($karte['text']))
                            <span class="mt-1.5 flex-1 text-[0.8125rem] leading-relaxed text-ink-soft">
                                {{ $karte['text'] }}
                            </span>
                        @endif
                        <span class="mt-3 text-[0.8125rem] text-green-deep">
                            {{ $karte['link'] ?? 'Mehr erfahren' }} <span aria-hidden="true">→</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
