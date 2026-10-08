@extends('layouts.app')

@section('title', $gruppe->name.' – '.__('Selbsthilfegruppe'))
@section('description', trim($gruppe->name.': '.$gruppe->teaser, ': '))

@php
    $offen = $gruppe->istOffen();
    $naechster = $gruppe->naechsterTermin();
    $adresse = \App\Models\Group::SHG_ADRESSE;
    $uebersicht = sprachlink('page', ['slug' => 'selbsthilfegruppen']);

    $krumen = [
        ['label' => __('rahmen.start'), 'url' => \App\Models\Language::aktuell()->pfad('/')],
        ['label' => __('Selbsthilfegruppen'), 'url' => $uebersicht],
        ['label' => $gruppe->name, 'url' => null],
    ];

    // Die anderen Gruppen als Weiterführung am Seitenende
    // Datum in der Sprache der Seite: Deutsch „Mittwoch, 14. Oktober 2026,
    // 19:00 Uhr“, Englisch „Wednesday, 14 October 2026, 7:00 pm“.
    $langesDatum = app()->isLocale('en') ? 'l, j F Y, g:i a' : 'l, j. F Y, H:i';

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
{{--
    Mit Titelbild (KEV-82) der Seitenkopf wie auf den anderen Seiten: Name
    und Kurzbeschreibung stehen auf dem Bild. Ohne Bild stehen sie im
    Artikel. Die Brotkrumen bleiben immer im Artikel, in der schmalen
    Spalte; im Kopf stünden sie in der breiten, versetzt zum Text.
--}}
@if ($gruppe->titelbild)
    <x-layout.seitenkopf
        :titel="$gruppe->name"
        :bereich="__('Selbsthilfegruppe')"
        :untertitel="$gruppe->teaser"
        :bild="$gruppe->titelbild" />
@endif

<article @class(['px-4 md:px-8 pb-8 lg:px-10 lg:pb-12', 'pt-8 lg:pt-12' => ! $gruppe->titelbild, 'pt-6' => $gruppe->titelbild])>
    <div class="mx-auto max-w-3xl">

        <x-ui.brotkrumen :krumen="$krumen" />

        <div class="flex flex-wrap items-center gap-2">
            {{-- Mit Titelbild steht „Selbsthilfegruppe“ schon darauf --}}
            @unless ($gruppe->titelbild)
                <span class="rounded-full bg-green-mist px-3 py-1 text-xs text-green-deep">{{ __('Selbsthilfegruppe') }}</span>
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

        @unless ($gruppe->titelbild)
            <h1 class="mt-2 font-display text-[1.75rem] font-medium leading-tight text-green lg:text-4xl">
                {{ $gruppe->name }}
            </h1>

            @if ($gruppe->teaser)
                <p class="mt-2 text-lg leading-relaxed text-ink-soft">{{ $gruppe->teaser }}</p>
            @endif
        @endunless

        <dl class="mt-6 flex flex-col gap-3 rounded-card border border-line bg-card px-5 py-4">
            @if ($gruppe->rhythmus)
                <div class="flex flex-wrap gap-x-3">
                    <dt class="w-36 shrink-0 text-sm text-ink-soft">{{ __('Wann') }}</dt>
                    <dd class="text-ink">
                        {{ $gruppe->rhythmus }}@if ($gruppe->uhrzeit) {{ __('um :uhrzeit', ['uhrzeit' => $gruppe->uhrzeit]) }}@endif
                    </dd>
                </div>
            @endif

            @if ($offen)
                {{-- Rechnet sich aus dem Rhythmus, steht also nie veraltet da
                     (Taddis Wunsch: „und sich dieser natürlich immer aktualisiert“). --}}
                @if ($naechster)
                    <div class="flex flex-wrap gap-x-3">
                        <dt class="w-36 shrink-0 text-sm text-ink-soft">{{ __('Nächster Termin') }}</dt>
                        <dd class="font-semibold text-ink">
                            <time datetime="{{ $naechster->toIso8601String() }}">
                                {{ __(':zeit Uhr', ['zeit' => $naechster->locale(app()->getLocale())->translatedFormat($langesDatum)]) }}
                            </time>
                        </dd>
                    </div>
                @endif
            @else
                <div class="flex flex-wrap gap-x-3">
                    <dt class="w-36 shrink-0 text-sm text-ink-soft">{{ __('Stand') }}</dt>
                    <dd class="text-ink">{{ __('Startet später, noch keine Anmeldung möglich') }}</dd>
                </div>
            @endif

            @if ($gruppe->ort || $gruppe->online)
                <div class="flex flex-wrap gap-x-3">
                    <dt class="w-36 shrink-0 text-sm text-ink-soft">{{ __('Wo') }}</dt>
                    <dd class="text-ink">{{ \Illuminate\Support\Str::ucfirst($gruppe->ort ?: __('online')) }}</dd>
                </div>
            @endif

            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">{{ __('Kosten') }}</dt>
                <dd class="text-ink">{{ __('Kostenfrei und unabhängig von einer Vereinsmitgliedschaft') }}</dd>
            </div>

            <div class="flex flex-wrap gap-x-3">
                <dt class="w-36 shrink-0 text-sm text-ink-soft">{{ $offen ? __('Kontakt und Anmeldung') : __('Kontakt') }}</dt>
                <dd><a href="mailto:{{ $adresse }}" class="text-green-deep underline">{{ $adresse }}</a></dd>
            </div>
        </dl>

        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-3">
            <x-ui.button :href="'mailto:'.$adresse.'?subject='.rawurlencode('Selbsthilfegruppe: '.$gruppe->name)"
                         :variant="$offen ? 'primary' : 'ghost'" size="sm">
                {{ $offen ? __('Per E-Mail anmelden') : __('Per E-Mail nachfragen') }}
            </x-ui.button>

            {{-- Mit dem Beitritt gelten sie, so steht es auf der Übersicht --}}
            <a href="{{ $uebersicht }}#dl-dokumente-zum-herunterladen" class="text-sm text-green-deep underline">
                {{ __('Teilnahmebedingungen und Gruppenregeln') }}
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

<x-layout.weiterlesen :seiten="$andere" :titel="__('Weitere Selbsthilfegruppen')" auf="card" />
@endsection
