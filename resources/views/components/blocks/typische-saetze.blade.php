@props([
    'titel' => null,
    'absaetze' => [],
    'formulierungen' => [],  // [['satz' =>, 'bedeutung' =>], ...]
    'anker' => null,
    'auf' => 'cream',        // cream | card
])

{{--
    Typische Sätze – einfach übersetzt (KEV-262).

    Taddis Idee: Oft ist nicht ein einzelnes Wort das Problem, sondern ein
    ganzer Satz aus dem Bescheid. Statt einer eigenen Seite stehen die Sätze
    unter dem Glossar ihrer Kategorie (Behörden & Bescheide, Gericht &
    Verfahren …).

    - Satz und Übersetzung sind ein Paar, deshalb <dl> wie im Glossar.
    - Karten in zwei Spalten statt Wörterbuchzeilen: Die Sätze sind länger als
      Begriffe, und „Einfach übersetzt“ soll sich vom Satz deutlich abheben.
    - Der Einleitungstext klappt nicht ein (ab_absatz), er ist der Hinweis,
      dass das keine Rechtsberatung ist.
    - Der Satz bleibt in jeder Sprache deutsch, so steht er im Brief; nur
      die Übersetzung wird übersetzt. Vorlesehilfen bekommen lang="de".
--}}
@php
    $deutsch = \App\Models\Language::aktuell()->code === 'de';
@endphp

<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div class="mx-auto max-w-6xl">
        <x-blocks.text-inhalt class="max-w-prose"
            :titel="$titel" :anker="$anker" :absaetze="$absaetze" :ab_absatz="99" />

        @if (count($formulierungen) > 0)
            <dl class="mt-8 grid gap-4 md:grid-cols-2">
                @foreach ($formulierungen as $eintrag)
                    <div @class([
                        'rounded-card border border-line p-5',
                        'bg-cream' => $auf === 'card',
                        'bg-card' => $auf !== 'card',
                    ])>
                        <dt @unless ($deutsch) lang="de" @endunless class="hyphens-auto font-display font-semibold text-ink">„{{ $eintrag['satz'] ?? '' }}“</dt>
                        <dd class="mt-2 hyphens-auto text-[0.9375rem] leading-relaxed text-ink-soft">
                            <span class="font-medium text-green-deep">{{ __('Einfach übersetzt:') }}</span>
                            {{ $eintrag['bedeutung'] ?? '' }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</section>
