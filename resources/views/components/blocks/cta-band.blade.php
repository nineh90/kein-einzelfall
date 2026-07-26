@props([
    'eyebrow' => null,
    'zitat',
    'notiz' => null,
    'ctas' => [],
    'wasserzeichen' => 'KE!N',
])

<section class="px-4 py-8 lg:px-10 lg:py-12">
    <div class="relative mx-auto max-w-6xl overflow-hidden rounded-band bg-green-deep px-6 py-8 lg:px-10 lg:py-11">

        {{-- Dekoratives Wasserzeichen. Auf schmalen Viewports ausgeblendet:
             dort würde es den Text nur unruhig machen. --}}
        <span aria-hidden="true"
              class="pointer-events-none absolute -right-4 top-1/2 hidden -translate-y-1/2
                     select-none font-hand text-[9.375rem] leading-none text-[#2B4536] opacity-50 lg:block">
            {{ $wasserzeichen }}
        </span>

        <div class="relative grid gap-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
            <div>
                @if ($eyebrow)
                    <x-ui.eyebrow auf="dunkel" class="mb-3">{{ $eyebrow }}</x-ui.eyebrow>
                @endif

                <blockquote class="font-display text-xl italic leading-snug text-on-green lg:text-2xl">
                    {{ $zitat }}
                </blockquote>

                @if ($notiz)
                    <p class="mt-3 text-sm text-[#9FB6A6]">{{ $notiz }}</p>
                @endif
            </div>

            @if ($ctas)
                <div class="flex flex-col gap-3">
                    @foreach ($ctas as $cta)
                        <x-ui.button :href="$cta['url']" :variant="$cta['variant'] ?? 'light'">
                            {{ $cta['label'] }}
                        </x-ui.button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
