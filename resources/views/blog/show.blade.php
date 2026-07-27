@extends('layouts.app')

@section('title', $beitrag->titel)
@section('description', $beitrag->meta_description ?: $beitrag->anriss(155))

@php
    // Strukturierte Daten für Suchmaschinen. Die Altseite hat nur
    // Organization/WebPage — Article ist hier echter Zugewinn.
    //
    // Bewusst hier zusammengebaut statt per @json im Template: Blade kommt mit
    // mehrzeiligen Arrays in Direktiven nicht zurecht.
    $strukturierteDaten = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $beitrag->titel,
        'datePublished' => $beitrag->published_at->toIso8601String(),
        'dateModified' => $beitrag->updated_at->toIso8601String(),
        'description' => $beitrag->meta_description ?: $beitrag->anriss(155),
        'author' => ['@type' => 'Organization', 'name' => 'KE!N EINZELFALL e.V.'],
        'publisher' => ['@type' => 'Organization', 'name' => 'KE!N EINZELFALL e.V.'],
        'mainEntityOfPage' => route('blog.show', $beitrag->slug),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
@endphp

@push('head')
    <script type="application/ld+json" @isset($cspNonce) nonce="{{ $cspNonce }}" @endisset>
        {!! $strukturierteDaten !!}
    </script>
@endpush

@section('content')
<article class="px-4 py-8 lg:px-10 lg:py-12">
    <div class="mx-auto max-w-3xl">

        <x-ui.brotkrumen :krumen="array_filter([
            ['label' => 'Start', 'url' => '/'],
            ['label' => 'Aktuelles', 'url' => route('blog.index')],
            $beitrag->category
                ? ['label' => $beitrag->category->name,
                   'url' => route('blog.index', ['kategorie' => $beitrag->category->slug])]
                : null,
            ['label' => $beitrag->titel, 'url' => null],
        ])" />

        <p class="text-xs uppercase tracking-[0.04em] text-ink-soft">
            <time datetime="{{ $beitrag->published_at->toDateString() }}">
                {{ $beitrag->published_at->translatedFormat('j. F Y') }}
            </time>
        </p>

        <h1 class="mt-1.5 font-display text-[1.75rem] font-medium leading-tight text-ink lg:text-4xl">
            {{ $beitrag->titel }}
        </h1>

        @if ($beitrag->teaser)
            <p class="mt-4 text-lg leading-relaxed text-ink-soft">{{ $beitrag->teaser }}</p>
        @endif

        @if ($beitrag->bild_pfad)
            <img src="{{ $beitrag->bild_pfad }}" alt="{{ $beitrag->bild_alt }}"
                 class="mt-6 w-full rounded-card">
        @endif

        {{-- Zeilenlänge begrenzt: lange Zeilen sind für Menschen mit Lese- oder
             Konzentrationsschwierigkeiten deutlich anstrengender. --}}
        <div class="mt-6 max-w-prose leading-relaxed text-ink
                    [&_a]:text-green-deep [&_a]:underline
                    [&_h2]:mt-8 [&_h2]:font-display [&_h2]:text-xl [&_h2]:text-ink
                    [&_h3]:mt-6 [&_h3]:font-display [&_h3]:text-lg [&_h3]:text-ink
                    [&_li]:mb-1 [&_ol]:list-decimal [&_ol]:pl-5
                    [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5">
            {!! $beitrag->inhalt !!}
        </div>

        @if ($weitere->isNotEmpty())
            <aside class="mt-12 border-t border-line pt-8" aria-labelledby="weitere-titel">
                <h2 id="weitere-titel" class="mb-4 font-display text-xl text-ink">Weitere Beiträge</h2>
                <ul class="flex flex-col gap-3">
                    @foreach ($weitere as $andere)
                        <li>
                            <a href="{{ route('blog.show', $andere->slug) }}"
                               class="flex items-baseline gap-3 no-underline">
                                <time datetime="{{ $andere->published_at->toDateString() }}"
                                      class="shrink-0 text-xs text-ink-soft">
                                    {{ $andere->published_at->format('d.m.Y') }}
                                </time>
                                <span class="text-ink hover:underline">{{ $andere->titel }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>
        @endif
    </div>
</article>
@endsection
