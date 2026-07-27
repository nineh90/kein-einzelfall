@props([
    'eyebrow' => null,
    'titel' => null,
    'auf' => 'cream',      // cream | card
    'cta' => null,         // ['label' =>, 'url' =>, 'variant' =>]
    'anker' => null,       // Sprungziel für das Inhaltsverzeichnis
])

{{-- Basis-Textblock. Prosa mit begrenzter Zeilenlänge (max-w-prose ≈ 65 Zeichen) —
     lange Zeilen sind für Menschen mit Lese- oder Konzentrationsschwierigkeiten
     deutlich anstrengender. --}}
<section @class([
    'px-4 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div class="mx-auto max-w-6xl">
        <div class="max-w-prose">
            @if ($eyebrow)
                <x-ui.eyebrow class="mb-3">{{ $eyebrow }}</x-ui.eyebrow>
            @endif

            @if ($titel)
                {{-- scroll-mt sorgt dafür, dass die Überschrift beim Anspringen nicht
                     unter dem klebenden Kopfbereich verschwindet. --}}
                <h2 @if ($anker) id="{{ $anker }}" @endif
                    class="mb-4 scroll-mt-24 font-display text-2xl font-medium text-ink lg:text-3xl">
                    {{ $titel }}
                </h2>
            @endif

            <div class="flex flex-col gap-4 leading-relaxed text-ink-soft
                        [&_a]:text-green-deep [&_a]:underline
                        [&_li]:mb-1 [&_ul]:list-disc [&_ul]:pl-5">
                {{ $slot }}
            </div>

            @if ($cta)
                <div class="mt-6">
                    <x-ui.button :href="$cta['url']" :variant="$cta['variant'] ?? 'primary'">
                        {{ $cta['label'] }}
                    </x-ui.button>
                </div>
            @endif
        </div>
    </div>
</section>
