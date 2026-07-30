@props([
    'eyebrow' => null,
    'titel',
    'text' => null,
    'hand' => null,          // handschriftlicher Akzent unter der Grafik
    'ctas' => [],            // [['label'=>, 'url'=>, 'variant'=>], ...]
])

<section class="px-4 pt-8 lg:px-10 lg:pt-16">
    <div class="mx-auto grid max-w-6xl items-center gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:gap-10">

        {{-- Auf schmalen Viewports steht die Grafik oben (order-first), wie im Mockup. --}}
        <div class="order-2 lg:order-1">
            @if ($eyebrow)
                <x-ui.eyebrow class="mb-4">{{ $eyebrow }}</x-ui.eyebrow>
            @endif

            <h1 class="font-display text-[1.75rem] font-medium leading-[1.18] text-ink lg:text-[2.75rem]">
                {{ $titel }}
            </h1>

            @if ($text)
                <p class="mt-4 max-w-prose text-[0.9375rem] leading-relaxed text-ink-soft lg:text-lg">
                    {{ $text }}
                </p>
            @endif

            @if ($ctas)
                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    @foreach ($ctas as $cta)
                        <x-ui.button :href="$cta['url']" :variant="$cta['variant'] ?? 'primary'">
                            {{ $cta['label'] }}
                        </x-ui.button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="order-1 lg:order-2">
            {{-- Der Steinstapel aus dem Mockup: rein aus CSS, keine Bilddatei.
                 Dekorativ, deshalb aria-hidden — er trägt keine Information. --}}
            <div class="relative mx-auto flex h-56 w-full max-w-sm items-end justify-center
                        lg:h-[22.5rem]"
                 aria-hidden="true">
                <div class="absolute inset-0 m-auto h-48 w-48 rounded-full
                            bg-[radial-gradient(circle,rgb(220_230_219/.9)_0%,transparent_70%)]
                            lg:h-72 lg:w-72"></div>

                <div class="relative flex flex-col items-center -space-y-1.5">
                    @foreach ([
                        ['w' => 'w-11', 'h' => 'h-5',  'bg' => 'from-[#009640] to-[#00702F]'],
                        ['w' => 'w-16', 'h' => 'h-7',  'bg' => 'from-[#EFE4CC] to-[#D9C7A2]'],
                        ['w' => 'w-24', 'h' => 'h-9',  'bg' => 'from-[#E7DCC2] to-[#C3AE83]'],
                        ['w' => 'w-32', 'h' => 'h-11', 'bg' => 'from-[#EFE4CC] to-[#C3AE83]'],
                        ['w' => 'w-40', 'h' => 'h-12', 'bg' => 'from-[#E2D3B2] to-[#C3AE83]'],
                    ] as $stein)
                        <span class="{{ $stein['w'] }} {{ $stein['h'] }} rounded-[50%]
                                     bg-gradient-to-b {{ $stein['bg'] }}
                                     shadow-[0_7px_12px_-7px_rgb(44_38_32/.38),inset_0_-3px_6px_rgb(44_38_32/.12),inset_0_3px_5px_rgb(255_255_255/.4)]"></span>
                    @endforeach
                </div>
            </div>

            @if ($hand)
                <p class="mt-3 text-center font-hand text-2xl text-green lg:text-[1.6875rem]">
                    {{ $hand }}
                </p>
            @endif
        </div>
    </div>
</section>
