@extends('layouts.app')

@section('title', $gruppe->name.' – Selbsthilfegruppe')
@section('description', trim($gruppe->name.': '.$gruppe->teaser, ': '))

@php
    $offen = $gruppe->istOffen();
    $naechster = $gruppe->naechsterTermin();
    $adresse = \App\Models\Group::SHG_ADRESSE;
    $uebersicht = sprachlink('page', ['slug' => 'selbsthilfegruppen']);

    // Die anderen Gruppen als Weiterführung am Seitenende
    $andere = \App\Models\Group::veroeffentlicht()->vomTyp('selbsthilfe')
        ->whereKeyNot($gruppe->getKey())
        ->orderBy('position')->orderBy('name')->get()
        ->map(fn ($g) => ['label' => $g->name, 'url' => $g->url()])
        ->all();
@endphp

@section('content')
{{--
    Eine Selbsthilfegruppe (KEV-73). Aufbau wie bei einer Veranstaltung:
    erst das Wichtigste zum Mitmachen (Wann, nächster Termin, Wo, Kosten,
    Kontakt), dann Taddis Text mit dem Schlusssatz in Handschrift.
--}}
<article class="px-4 md:px-8 py-8 lg:px-10 lg:py-12">
    <div class="mx-auto max-w-3xl">

        <x-ui.brotkrumen :krumen="[
            ['label' => 'Start', 'url' => '/'],
            ['label' => 'Selbsthilfegruppen', 'url' => $uebersicht],
            ['label' => $gruppe->name, 'url' => null],
        ]" />

        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-green-mist px-3 py-1 text-xs text-green-deep">Selbsthilfegruppe</span>
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
            @if ($gruppe->rhythmus)
                <div class="flex flex-wrap gap-x-3">
                    <dt class="w-36 shrink-0 text-sm text-ink-soft">Wann</dt>
                    <dd class="text-ink">
                        {{ $gruppe->rhythmus }}@if ($gruppe->uhrzeit) um {{ $gruppe->uhrzeit }}@endif
                    </dd>
                </div>
            @endif

            @if ($offen)
                {{-- Rechnet sich aus dem Rhythmus, steht also nie veraltet da
                     (Taddis Wunsch: „und sich dieser natürlich immer aktualisiert“). --}}
                @if ($naechster)
                    <div class="flex flex-wrap gap-x-3">
                        <dt class="w-36 shrink-0 text-sm text-ink-soft">Nächster Termin</dt>
                        <dd class="font-semibold text-ink">
                            <time datetime="{{ $naechster->toIso8601String() }}">
                                {{ $naechster->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}, {{ $naechster->format('H:i') }} Uhr
                            </time>
                        </dd>
                    </div>
                @endif
            @else
                <div class="flex flex-wrap gap-x-3">
                    <dt class="w-36 shrink-0 text-sm text-ink-soft">Stand</dt>
                    <dd class="text-ink">Startet später, noch keine Anmeldung möglich</dd>
                </div>
            @endif

            @if ($gruppe->ort || $gruppe->online)
                <div class="flex flex-wrap gap-x-3">
                    <dt class="w-36 shrink-0 text-sm text-ink-soft">Wo</dt>
                    <dd class="text-ink">{{ \Illuminate\Support\Str::ucfirst($gruppe->ort ?: 'online') }}</dd>
                </div>
            @endif

            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">Kosten</dt>
                <dd class="text-ink">Kostenfrei und unabhängig von einer Vereinsmitgliedschaft</dd>
            </div>

            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">{{ $offen ? 'Kontakt und Anmeldung' : 'Kontakt' }}</dt>
                <dd><a href="mailto:{{ $adresse }}" class="text-green-deep underline">{{ $adresse }}</a></dd>
            </div>
        </dl>

        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-3">
            <x-ui.button :href="'mailto:'.$adresse.'?subject='.rawurlencode('Selbsthilfegruppe: '.$gruppe->name)"
                         :variant="$offen ? 'primary' : 'ghost'" size="sm">
                {{ $offen ? 'Per E-Mail anmelden' : 'Per E-Mail nachfragen' }}
            </x-ui.button>

            {{-- Mit dem Beitritt gelten sie, so steht es auf der Übersicht --}}
            <a href="{{ $uebersicht }}#dl-dokumente-zum-herunterladen" class="text-sm text-green-deep underline">
                Teilnahmebedingungen und Gruppenregeln
            </a>
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

<x-layout.weiterlesen :seiten="$andere" titel="Weitere Selbsthilfegruppen" auf="card" />
@endsection
