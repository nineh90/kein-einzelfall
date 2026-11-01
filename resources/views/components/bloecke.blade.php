@props([
    // Ergebnis von PageBlock::abschnitte()
    'abschnitte' => [],
    // artikel = Inhaltsseiten, nebeneinander = Startseite (kurze Abschnitte als Spalten)
    'art' => 'artikel',
    // Punkte für „Auf dieser Seite“; bekommt nur der erste Artikel der Seite
    'verzeichnis' => [],
    // KEV-294: „Auf dieser Seite“ rechts neben dem Text, der das wünscht
    // (Schalter am Textbaustein). Punkte wie bei $verzeichnis.
    'verzeichnisDaneben' => [],
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

    @if (count($bloecke) === 1 && isset($abschnitt['daneben']))
        <x-blocks.text-mit-dokumenten :text="$bloecke[0]" :dokumente="$abschnitt['daneben']" :auf="$abschnitt['flaeche']" />

    @elseif (count($bloecke) === 1 && $verzeichnisDaneben && $bloecke[0]->typ === 'text'
             && ! empty($bloecke[0]->data['verzeichnis_daneben']))
        <x-blocks.text-mit-verzeichnis :text="$bloecke[0]" :punkte="$verzeichnisDaneben" :auf="$abschnitt['flaeche']" />

    @elseif (count($bloecke) === 1)
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
