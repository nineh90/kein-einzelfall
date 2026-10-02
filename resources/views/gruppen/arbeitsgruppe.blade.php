@extends('layouts.app')

@section('title', $gruppe->name.' – Arbeitsgruppe')
@section('description', trim($gruppe->name.': '.$gruppe->teaser, ': '))

@php
    $offen = $gruppe->istOffen();
    $adresse = \App\Models\Group::AG_ADRESSE;
    $betreff = trim($gruppe->kuerzel.': '.$gruppe->name, ': ');

    // Die anderen AGs als Weiterführung am Seitenende, mit Nummer: So findet
    // man „die AG 6“, von der jemand erzählt hat.
    $andere = \App\Models\Group::veroeffentlicht()->vomTyp('arbeits')
        ->whereKeyNot($gruppe->getKey())
        ->orderBy('position')->orderBy('name')->get()
        ->map(fn ($g) => ['label' => trim($g->kuerzel.': '.$g->name, ': '), 'url' => $g->url()])
        ->all();
@endphp

@section('content')
{{--
    Eine Arbeitsgruppe (KEV-84). Aufbau wie bei den Selbsthilfegruppen
    (gruppen/show): erst das Wichtigste zum Mitmachen, dann Taddis Text mit
    dem Schlusssatz in Handschrift.

    AGs haben keinen festen Termin. Statt „Wann“ steht da, wie sie arbeiten.
    Die Angaben stammen aus Taddis Einleitung auf /arbeitsgruppen („online,
    projektbezogen“, „Ein Einstieg ist jederzeit möglich“, „kostenfrei und
    nicht an eine Vereinsmitgliedschaft gebunden“).
--}}
<article class="px-4 md:px-8 py-8 lg:px-10 lg:py-12">
    <div class="mx-auto max-w-3xl">

        <x-ui.brotkrumen :krumen="[
            ['label' => 'Start', 'url' => '/'],
            ['label' => 'Arbeitsgruppen', 'url' => sprachlink('page', ['slug' => 'arbeitsgruppen'])],
            ['label' => $gruppe->name, 'url' => null],
        ]" />

        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-green-mist px-3 py-1 text-xs text-green-deep">
                {{ $gruppe->kuerzel ?: 'Arbeitsgruppe' }}
            </span>
            @if ($gruppe->online)
                <span class="rounded-full border border-line px-3 py-1 text-xs text-ink-soft">Online</span>
            @endif
            @unless ($offen)
                <span class="rounded-full border border-line px-3 py-1 text-xs text-ink-soft">
                    {{ \App\Models\Group::STATUS[$gruppe->status] ?? $gruppe->status }}
                </span>
            @endunless
        </div>

        <h1 class="mt-2 font-display text-[1.75rem] font-medium leading-tight text-green lg:text-4xl">
            {{ $gruppe->name }}
        </h1>

        @if ($gruppe->teaser)
            <p class="mt-2 text-lg leading-relaxed text-ink-soft">{{ $gruppe->teaser }}</p>
        @endif

        <dl class="mt-6 flex flex-col gap-3 rounded-card border border-line bg-card px-5 py-4">
            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">Wie</dt>
                <dd class="text-ink">
                    {{ $gruppe->online ? 'Online und projektbezogen' : 'Projektbezogen' }}@if ($offen), Einstieg jederzeit möglich @endif
                </dd>
            </div>

            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">Für wen</dt>
                <dd class="text-ink">Betroffene, Angehörige, Interessierte und Fachpersonen</dd>
            </div>

            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">Kosten</dt>
                <dd class="text-ink">Kostenfrei und nicht an eine Vereinsmitgliedschaft gebunden</dd>
            </div>

            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">Kontakt</dt>
                <dd><a href="mailto:{{ $adresse }}" class="text-green-deep underline">{{ $adresse }}</a></dd>
            </div>
        </dl>

        {{-- Der Betreff nennt die AG, wie bisher auf der Karte (KEV-74). --}}
        <div class="mt-4">
            <x-ui.button :href="'mailto:'.$adresse.'?subject='.rawurlencode($betreff)"
                         :variant="$offen ? 'primary' : 'ghost'" size="sm">
                {{ $offen ? 'Per E-Mail mitmachen' : 'Per E-Mail nachfragen' }}
            </x-ui.button>
        </div>

        @if (filled(strip_tags((string) $gruppe->beschreibung)))
            <div class="mt-10 max-w-prose leading-relaxed text-ink
                        [&_a]:text-green-deep [&_a]:underline [&_p]:mb-4">
                {!! $gruppe->beschreibung !!}
            </div>
        @endif

        @if ($gruppe->schlusssatz)
            <p class="mt-6 max-w-prose font-hand text-2xl leading-snug text-green">{{ $gruppe->schlusssatz }}</p>
        @endif
    </div>
</article>

<x-layout.weiterlesen :seiten="$andere" titel="Weitere Arbeitsgruppen" auf="card" />
@endsection
