@extends('layouts.app')

@section('title', $page->titel)
@section('description', $page->meta_description)

@if ($page->noindex)
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush
@endif

@section('content')

@php
    // Sprungmarken aus den Abschnittsüberschriften. Die Komponente blendet sich
    // selbst aus, wenn es weniger als vier gibt — kurze Seiten brauchen kein
    // Inhaltsverzeichnis.
    $sprungpunkte = $page->blocks
        ->filter(fn ($b) => $b->anker() && ($b->data['titel'] ?? null))
        ->map(fn ($b) => ['anker' => $b->anker(), 'titel' => $b->data['titel']])
        ->values()
        ->all();
@endphp

    {{-- Seitentitel. Die h1 steht hier und nicht im Block, damit sie garantiert
         genau einmal vorkommt — egal wie die Blöcke zusammengesetzt sind. --}}
    <div class="px-4 pt-8 lg:px-10 lg:pt-14">
        <div class="mx-auto max-w-6xl">
            <h1 class="max-w-prose font-display text-[1.75rem] font-medium leading-tight text-ink lg:text-4xl">
                {{ $page->titel }}
            </h1>

            @if (count($sprungpunkte) >= 4)
                <div class="mt-6 max-w-prose">
                    <x-ui.sprungmarken :punkte="$sprungpunkte" />
                </div>
            @endif
        </div>
    </div>

    @foreach ($page->blocks as $block)
        <x-block :block="$block" />
    @endforeach

@endsection
