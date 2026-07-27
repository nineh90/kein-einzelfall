@props([
    'titel' => null,
    'text' => null,
    'art' => 'hinweis',   // hinweis | frist | wichtig
])

@php
    // Bewusst zurückhaltend eingefärbt. Grelle Warnfarben erzeugen bei Menschen
    // in belastenden Situationen Druck — die Botschaft soll auffallen, nicht
    // alarmieren. Deshalb trägt die Aussage ein Symbol und ein Rand, nicht
    // eine grosse rote Fläche.
    $stil = match ($art) {
        'frist' => ['icon' => 'lock', 'rahmen' => 'border-alert', 'flaeche' => 'bg-card',
                    'akzent' => 'text-alert', 'standardtitel' => 'Auf die Frist achten'],
        'wichtig' => ['icon' => 'shield', 'rahmen' => 'border-green', 'flaeche' => 'bg-green-mist',
                      'akzent' => 'text-green-deep', 'standardtitel' => 'Wichtig'],
        default => ['icon' => 'message', 'rahmen' => 'border-line', 'flaeche' => 'bg-card',
                    'akzent' => 'text-green-deep', 'standardtitel' => 'Gut zu wissen'],
    };
@endphp

{{--
    Hervorgehobener Hinweis.

    Gedacht für das, was im Fliesstext untergeht, aber teuer werden kann:
    Widerspruchsfristen, notwendige Nachweise, häufige Missverständnisse.
--}}
<section class="px-4 py-4 lg:px-10">
    <div class="mx-auto max-w-6xl">
        <aside class="max-w-prose rounded-card border-2 {{ $stil['rahmen'] }} {{ $stil['flaeche'] }} px-5 py-4">
            <div class="flex gap-3">
                <span class="mt-0.5 shrink-0 {{ $stil['akzent'] }}">
                    <x-ui.icon :name="$stil['icon']" :size="20" />
                </span>

                <div class="flex-1">
                    <p class="font-display text-base font-medium {{ $stil['akzent'] }}">
                        {{ $titel ?: $stil['standardtitel'] }}
                    </p>

                    @if ($text)
                        <p class="mt-1.5 leading-relaxed text-ink">{{ $text }}</p>
                    @endif

                    {{ $slot }}
                </div>
            </div>
        </aside>
    </div>
</section>
