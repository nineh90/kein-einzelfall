@extends('layouts.app')

@section('title', $anfrage !== '' ? 'Suche: '.$anfrage : 'Suche')
@section('description', 'Finde Seiten, Begriffe und Selbsthilfegruppen auf dieser Website.')

{{-- Suchergebnisse gehören nicht in den Index einer Suchmaschine: Sie sind für
     jede Anfrage anders und tragen keinen eigenen Inhalt. --}}
@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

{{--
    Suche (KEV-23).

    ── Warum ein Formular und kein Chatfenster ────────────────────────────────

    Der Auftrag lautete zunächst „Chatbot". Ein Eingabefeld mit Trefferliste
    kann dasselbe, ist aber ohne JavaScript bedienbar, mit einer Vorlesehilfe
    seit Jahrzehnten ein gelöstes Muster und hinterlässt keinen Gesprächsverlauf,
    den jemand später findet. Für diese Zielgruppe zählt jeder dieser Punkte.

    ── Barrierefreiheit ───────────────────────────────────────────────────────

    - `role="search"` am Formular, damit Screenreader-Nutzer den Bereich direkt
      anspringen können (Landmarken-Navigation).
    - Ein echtes, sichtbares <label>. Ein Platzhalter ist kein Ersatz: Er
      verschwindet beim Tippen, und genau dann braucht ihn jemand mit
      Konzentrationsschwierigkeiten noch.
    - Die Trefferzahl steht als Überschrift über der Liste und wird per
      `aria-live` angesagt, falls die Seite später ohne Neuladen aktualisiert.
    - Die Treffer sind eine echte <ol> mit echten Links: zählbar, in jeder
      Vorlesehilfe navigierbar, mit Tastatur erreichbar, und „in neuem Tab
      öffnen" funktioniert.
    - Autofokus ist bewusst NICHT gesetzt. Er reisst Screenreader-Nutzer aus der
      Seitenstruktur, bevor sie die Überschrift gehört haben.
--}}

