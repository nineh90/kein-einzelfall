@extends('layouts.app')

{{--
    Startseite.

    Sie ist ein gewöhnlicher Datensatz in `pages` — Überschrift, Texte, Karten
    und Knöpfe pflegt der Verein im Panel. Diese Datei entscheidet nur noch über
    den Rahmen.

    Zwei Unterschiede zu `page.blade.php`, und beide sind der Grund, warum es
    diese Ansicht überhaupt noch gibt:

      - Kein Seitenkopf. Die Überschrift der Startseite steht im Aufmacher;
        ein zusätzlicher Seitenkopf ergäbe eine zweite <h1>.
      - Keine Brotkrumen, kein „Weiterlesen“, kein angehängter Kontaktschluss.
        Die Startseite ist der Anfang des Weges, nicht eine Station darin — und
        ihren Kontaktabschluss bringt sie als eigenen Baustein mit.

    Die Texte stammen wörtlich vom Verein (Bestand von kein-einzelfall.de,
    Stand 26.07.2026) und stehen im `StartseiteSeeder`.
--}}

@section('title', $page->titel)

{{-- Der gepflegte meta_title schlägt den Seitentitel — wie bei jeder Seite. --}}
@section('vollertitel', $page->seiteTitel())

{{-- Nie null übergeben: @section mit null als Inhalt öffnet einen Ausgabepuffer,
     der mangels @endsection nie geschlossen wird. --}}
@section('description', $page->meta_description ?: 'Austausch- und Informationsplattform für Opfer und Mit-Opfer von Straftaten, Angehörige und Fachpersonen.')

@if ($page->noindex)
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush
@endif

@section('content')

    {{-- Steht ganz oben und nicht am Seitenende: Wer die Sprache nicht
         liest, soll es erfahren, bevor er zu lesen anfängt. --}}
    <x-layout.sprachrueckfall :quelle="$ersatzsprache ?? null" />

    {{-- Ebenfalls oben: Wer Leichte Sprache braucht, soll den Hinweis finden,
         bevor er anfängt zu lesen. --}}
    <x-layout.fassungswechsel :page="$page" />

    @foreach ($page->blocks as $block)
        <x-block :block="$block" :flaeche="$loop->index % 2 === 1 ? 'card' : 'cream'" />
    @endforeach

@endsection
