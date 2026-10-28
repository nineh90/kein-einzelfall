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
        Die Startseite ist der Anfang des Weges, nicht eine Station darin. Bis
        KEV-121 brachte sie ihren Kontaktabschluss als eigenen Baustein mit;
        der Verein wollte ihn weg haben.

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

{{-- Strukturierte Daten zum Verein (SEO-Prüfung 28.10.2026): Name, Logo,
     Adresse und Kontakt wie im Fuß jeder Seite, dazu die Profile in sozialen
     Netzwerken aus config/navigation.php. Google zeigt daraus u. a. das
     Logo im Suchergebnis. --}}
@push('head')
    @php
        $verein = [
            '@context' => 'https://schema.org',
            '@type' => 'NGO',
            'name' => 'KE!N EINZELFALL e.V.',
            'alternateName' => 'Kein Einzelfall e.V.',
            'url' => url('/'),
            'logo' => url('/img/logo-gross.webp'),
            'email' => 'kontakt@kein-einzelfall.de',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Schiffbeker Höhe 30',
                'postalCode' => '22119',
                'addressLocality' => 'Hamburg',
                'addressCountry' => 'DE',
            ],
            'sameAs' => array_column(config('navigation.footer.social', []), 'url'),
        ];
    @endphp
    <script type="application/ld+json" @isset($cspNonce) nonce="{{ $cspNonce }}" @endisset>
        {!! json_encode($verein, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')

    {{-- Steht ganz oben und nicht am Seitenende: Wer die Sprache nicht
         liest, soll es erfahren, bevor er zu lesen anfängt. --}}
    <x-layout.sprachrueckfall :quelle="$ersatzsprache ?? null" />

    {{-- Ebenfalls oben: Wer Leichte Sprache braucht, soll den Hinweis finden,
         bevor er anfängt zu lesen. --}}
    <x-layout.fassungswechsel :page="$page" />

    {{-- Maschinell übersetzt? Dann steht das vor dem Aufmacher (08.10.2026). --}}
    <x-layout.entwurfsvermerk :page="$page" />

    {{-- Flächenwechsel von Abschnitt zu Abschnitt, siehe PageBlock::FLAECHEN.
         Über dem ersten Baustein steht nur die helle Kopfzeile. --}}
    {{-- Kurze Textabschnitte hintereinander stehen hier als Spalten
         nebeneinander (x-blocks.nebeneinander). --}}
    {{-- Leere Bausteine fallen vorher raus, wie in page.blade.php. --}}
    <x-bloecke :abschnitte="\App\Models\PageBlock::abschnitte($page->blocks->reject->zeigtNichts()->values(), davor: 'cream')"
               art="nebeneinander" />

@endsection
