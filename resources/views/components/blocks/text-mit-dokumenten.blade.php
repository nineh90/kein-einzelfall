@props([
    'text',          // App\Models\PageBlock, Typ text
    'dokumente',     // App\Models\PageBlock, Typ download_list
    'auf' => 'cream',
])

{{--
    Ein Textabschnitt mit den Dokumenten rechts daneben (KEV-226, Taddis
    Wunsch auf /selbsthilfegruppen: Teilnahmevereinbarung und Gruppenregeln
    gleich neben der Einleitung, die darauf verweist).

    Der Text behält seine Lesebreite, die Liste nimmt den Platz, der daneben
    sonst leer bliebe. Unter lg steht sie unter dem Text, in derselben
    Reihenfolge wie im Panel. Eingeschaltet wird das am Dokumente-Baustein
    („Neben den Textabschnitt darüber stellen“), siehe PageBlock::abschnitte().
    Genauso der Termin-Kalender (KEV-224) neben der Einleitung von
    /gruppen-und-veranstaltungen.
--}}
@php
    $d = $text->data ?? [];
    $dok = $dokumente->data ?? [];
@endphp

<section @class([
    'px-4 md:px-8 py-8 lg:px-10 lg:py-12',
    'bg-card border-y border-line' => $auf === 'card',
])>
    <div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[minmax(0,65ch)_minmax(0,1fr)] lg:gap-14">
        <x-blocks.text-inhalt class="max-w-prose"
            :eyebrow="$d['eyebrow'] ?? null"
            :titel="$d['titel'] ?? null"
            :cta="knoepfe([$d['cta'] ?? null])[0] ?? null"
            :anker="$text->anker()"
            :absaetze="$d['absaetze'] ?? []"
            :hand="$d['hand'] ?? null"
            :ab_absatz="$text->textAngaben()['ab_absatz']"
            :mittig="(bool) ($d['mittig'] ?? false)" />

        {{-- Seit KEV-224 auch der Termin-Kalender (PageBlock::DANEBEN). --}}
        @if ($dokumente->typ === 'kalender')
            <x-blocks.kalender :rahmen="false" :auf="$auf" :titel="$dok['titel'] ?? null" />
        @elseif ($dokumente->typ === 'seitenkarten')
            {{-- KEV-174: Selbsthilfe- und Arbeitsgruppen neben dem Vereinstext. --}}
            {{-- KEV-242: auf /wissen Taddis Kategorien als Knöpfe. --}}
            <x-blocks.seitenkarten :rahmen="false" :auf="$auf" :titel="$dok['titel'] ?? null"
                :seiten="$dok['seiten'] ?? []" :darstellung="$dok['darstellung'] ?? 'karten'" />
        @else
            <x-blocks.download-list :rahmen="false" :auf="$auf"
                :titel="$dok['titel'] ?? null"
                :dokumente="$dok['dokumente'] ?? []" />
        @endif
    </div>
</section>