@section('content')

    <x-layout.seitenkopf
        titel="Suche"
        :krumen="[
            ['label' => 'Start', 'url' => '/'],
            ['label' => 'Suche', 'url' => null],
        ]"
        lead="Beschreibe mit eigenen Worten, was du suchst. Du musst die Fachbegriffe nicht kennen." />

    <section class="px-4 md:px-8 py-8 lg:px-10 lg:py-10">
        <div class="mx-auto max-w-6xl">

            {{-- action ohne Parameter, Methode GET: Der Browser hängt `q` selbst
                 an. Kein JavaScript beteiligt. --}}
            <form role="search" method="get" action="{{ url('/suche') }}" class="max-w-prose">
                <label for="suchfeld" class="mb-2 block font-display text-lg text-ink">
                    Wonach suchst du?
                </label>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <input type="search"
                           id="suchfeld"
                           name="q"
                           value="{{ $anfrage }}"
                           maxlength="200"
                           autocomplete="off"
                           spellcheck="false"
                           aria-describedby="suchhilfe"
                           class="w-full rounded-full border border-line bg-card px-5 py-3 text-ink
                                  placeholder:text-ink-soft focus:border-green focus:outline-none">

                    <x-ui.button type="submit" variant="primary">Suchen</x-ui.button>
                </div>

                <p id="suchhilfe" class="mt-2 text-sm text-ink-soft">
                    Zum Beispiel: „Brief vom Amt", „die glauben mir nicht" oder „Ausweis beantragen".
                </p>

                {{-- Ehrlich statt beruhigend.

                     Was gesucht wurde, steht danach in der Adresszeile und im
                     Verlauf des Browsers. Der Notausgang kann das nicht
                     rückgängig machen — aus einer Webseite heraus lässt sich
                     kein Verlauf löschen. Wer auf dieser Seite mit einem
                     Notausgang wirbt, muss auch sagen, wo dessen Grenze liegt;
                     ein Versprechen ohne Deckung wäre hier gefährlicher als
                     gar keins. Steht klein und ruhig da: Es soll niemanden vom
                     Suchen abhalten, nur erreichbar sein. --}}
                <p class="mt-2 text-sm text-ink-soft">
                    Deine Suche steht danach in der Adresszeile.
                    <a href="/barrierefreiheit" class="text-green-deep underline">Wie du keine Spuren hinterlässt</a>
                </p>
            </form>
        </div>
    </section>

    {{-- Der Krisenhinweis steht VOR den Treffern und nicht darunter.

         Wer „ich kann nicht mehr" in ein Suchfeld tippt, sucht keine
         Antragsseite. Die Trefferliste bleibt trotzdem stehen — vielleicht war
         es doch die Formulierung und nicht die Lage. Entschieden wird das hier
         nicht, angeboten schon. --}}
    @if ($krise)
        <section class="px-4 md:px-8 pb-2 lg:px-10" aria-labelledby="krisenhinweis">
            <div class="mx-auto max-w-6xl">
                <div class="max-w-prose rounded-card border-2 border-green bg-green-mist px-5 py-4">
                    <p id="krisenhinweis" class="font-display text-base font-medium text-green-deep">
                        Wenn es dir gerade sehr schlecht geht
                    </p>
                    <p class="mt-1.5 leading-relaxed text-ink">
                        Du musst das nicht allein aushalten. Unter den Nummern unten nimmt
                        rund um die Uhr jemand ab — kostenlos und auf Wunsch anonym.
                    </p>
                    <p class="mt-3">
                        <a href="#hilfe-nummern" class="text-green-deep underline">Zu den Hilfe-Nummern</a>
                    </p>
                </div>
            </div>
        </section>
    @endif

    <section class="px-4 md:px-8 pb-10 lg:px-10 lg:pb-14" aria-labelledby="trefferzahl">
        <div class="mx-auto max-w-6xl">

            @if ($anfrage === '')
                <p class="max-w-prose leading-relaxed text-ink-soft">
                    Gib oben ein, was du suchst. Ganze Sätze sind ausdrücklich erlaubt.
                </p>

            @elseif ($treffer === [])
                {{-- Eine leere Trefferliste ist eine Sackgasse. Wer hier landet,
                     hat es schon mit eigenen Worten versucht — ihn jetzt mit
                     „nichts gefunden" stehen zu lassen, wäre das Gegenteil von
                     dem, was diese Seite sein will. --}}
                <h2 id="trefferzahl" class="mb-3 font-display text-xl text-ink">
                    Dazu haben wir nichts gefunden
                </h2>

                <div class="max-w-prose leading-relaxed text-ink-soft">
                    <p>
                        Das liegt nicht an dir. Vielleicht nennen wir die Sache anders, oder
                        es steht noch nicht auf der Seite.
                    </p>
                    <p class="mt-3">Diese Wege führen weiter:</p>
                </div>

                <ul class="mt-4 flex flex-col gap-2 max-w-prose">
                    <li><a href="/anfragen" class="text-green-deep underline">Frag uns direkt — auf Wunsch anonym</a></li>
                    <li><a href="/glossar" class="text-green-deep underline">Im Glossar nachschlagen</a></li>
                    <li><a href="/wissen" class="text-green-deep underline">Im Bereich Wissen stöbern</a></li>
                    <li><a href="/selbsthilfegruppen" class="text-green-deep underline">Zu den Selbsthilfegruppen</a></li>
                </ul>

            @else
                <h2 id="trefferzahl" aria-live="polite" class="mb-5 font-display text-xl text-ink">
                    {{ count($treffer) }}
                    {{ count($treffer) === 1 ? 'Treffer' : 'Treffer' }}
                    für „{{ $anfrage }}"
                </h2>

                {{-- <ol> und nicht <ul>: Die Reihenfolge trägt Bedeutung, der
                     beste Treffer steht oben. Vorlesehilfen sagen die Position
                     mit an („3 von 8") — eine echte Orientierungshilfe. --}}
                {{-- data-treffer: Die Brotkrumen oben sind ebenfalls eine <ol>.
                     Ohne eine eigene Kennung greift jeder Test — und jedes
                     spätere Skript — zuerst auf sie zu. --}}
                <ol data-treffer class="flex flex-col gap-4">
                    @foreach ($treffer as $eintrag)
                        <li class="rounded-card border border-line bg-card px-5 py-4">
                            <a href="{{ $eintrag['url'] }}"
                               class="inline-block py-0.5 font-display text-lg text-green-deep underline">
                                {{ $eintrag['titel'] }}
                            </a>

                            @if ($eintrag['bereich'])
                                {{-- Woher der Treffer kommt. Ohne diese Zeile
                                     weiss niemand, ob ihn ein Klick auf eine
                                     Inhaltsseite, ins Glossar oder zu einer
                                     Gruppe führt. --}}
                                <p class="mt-1 text-sm text-ink-soft">{{ $eintrag['bereich'] }}</p>
                            @endif

                            @if ($eintrag['ausschnitt'])
                                <p class="mt-2 leading-relaxed text-ink-soft">{{ $eintrag['ausschnitt'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>

                <p class="mt-6 max-w-prose text-sm text-ink-soft">
                    Nicht dabei, was du gesucht hast?
                    <a href="/anfragen" class="text-green-deep underline">Frag uns direkt</a> —
                    auf Wunsch anonym.
                </p>
            @endif
        </div>
    </section>

    {{-- Die Notfallnummern stehen auf jeder Seite im Fuss; hier bekommen sie ein
         Sprungziel, damit der Krisenhinweis oben darauf zeigen kann. --}}
    <div id="hilfe-nummern" class="scroll-mt-24"></div>

@endsection
