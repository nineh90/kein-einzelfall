@props([
    'titel',
    'sub' => null,
    'alleUrl' => null,
    'alleLabel' => null,
    'stufe' => 'h2',
])

@php $alleLabel ??= __('rahmen.alle_ansehen'); @endphp

<div class="mb-6 flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
    <div>
        <{{ $stufe }} class="font-display text-2xl font-semibold text-ink">{{ $titel }}</{{ $stufe }}>
        @if ($sub)
            <p class="mt-1 text-sm text-ink-soft">{{ $sub }}</p>
        @endif
    </div>

    @if ($alleUrl)
        {{-- Linktext trägt den Kontext mit: "Alle ansehen" allein ist auf einer
             Seite mit mehreren solcher Links nicht unterscheidbar (WCAG 2.4.4). --}}
        <a href="{{ $alleUrl }}"
           class="shrink-0 text-sm text-green-deep no-underline hover:underline">
            {{ $alleLabel }} <span aria-hidden="true">→</span>
        </a>
    @endif
</div>
