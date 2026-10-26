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
    use App\Support\Bereichswarnung;
    use App\Support\Seitenkontext;

    $kontext = Seitenkontext::fuer($page->slug);

    // Eigene Triggerwarnung der Seite oder ihres Bereichs (KEV-156).
    $warnung = Bereichswarnung::fuer($page);

    // Bausteine, die nichts zeigen (etwa „Einträge mit Datum“ ohne Einträge,
    // KEV-129), gar nicht erst mitzählen: Sonst bekämen sie eine Fläche, und
    // zwei gleiche Flächen stünden direkt untereinander.
    $bloecke = $page->blocks->reject->zeigtNichts()->values();

    // Der erste Absatz wandert als Vorspann in den Seitenkopf — aber nur, wenn
    // der erste Baustein ein Text ohne eigene Überschrift ist. Sonst risse man
    // einen Abschnitt auseinander.
    $lead = null;
    $ersterBlock = $bloecke->first();

    // Mit Titelbild nicht: Auf dem Bild steht statt eines ganzen Absatzes
    // immer nur die kurze Unterzeile, damit alle Köpfe gleich gebaut sind.
    // Der Absatz bleibt dann, wo er ist, als Einstieg in den Inhalt.
    if (! $page->titelbild
        && $ersterBlock
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

    // Die Unterzeile auf dem Titelbild ist oft der erste Satz der Seite
    // („Raum für deine Geschichte …“). Dann stünde er direkt darunter gleich
    // noch einmal; im Inhalt fällt er deshalb weg.
    if ($page->titelbild && $page->untertitel && $ersterBlock?->typ === 'text') {
        $absaetze = $bloecke->first()->data['absaetze'] ?? [];

        if (trim($absaetze[0] ?? '') === trim($page->untertitel)) {
            $gekuerzt = clone $bloecke->first();
            $gekuerzt->data = array_merge($gekuerzt->data, ['absaetze' => array_slice($absaetze, 1)]);
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
    //
    // Und Dokumentenlisten mit Überschrift (KEV-193, Taddi auf /satzung: „kann
    // da noch Satzung downloaden hin“). Ihre Überschrift trägt das Ziel
    // „dl-…“. Nicht die neben einem Text: Die stehen im Abschnitt darüber.
    $sprungpunkte = $bloecke
        ->map(fn ($b) => match (true) {
            $b->typ === 'text' && $b->anker() && ($b->data['titel'] ?? null)
                => ['anker' => $b->anker(), 'titel' => $b->data['titel']],
            $b->typ === 'donation_options'
                => ['anker' => 'spenden', 'titel' => $b->data['titel'] ?? 'Jetzt spenden'],
            $b->typ === 'download_list' && ($b->data['titel'] ?? null) && empty($b->data['neben_text'])
                => ['anker' => 'dl-'.\Illuminate\Support\Str::slug($b->data['titel']), 'titel' => $b->data['titel']],
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

    // Zeigt die Seite ihre Unterseiten schon als Kacheln (KEV-156), wäre die
    // Liste am Ende dieselben Links noch einmal.
    $geschwister = $page->blocks->contains('typ', 'unterseiten') ? [] : $kontext->geschwister();
    $weiterlesenAuf = PageBlock::gegenflaeche($zuletzt);
    $kontaktAuf = PageBlock::gegenflaeche(count($geschwister) > 0 ? $weiterlesenAuf : $zuletzt);
@endphp

@if ($warnung)
    {{-- Vor dem ersten Zeichnen verstecken, wenn in diesem Besuch schon
         bestätigt, wie beim allgemeinen Hinweis im Layout. Nur verstecken,
         nie zeigen: Sichtbar ist die Warnung schon aus dem Server-HTML. --}}
    @push('head')
        <script @isset($cspNonce) nonce="{{ $cspNonce }}" @endisset>
        try {
            if ((JSON.parse(sessionStorage.getItem('ke.bereichswarnung')) || []).indexOf(@json($warnung->slug)) !== -1) {
                document.documentElement.classList.add('ke-bereichswarnung-aus');
            }
        } catch (e) { /* Im Zweifel warnen. */ }
        </script>
    @endpush

    @section('vorschaltung')
        <x-layout.bereichswarnung :seite="$warnung" />
    @endsection
@endif

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
        :untertitel="$page->titelbild ? $page->untertitel : null"
        :bild="$page->titelbild"
        :bild_alt="$page->titelbild_alt"
        :name_gross="$kontext->istBereichsUebersicht() && $kontext->istVereinsbereich()"
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
        :spalten="$kontext->istBereichsUebersicht()['spalten'] ?? 3"
        :spaltenweise="isset(($kontext->istBereichsUebersicht() ?? $kontext->bereich())['kacheln'])"
        :auf="$weiterlesenAuf"
        :titel="$kontext->istVereinsbereich() ? __('rahmen.weiterlesen.mehr_ueber_verein') : null"
        :bereich="$kontext->istBereichsUebersicht() ? $page->titel : $kontext->bereichName()" />

    {{-- Gemeinsamer Abschluss: Auf jeder Unterseite soll der Weg zu uns
         genauso nah sein wie auf der Startseite. Ausgenommen sind Rechtstexte —
         unter einer Datenschutzerklärung wirkt eine Gesprächseinladung
         deplatziert.

         Überschrift und Text seit KEV-71 von Taddi (vorher „Fragen zu diesem
         Thema?“, ohne die Projektideen), seit KEV-177 ihr neuer Text mit
         Links auf die Gruppen, Projekte und „Raum für Ungesagtes“. Jede
         Zeile wird ein Absatz. --}}
    {{-- Und nicht unter dem Kontaktformular selbst: Dort zeigte „Anfrage
         stellen“ auf dieselbe Seite. --}}
    @unless ($kontext->istRechtstext() || $page->blocks->contains('typ', 'contact_form'))
        @php
            // Ein Übersetzungsschlüssel, die Absätze durch Zeilenumbrüche
            // getrennt (lang/en.json).
            $kontaktText = __(implode("\n", [
                'Vielleicht hast Du eine Idee für eine [Arbeitsgruppe](/arbeitsgruppen), [Selbsthilfegruppe](/selbsthilfegruppen) oder ein [Projekt](/projekte). Vielleicht möchtest Du etwas zu [Ungesagtes](/raum-fuer-ungesagtes) beitragen.',
                'Vielleicht möchtest Du Dich auch einfach mit Deinem Wissen oder Deiner Erfahrung, Deiner Kreativität oder dem Wunsch etwas zu verändern einbringen.',
                'Du musst keinen fertigen Plan mitbringen. Manchmal beginnt etwas einfach damit, dass jemand sagt: Ich habe da eine Idee. Ich möchte etwas beitragen. Ich brauche Unterstützung. Oder: Ich möchte einfach dabei sein.',
                'Auch wenn Du Fragen zum Sozialen Entschädigungsrecht (OEG/SGB XIV), zum Schwerbehindertenausweis, zum Pflegegrad oder zu anderen Hilfesystemen hast oder Dir einen persönlichen Austausch wünschst, kannst Du Dich gern an uns wenden.',
            ]));
        @endphp
        <x-blocks.contact-close
            :auf="$kontaktAuf"
            :titel="__('Du möchtest uns etwas mitteilen, Dich einbringen oder einfach Teil von KE!N EINZELFALL sein?')"
            :text="$kontaktText"
            :ctas="[
                ['label' => __('Anfrage stellen'), 'url' => \App\Models\Language::aktuell()->pfad('/anfragen'), 'variant' => 'primary'],
                ['label' => __('Kontakt'), 'url' => \App\Models\Language::aktuell()->pfad('/kontakt'), 'variant' => 'ghost'],
            ]" />
    @endunless

@endsection
