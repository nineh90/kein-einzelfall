@props([
    'titel' => 'Wissen & Hilfesystem',
    'sub' => null,
    'alleUrl' => null,
    'alleLabel' => 'Zum Wissensbereich',
    'themen' => [],   // [['label'=>, 'url'=>, 'icon'=>], ...]
])

{{-- Abgesetzte Fläche in --card, mit Linien oben/unten statt Schatten. --}}
<section class="border-y border-line bg-card px-4 py-8 lg:px-10 lg:py-12"
         aria-labelledby="themen-titel">
    <div class="mx-auto max-w-6xl">
        <x-ui.section-head :titel="$titel" :sub="$sub"
                           :alleUrl="$alleUrl" :alleLabel="$alleLabel" />

        <ul class="grid gap-x-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($themen as $thema)
                <li>
                    <a href="{{ $thema['url'] }}"
                       class="group flex items-center gap-3 border-b border-line py-3 no-underline">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.5625rem]
                                     bg-green-mist text-green-deep">
                            <x-ui.icon :name="$thema['icon'] ?? 'arrow-right'" :size="17" />
                        </span>
                        <span class="flex-1 text-[0.9375rem] text-ink group-hover:underline">
                            {{ $thema['label'] }}
                        </span>
                        <span class="shrink-0 text-ink-soft" aria-hidden="true">→</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
