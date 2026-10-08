@extends('layouts.app')

@section('title', $gruppe->name.' – '.__('Arbeitsgruppe'))
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

    // Titelbild: das eigene der AG, sonst das der Übersicht /arbeitsgruppen.
    // Aus der Datenbank, damit ein neues Bild im Panel überall ankommt.
    $bild = $gruppe->titelbild ?: \App\Models\Page::where('slug', 'arbeitsgruppen')
        ->where('locale', \App\Models\Language::standardCode())->value('titelbild');
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
{{--
    Seitenkopf mit Bild wie bei den Selbsthilfegruppen (KEV-82): Name und
    Kurzbeschreibung stehen darauf, die Brotkrumen bleiben im Artikel. Ohne
    Bild stehen Name und Kurzbeschreibung im Artikel.
--}}
@if ($bild)
    <x-layout.seitenkopf
        :titel="$gruppe->name"
        :bereich="$gruppe->kuerzel ?: __('Arbeitsgruppe')"
        :untertitel="$gruppe->teaser"
        :bild="$bild" />
@endif

<article @class(['px-4 md:px-8 pb-8 lg:px-10 lg:pb-12', 'pt-8 lg:pt-12' => ! $bild, 'pt-6' => $bild])>
    <div class="mx-auto max-w-3xl">

        <x-ui.brotkrumen :krumen="[
            ['label' => __('rahmen.start'), 'url' => \App\Models\Language::aktuell()->pfad('/')],
            ['label' => __('Arbeitsgruppen'), 'url' => sprachlink('page', ['slug' => 'arbeitsgruppen'])],
            ['label' => $gruppe->name, 'url' => null],
        ]" />

        <div class="flex flex-wrap items-center gap-2">
            {{-- Mit Titelbild steht das Kürzel schon darauf --}}
            @unless ($bild)
                <span class="rounded-full bg-green-mist px-3 py-1 text-xs text-green-deep">
                    {{ $gruppe->kuerzel ?: __('Arbeitsgruppe') }}
                </span>
            @endunless
            @if ($gruppe->online)
                <span class="rounded-full border border-line px-3 py-1 text-xs text-ink-soft">{{ __('Online') }}</span>
            @endif
            @unless ($offen)
                <span class="rounded-full border border-line px-3 py-1 text-xs text-ink-soft">
                    {{ __(\App\Models\Group::STATUS[$gruppe->status] ?? $gruppe->status) }}
                </span>
            @endunless
        </div>

        @unless ($bild)
            <h1 class="mt-2 font-display text-[1.75rem] font-medium leading-tight text-green lg:text-4xl">
                {{ $gruppe->name }}
            </h1>

            @if ($gruppe->teaser)
                <p class="mt-2 text-lg leading-relaxed text-ink-soft">{{ $gruppe->teaser }}</p>
            @endif
        @endunless

        <dl class="mt-6 flex flex-col gap-3 rounded-card border border-line bg-card px-5 py-4">
            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">{{ __('Wie') }}</dt>
                <dd class="text-ink">
                    @if ($offen)
                        {{ $gruppe->online ? __('Online und projektbezogen, Einstieg jederzeit möglich') : __('Projektbezogen, Einstieg jederzeit möglich') }}
                    @else
                        {{ $gruppe->online ? __('Online und projektbezogen') : __('Projektbezogen') }}
                    @endif
                </dd>
            </div>

            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">{{ __('Für wen') }}</dt>
                <dd class="text-ink">{{ __('Betroffene, Angehörige, Interessierte und Fachpersonen') }}</dd>
            </div>

            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">{{ __('Kosten') }}</dt>
                <dd class="text-ink">{{ __('Kostenfrei und nicht an eine Vereinsmitgliedschaft gebunden') }}</dd>
            </div>

            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">{{ __('Kontakt') }}</dt>
                <dd><a href="mailto:{{ $adresse }}" class="text-green-deep underline">{{ $adresse }}</a></dd>
            </div>
        </dl>

        {{-- Der Betreff nennt die AG, wie bisher auf der Karte (KEV-74). --}}
        <div class="mt-4">
            <x-ui.button :href="'mailto:'.$adresse.'?subject='.rawurlencode($betreff)"
                         :variant="$offen ? 'primary' : 'ghost'" size="sm">
                {{ $offen ? __('Per E-Mail mitmachen') : __('Per E-Mail nachfragen') }}
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

<x-layout.weiterlesen :seiten="$andere" :titel="__('Weitere Arbeitsgruppen')" auf="card" />
@endsection
