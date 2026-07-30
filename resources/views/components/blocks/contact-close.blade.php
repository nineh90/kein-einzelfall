@props([
    'titel',
    'text' => null,
    'hinweis' => null,   // Bedienhinweis, klar abgesetzt vom Inhaltstext
    'ctas' => [],
])

{{-- Leere Knöpfe aus dem Panel aussortieren, siehe helpers.php --}}
@php $knoepfe = knoepfe($ctas); @endphp

<section class="px-4 py-10 text-center lg:px-10 lg:py-14" aria-labelledby="kontakt-titel">
    <div class="mx-auto max-w-2xl">
        <span class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full
                     bg-green-mist text-green-deep">
            <x-ui.icon name="heart" :size="24" />
        </span>

        <h2 id="kontakt-titel" class="font-display text-[1.3125rem] font-medium text-ink lg:text-[1.75rem]">
            {{ $titel }}
        </h2>

        @if ($text)
            <p class="mx-auto mt-3 max-w-prose text-ink-soft">{{ $text }}</p>
        @endif

        @if ($knoepfe)
            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                @foreach ($knoepfe as $cta)
                    <x-ui.button :href="$cta['url']" :variant="$cta['variant'] ?? 'primary'">
                        {{ $cta['label'] }}
                    </x-ui.button>
                @endforeach
            </div>
        @endif

        @if ($hinweis)
            <p class="mx-auto mt-5 max-w-prose text-sm text-ink-soft">{{ $hinweis }}</p>
        @endif

        {{-- Vertrauenssignale. Die Aussagen müssen stimmen — der Notausgang ist
             umgesetzt, TLS ist Pflicht, die Verschlüsselung sensibler Felder kommt
             mit dem Kontaktformular. Nichts hier ist Dekoration. --}}
        <ul class="mt-7 flex flex-wrap justify-center gap-x-6 gap-y-2 text-xs text-ink-soft">
            @foreach ([
                ['icon' => 'lock',   'text' => 'TLS-verschlüsselt'],
                ['icon' => 'shield', 'text' => 'Vertraulich & DSGVO-konform'],
                ['icon' => 'exit',   'text' => 'Notausgang jederzeit'],
            ] as $trust)
                <li class="flex items-center gap-1.5">
                    <x-ui.icon :name="$trust['icon']" :size="15" />
                    {{ $trust['text'] }}
                </li>
            @endforeach
        </ul>
    </div>
</section>
