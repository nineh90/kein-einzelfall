@props([
    'text',          // App\Models\PageBlock, Typ text
    'punkte' => [],  // Sprungpunkte (anker, titel), siehe page.blade.php
    'auf' => 'cream',
])

{{--
    Ein Textabschnitt mit „Auf dieser Seite“ rechts daneben (KEV-294, Taddi
    auf /ueber-uns-vorstand-und-team: der Kasten rechts, die Einleitung
    links). Wie die Dokumente neben einem Text (text-mit-dokumenten): Der
    Text behält seine Lesebreite, der Kasten nimmt den Platz, der daneben
    sonst leer bliebe. Unter lg steht er unter dem Text.

    Eingeschaltet am Textbaustein („Auf dieser Seite“ rechts daneben). Der
    Kasten oben über dem Inhalt entfällt dann.
--}}
@php $t = $text->textAngaben(); @endphp

<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[minmax(0,65ch)_minmax(0,1fr)] lg:gap-14">
        <x-blocks.text-inhalt class="max-w-prose"
            :eyebrow="$t['eyebrow']" :titel="$t['titel']" :anker="$t['anker']"
            :absaetze="$t['absaetze']" :hand="$t['hand']" :cta="$t['cta']" :mittig="$t['mittig']"
            :ab_absatz="$t['ab_absatz']" />

        <div class="lg:self-start lg:pt-2">
            <x-ui.sprungmarken :punkte="$punkte" :mindestens="1" />
        </div>
    </div>
</section>
