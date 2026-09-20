@extends('layouts.app')

@section('title', 'Glossar')
@section('description', 'Abkürzungen und Fachbegriffe aus dem sozialen Entschädigungsrecht — kurz erklärt.')

{{--
    Glossar.

    Aufbau wie die übrigen Übersichtsseiten: aussen kein Rand, jeder Abschnitt
    setzt `px-4 lg:px-10` und `max-w-6xl` selbst.

    Zwei Entscheidungen, die hier zählen:

    - Jeder Eintrag hat ein eigenes Sprungziel (`#gdb`). Ein Glossar, aus dem
      man nicht auf einen einzelnen Begriff verlinken kann, ist in einer Antwort
      des Vereins auf eine Anfrage nur die halbe Hilfe.
    - Die Buchstabenleiste ist ein <nav> mit echten Ankern und kommt ohne
      JavaScript aus. Sie ist eine Abkürzung, kein Filter — die vollständige
      Liste bleibt sichtbar, und die Suche des Browsers (Strg+F) findet alles.
--}}

@section('content')

    <x-layout.seitenkopf
        titel="Glossar"
        bereich="Wissen"
        :krumen="[
            ['label' => 'Start', 'url' => '/'],
            ['label' => 'Glossar', 'url' => null],
        ]"
        lead="Abkürzungen und Fachbegriffe, wie sie in Bescheiden und Formularen vorkommen — kurz erklärt." />

    {{-- Flächenwechsel wie auf den Inhaltsseiten; der Seitenkopf darüber ist
         eine Karte. Das Verzeichnis nimmt die Gegenfläche des letzten
         Bausteins — seine Einträge stehen dann auf der jeweils anderen. --}}
    @php
        $flaechen = $einleitung
            ? \App\Models\PageBlock::flaechenFuer($einleitung->blocks, davor: 'card')
            : [];
        $listeAuf = \App\Models\PageBlock::gegenflaeche(end($flaechen) ?: 'card');
        $karte = $listeAuf === 'card' ? 'bg-cream' : 'bg-card';
    @endphp

    @if ($einleitung)
        @foreach ($einleitung->blocks as $block)
            <x-block :block="$block" :flaeche="$flaechen[$loop->index]" />
        @endforeach
    @endif

    <div @class(['px-4 py-8 lg:px-10 lg:py-12', 'bg-card border-y border-line' => $listeAuf === 'card'])
         @if ($ersatzsprache) lang="{{ $ersatzsprache->code }}" dir="{{ $ersatzsprache->richtung }}" @endif>
        <div class="mx-auto max-w-6xl">

            @if ($gruppen->isEmpty())
                <p class="max-w-prose leading-relaxed text-ink-soft">
                    Hier entsteht ein Verzeichnis der Abkürzungen und Fachbegriffe.
                    Es wird nach und nach ergänzt.
                </p>
            @else

                {{-- Buchstabenleiste. Nur die Buchstaben, zu denen es auch
                     etwas gibt: Ein ausgegrautes „Q“ nimmt Platz weg und sagt
                     nichts, was die Liste darunter nicht auch sagt. --}}
                <nav aria-label="Nach Anfangsbuchstaben springen" class="mb-10">
                    <ul class="flex flex-wrap gap-1.5">
                        @foreach ($gruppen->keys() as $buchstabe)
                            <li>
                                <a href="#buchstabe-{{ $buchstabe === '#' ? 'zeichen' : Str::lower($buchstabe) }}"
                                   class="inline-flex h-9 w-9 items-center justify-center rounded-full
                                          border border-line font-display text-sm text-ink no-underline
                                          hover:border-green hover:bg-green hover:text-on-green">
                                    {{ $buchstabe }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                @foreach ($gruppen as $buchstabe => $eintraege)
                    <section class="mb-10"
                             aria-labelledby="buchstabe-{{ $buchstabe === '#' ? 'zeichen' : Str::lower($buchstabe) }}">

                        {{-- scroll-mt hält die Überschrift beim Anspringen
                             unter dem klebenden Kopfbereich sichtbar. --}}
                        <h2 id="buchstabe-{{ $buchstabe === '#' ? 'zeichen' : Str::lower($buchstabe) }}"
                            class="mb-4 scroll-mt-24 border-b border-line pb-2 font-display text-xl
                                   font-medium text-green-deep">
                            {{ $buchstabe }}
                        </h2>

                        {{-- <dl> und nicht <ul>: Begriff und Erklärung sind ein
                             Paar, und Vorlesehilfen sagen das auch so an. --}}
                        <dl class="grid gap-4 md:grid-cols-2">
                            @foreach ($eintraege as $eintrag)
                                <div id="{{ $eintrag->slug }}"
                                     class="scroll-mt-24 rounded-card border border-line {{ $karte }} px-5 py-4">

                                    <dt class="font-display text-lg text-ink">
                                        @if ($eintrag->kuerzel)
                                            {{-- <abbr> mit title: Wer mit der Maus darauf
                                                 zeigt, sieht die Auflösung; Vorlesehilfen
                                                 können sie ansagen. Der ausgeschriebene
                                                 Begriff steht trotzdem sichtbar daneben —
                                                 ein title allein ist auf Tastatur und
                                                 Touch nicht erreichbar. --}}
                                            <abbr title="{{ $eintrag->begriff }}"
                                                  class="font-semibold no-underline">{{ $eintrag->kuerzel }}</abbr>
                                            <span class="text-ink-soft"> — {{ $eintrag->begriff }}</span>
                                        @else
                                            <span class="font-semibold">{{ $eintrag->begriff }}</span>
                                        @endif
                                    </dt>

                                    <dd class="mt-2 leading-relaxed text-ink-soft">
                                        {{ $eintrag->erklaerung }}

                                        @if ($eintrag->mehr_url)
                                            <a href="{{ $eintrag->mehr_url }}"
                                               class="mt-2 inline-block text-green-deep underline">
                                                {{ $eintrag->mehr_label ?: 'Mehr dazu' }}
                                                <span class="sr-only">zu „{{ $eintrag->ueberschrift() }}“</span>
                                            </a>
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endforeach
            @endif
        </div>
    </div>
@endsection
