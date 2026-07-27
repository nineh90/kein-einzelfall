@extends('layouts.app')

@section('title', $page->titel)

{{-- Nie null übergeben: @section mit null als Inhalt öffnet einen
     Ausgabepuffer, der mangels @endsection nie geschlossen wird. Seiten ohne
     gepflegte Beschreibung erhalten deshalb den Seitentitel als Notbehelf —
     besser als ein leeres description-Feld im Quelltext. --}}
@section('description', $page->meta_description ?: $page->titel.' — KE!N EINZELFALL e.V.')

@if ($page->noindex)
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush
@endif

@php
    use App\Support\Seitenkontext;

    $kontext = Seitenkontext::fuer($page->slug);

    $bloecke = $page->blocks;

    // Der erste Absatz wandert als Vorspann in den Seitenkopf — aber nur, wenn
    // der erste Baustein ein Text ohne eigene Überschrift ist. Sonst risse man
    // einen Abschnitt auseinander.
    $lead = null;
    $ersterBlock = $bloecke->first();

    if ($ersterBlock
        && $ersterBlock->typ === 'text'
        && blank($ersterBlock->data['titel'] ?? null)
        && filled($ersterBlock->data['absaetze'] ?? [])) {

        $absaetze = $ersterBlock->data['absaetze'];
        $lead = array_shift($absaetze);

        if ($absaetze === []) {
            // Der Baustein bestand nur aus diesem einen Absatz
            $bloecke = $bloecke->slice(1);
        } else {
            $gekuerzt = clone $ersterBlock;
            $gekuerzt->data = array_merge($ersterBlock->data, ['absaetze' => $absaetze]);
            $bloecke = $bloecke->slice(1)->prepend($gekuerzt);
        }
    }

    // Sprungmarken für lange Seiten
    $sprungpunkte = $bloecke
        ->filter(fn ($b) => $b->anker() && ($b->data['titel'] ?? null))
        ->map(fn ($b) => ['anker' => $b->anker(), 'titel' => $b->data['titel']])
        ->values()
        ->all();
@endphp

@section('content')

    <x-layout.seitenkopf
        :titel="$page->titel"
        :bereich="$kontext->bereichName()"
        :krumen="$kontext->brotkrumen($page->titel)"
        :lead="$lead" />

    @if (count($sprungpunkte) >= 4)
        <div class="px-4 pt-8 lg:px-10">
            <div class="mx-auto max-w-6xl">
                <div class="max-w-prose">
                    <x-ui.sprungmarken :punkte="$sprungpunkte" />
                </div>
            </div>
        </div>
    @endif

    @foreach ($bloecke as $block)
        {{-- Textbausteine wechseln die Fläche. Ohne das laufen zehn Abschnitte
             optisch ununterscheidbar ineinander; der Wechsel gibt der Seite
             Rhythmus und macht Abschnittsgrenzen sichtbar. --}}
        <x-block :block="$block" :flaeche="$loop->index % 2 === 1 ? 'card' : 'cream'" />
    @endforeach

    <x-layout.weiterlesen
        :seiten="$kontext->geschwister()"
        :bereich="$kontext->istBereichsUebersicht() ? $page->titel : $kontext->bereichName()" />

    {{-- Gemeinsamer Abschluss: Auf jeder Unterseite soll der Weg zu uns
         genauso nah sein wie auf der Startseite. Ausgenommen sind Rechtstexte —
         unter einer Datenschutzerklärung wirkt eine Gesprächseinladung
         deplatziert. --}}
    @unless ($kontext->istRechtstext())
        <x-blocks.contact-close
            titel="Fragen zu diesem Thema?"
            text="Du wünschst einen persönlichen Austausch in Bezug auf das Soziale Entschädigungsrecht (OEG/SGB XIV), den Schwerbehindertenausweis und/oder den Pflegegrad, oder hast Fragen zu anderen Hilfesystemen, oder möchtest uns etwas mitteilen?"
            :ctas="[
                ['label' => 'Anfrage stellen', 'url' => '/anfragen', 'variant' => 'primary'],
                ['label' => 'Kontakt', 'url' => '/kontakt', 'variant' => 'ghost'],
            ]" />
    @endunless

@endsection
