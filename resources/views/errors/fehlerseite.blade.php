@extends('layouts.app')

@php
    use App\Models\Language;
    use App\Support\Navigation;

    /*
     * Keine hreflang-Angaben auf einer Fehlerseite: Sie zeigten auf Adressen,
     * die es in keiner Sprache gibt. Das Layout füllt $fassungen nur, wenn es
     * noch nichts vorgesetzt bekommen hat.
     */
    $fassungen = [];
    $istFehlerseite = true;

    $sprache = Language::aktuell();
@endphp

@section('title', __("rahmen.fehler.titel_{$status}"))
@section('description', __("rahmen.fehler.lead_{$status}"))

{{-- Fehlerseiten gehören nicht in den Suchindex. --}}
@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')

    {{--
        Eine Sackgasse ist für diese Zielgruppe kein Schönheitsfehler.

        Wer nach einer Straftat nach Hilfe sucht, oft mobil und unter Druck,
        gibt nach einem „nicht gefunden“ eher auf als jemand, der beiläufig
        surft. Deshalb bietet diese Seite drei Auswege statt einer Entschuldigung:
        die Notfallnummern zuerst, dann die Suche, dann die Hauptbereiche.

        Die Nummern stehen bewusst oben und nicht unten: Sie sind der einzige
        Inhalt hier, der in einer akuten Lage zählt.
    --}}
    <div class="mx-auto max-w-6xl px-4 py-10 lg:px-10 lg:py-14">

        <p class="mb-2 font-display text-sm tracking-[0.08em] text-ink-soft">{{ $status }}</p>

        <h1 class="mb-3 font-display text-3xl font-semibold text-ink lg:text-4xl">
            {{ __("rahmen.fehler.titel_{$status}") }}
        </h1>

        <p class="mb-9 max-w-2xl text-lg text-ink-soft">
            {{ __("rahmen.fehler.lead_{$status}") }}
        </p>

        <div class="grid gap-8 lg:grid-cols-2 lg:gap-10">

            <x-blocks.hilfe-box />

            <div class="flex flex-col gap-8">

                {{-- Suche. Ein normales GET-Formular auf die Beitragsübersicht:
                     ohne JavaScript bedienbar, und das Ergebnis hat eine
                     Adresse, die man weitergeben kann. --}}
                <form method="GET" action="{{ sprachlink('blog.index') }}" role="search"
                      class="flex flex-col gap-2">
                    <label for="fehler-suche" class="font-display text-base font-medium text-ink">
                        {{ __('rahmen.fehler.suche') }}
                    </label>
                    <div class="flex gap-2">
                        <input type="search" id="fehler-suche" name="suche"
                               class="min-h-11 flex-1 rounded-lg border border-line bg-card px-3 text-ink">
                        <x-ui.button type="submit" variant="primary">
                            {{ __('rahmen.fehler.suche_knopf') }}
                        </x-ui.button>
                    </div>
                </form>

                <nav aria-labelledby="fehler-wohin">
                    <h2 id="fehler-wohin" class="mb-3 font-display text-base font-medium text-ink">
                        {{ __('rahmen.fehler.wohin') }}
                    </h2>
                    <ul class="flex flex-col gap-2">
                        <li>
                            <a href="{{ $sprache->pfad('/') }}"
                               class="text-green-deep no-underline hover:underline">
                                {{ __('rahmen.fehler.zur_startseite') }}
                            </a>
                        </li>
                        @foreach (Navigation::haupt() as $punkt)
                            <li>
                                <a href="{{ $punkt['url'] }}"
                                   class="text-green-deep no-underline hover:underline">
                                    {{ $punkt['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>
        </div>
    </div>

@endsection
