@props([
    'titel',
    'bereich' => null,
    'krumen' => [],
    'lead' => null,
    // Fläche des ersten Abschnitts darunter: Der Kopf steht auf ihr.
    'auf' => 'cream',      // cream | card
])

{{--
    Seitenkopf.

    Ohne ihn beginnt jede Unterseite mit einer nackten Überschrift auf leerer
    Fläche — die Startseite hat einen Aufmacher, die Unterseiten wirkten dagegen
    unfertig. Der Kopf gibt ihnen denselben ruhigen Einstieg, ohne dass dafür
    Inhalte erfunden werden müssten: Bereich und Brotkrumen stammen aus der
    Navigation, der Vorspann ist der erste Absatz der Seite.
--}}
{{--
    Seit dem 23.09.2026 schlank und ohne eigenes Band (Abnahme): Vorher war
    der Kopf eine eigene Karte mit Linie darunter, und das Größte darin war
    ein einzelnes Wort wie „Spenden“ in 40 px. Jetzt stehen Brotkrumen und
    Titel auf der Fläche des ersten Abschnitts, ohne Trennlinie, und der
    Inhalt beginnt direkt darunter.

    Die H1 bleibt: Vorlesehilfen springen auf sie, um zu erfahren, wo man ist,
    und sie ist das Thema der Seite für Suchmaschinen. Sie ist nur kleiner.

    data-anschliessend: Der Kopf gehört zum Abschnitt darunter und ist kein
    eigener Abschnitt im Flächenwechsel (SeitengestaltungTest).
--}}
<header data-anschliessend @class([
    'px-4 md:px-8 pt-6 lg:px-10 lg:pt-10',
    'bg-card' => $auf === 'card',
])>
    <div class="mx-auto max-w-6xl">
        <x-ui.brotkrumen :krumen="$krumen" />

        @if ($bereich)
            <x-ui.eyebrow class="mb-2">{{ $bereich }}</x-ui.eyebrow>
        @endif

        {{-- Kleiner als früher (40 px), aber immer eine Stufe über den
             Abschnittsüberschriften (24/30 px): Eine H1 in derselben Größe
             wie die H2 darunter kehrt die Rangordnung optisch um. --}}
        <h1 class="max-w-3xl font-display text-[1.75rem] font-medium leading-tight text-ink lg:text-[2.25rem]">
            {{ $titel }}
        </h1>

        @if ($lead)
            {{-- Erster Absatz größer gesetzt: gibt der Seite einen Einstieg und
                 hilft beim Einordnen, bevor der Fließtext beginnt. --}}
            <p class="mt-4 max-w-prose text-lg leading-relaxed text-ink-soft">
                {{ $lead }}
            </p>
        @endif
    </div>
</header>
