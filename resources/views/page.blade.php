@extends('layouts.app')

@section('title', $page->titel)

{{-- Der gepflegte meta_title schlaegt den Seitentitel. Ohne diese Zeile wuerde
     das Feld im Panel nichts bewirken. --}}
@section('vollertitel', $page->seiteTitel())

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
    use App\Models\PageBlock;
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

    // Sprungmarken für lange Seiten.
    //
    // Nur Textbausteine: Sie sind die einzigen, die ihre Überschrift mit einem
    // Sprungziel versehen. Ein Einstiegskarten-Baustein mit Überschrift stand
    // sonst zwar im Verzeichnis, sprang aber nirgendwohin — und das fällt
    // ausgerechnet dem auf, der das Verzeichnis benutzt, weil er nicht scrollen
    // kann oder will.
    //
    // Dazu der Spendenblock: Er trägt mit id="spenden" ein festes Sprungziel.
    $sprungpunkte = $bloecke
        ->map(fn ($b) => match (true) {
            $b->typ === 'text' && $b->anker() && ($b->data['titel'] ?? null)
                => ['anker' => $b->anker(), 'titel' => $b->data['titel']],
            $b->typ === 'donation_options'
                => ['anker' => 'spenden', 'titel' => $b->data['titel'] ?? 'Jetzt spenden'],
            default => null,
        })
        ->filter()
        ->values()
        ->all();

    // Flächenwechsel von Abschnitt zu Abschnitt. Ohne ihn laufen zehn
    // Abschnitte optisch ununterscheidbar ineinander; der Wechsel gibt der
    // Seite Rhythmus und macht Abschnittsgrenzen sichtbar. Der Seitenkopf ist
    // eine Karte, also beginnt der Inhalt hell — und was nach dem letzten
    // Baustein kommt, hebt sich ebenfalls von ihm ab.
    //
    // Aufeinanderfolgende Textbausteine bilden dabei einen Abschnitt (ein
    // Artikel, siehe PageBlock::abschnitte), und die Fläche wechselt von
    // Abschnitt zu Abschnitt.
    $abschnitte = PageBlock::abschnitte($bloecke, davor: 'card');
    $zuletzt = $abschnitte ? end($abschnitte)['flaeche'] : 'card';

    // Der Seitenkopf steht ohne eigenes Band auf der Fläche des ersten
    // Abschnitts (seit 23.09.2026).
    $ersteFlaeche = $abschnitte[0]['flaeche'] ?? 'cream';

    // Steht ein Artikel mit Seitenleiste auf der Seite, trägt die ab „lg“ das
    // Verzeichnis. Der Kasten oben wäre dort doppelt.
    $mitSeitenleiste = count($sprungpunkte) >= 2
        && collect($abschnitte)->contains(fn ($a) => count($a['bloecke']) > 1);

    $geschwister = $kontext->geschwister();
    $weiterlesenAuf = PageBlock::gegenflaeche($zuletzt);
    $kontaktAuf = PageBlock::gegenflaeche(count($geschwister) > 0 ? $weiterlesenAuf : $zuletzt);
@endphp

@section('content')

    {{-- Steht ganz oben und nicht am Seitenende: Wer die Sprache nicht
         liest, soll es erfahren, bevor er zu lesen anfängt. --}}
    <x-layout.sprachrueckfall :quelle="$ersatzsprache ?? null" />

    {{-- Ebenfalls oben: Wer Leichte Sprache braucht, soll den Hinweis finden,
         bevor er anfaengt zu lesen. --}}
    <x-layout.fassungswechsel :page="$page" />

    {{-- Und noch davor im Rang: Wenn der Text dieser Seite von uns stammt und
         der Verein ihn noch nicht freigegeben hat, gehört das vor die erste
         Zeile — nicht in eine Fussnote. --}}
    <x-layout.entwurfsvermerk :page="$page" />

    <x-layout.seitenkopf
        :titel="$page->titel"
        :bereich="$kontext->bereichName()"
        :krumen="$kontext->brotkrumen($page->titel)"
        :lead="$lead"
        :auf="$ersteFlaeche" />

    @if (count($sprungpunkte) >= 4)
        <div @class(['px-4 md:px-8 pt-8 lg:px-10', 'lg:hidden' => $mitSeitenleiste, 'bg-card' => $ersteFlaeche === 'card'])>
            <div class="mx-auto max-w-6xl">
                <div class="max-w-prose">
                    <x-ui.sprungmarken :punkte="$sprungpunkte" />
                </div>
            </div>
        </div>
    @endif

    <x-bloecke :abschnitte="$abschnitte" art="artikel"
               :verzeichnis="$mitSeitenleiste ? $sprungpunkte : []" />

    <x-layout.weiterlesen
        :seiten="$geschwister"
        :auf="$weiterlesenAuf"
        :bereich="$kontext->istBereichsUebersicht() ? $page->titel : $kontext->bereichName()" />

    {{-- Gemeinsamer Abschluss: Auf jeder Unterseite soll der Weg zu uns
         genauso nah sein wie auf der Startseite. Ausgenommen sind Rechtstexte —
         unter einer Datenschutzerklärung wirkt eine Gesprächseinladung
         deplatziert. --}}
    @unless ($kontext->istRechtstext())
        <x-blocks.contact-close
            :auf="$kontaktAuf"
            titel="Fragen zu diesem Thema?"
            text="Du wünschst einen persönlichen Austausch in Bezug auf das Soziale Entschädigungsrecht (OEG/SGB XIV), den Schwerbehindertenausweis und/oder den Pflegegrad, oder hast Fragen zu anderen Hilfesystemen, oder möchtest uns etwas mitteilen?"
            :ctas="[
                ['label' => 'Anfrage stellen', 'url' => '/anfragen', 'variant' => 'primary'],
                ['label' => 'Kontakt', 'url' => '/kontakt', 'variant' => 'ghost'],
            ]" />
    @endunless

@endsection
