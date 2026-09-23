@props([
    // Ergebnis von PageBlock::abschnitte()
    'abschnitte' => [],
    // artikel = Inhaltsseiten, nebeneinander = Startseite (kurze Abschnitte als Spalten)
    'art' => 'artikel',
    // Punkte für „Auf dieser Seite“; bekommt nur der erste Artikel der Seite
    'verzeichnis' => [],
])

{{--
    Die Bausteine einer Seite, Abschnitt für Abschnitt.

    Ein einzelner Baustein steht wie gewohnt (x-block). Mehrere Textbausteine
    hintereinander bilden einen Abschnitt: auf der Startseite als Spalten,
    wenn sie kurz sind (höchstens drei, je höchstens drei Absätze), sonst
    als ein durchgehender Artikel.
--}}
@php $verzeichnisVergeben = false; @endphp

@foreach ($abschnitte as $abschnitt)
    @php $bloecke = $abschnitt['bloecke']; @endphp

    @if (count($bloecke) === 1)
        <x-block :block="$bloecke[0]" :flaeche="$abschnitt['flaeche']" />

    @elseif ($art === 'nebeneinander' && count($bloecke) <= 3
             && collect($bloecke)->every(fn ($b) => $b->istKurzerText()))
        <x-blocks.nebeneinander :bloecke="$bloecke" :auf="$abschnitt['flaeche']" />

    @else
        <x-blocks.artikel :bloecke="$bloecke" :auf="$abschnitt['flaeche']"
                          :verzeichnis="$verzeichnisVergeben ? [] : $verzeichnis" />
        @php $verzeichnisVergeben = (bool) $verzeichnis; @endphp
    @endif
@endforeach
