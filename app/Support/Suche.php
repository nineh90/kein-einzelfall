<?php

namespace App\Support;

use App\Models\Frist;
use App\Models\GlossaryTerm;
use App\Models\Group;
use App\Models\OutreachItem;
use App\Models\Page;
use Illuminate\Support\Str;

/**
 * Die Suche der Website (KEV-23).
 *
 * ── Warum sie ohne Sprachmodell funktioniert ───────────────────────────────
 *
 * Ein Sprachmodell kann Laiensprache in Fachbegriffe übersetzen, und genau das
 * braucht diese Zielgruppe. Es darf aber nicht die Grundlage sein: Wer hier
 * sucht, sitzt womöglich vor einer Frist. Eine Suche, die ausfällt, weil eine
 * fremde Schnittstelle langsam ist oder ein Guthaben leer, ist für diesen
 * Menschen wertlos — und er erfährt nie, warum.
 *
 * Diese Klasse sucht deshalb vollständig lokal und deterministisch. Das
 * Sprachmodell kommt als zweite Schicht darüber und verbessert die Reihenfolge,
 * wenn es erreichbar ist. Fällt es aus, merkt niemand etwas ausser dass die
 * Treffer etwas gröber sortiert sind.
 *
 * ── Warum eigene Bewertung und kein FULLTEXT ───────────────────────────────
 *
 * Der Bestand ist klein (rund 40 Seiten, 160 Bausteine, dazu Glossar und
 * Gruppen) — alles passt in den Arbeitsspeicher, und eine Suche über den
 * ganzen Bestand dauert Millisekunden. Dafür bekommen wir volle Kontrolle
 * über die Bewertung, deutsche Wortzusammensetzungen und die Synonymliste
 * unten. MySQL-FULLTEXT könnte nichts davon und wäre bei deutschen Komposita
 * ("Schwerbehindertenausweis" enthält "Ausweis") schlechter.
 */
class Suche
{
    /**
     * Was Menschen eintippen → wonach wir tatsächlich suchen.
     *
     * Das ist das Herzstück für diese Zielgruppe. Wer zum ersten Mal mit dem
     * Sozialrecht zu tun hat, kennt die Wörter nicht, die auf unseren Seiten
     * stehen. Er schreibt „die glauben mir nicht", nicht „Beweislast", und
     * „Brief vom Amt", nicht „Bescheid".
     *
     * Ohne diese Liste findet eine Volltextsuche für genau die Menschen nichts,
     * für die die Seite gemacht ist. Sie ist bewusst hier im Code und nicht in
     * der Datenbank: Sie gehört zur Suchlogik, nicht zum Inhalt, und sie soll
     * mitwandern, wenn jemand das Projekt woanders aufsetzt.
     *
     * @var array<string, list<string>>
     */
    public const SYNONYME = [
        // Behörden und Post
        'amt' => ['versorgungsamt', 'behörde', 'antrag'],
        'brief' => ['bescheid', 'schreiben', 'post'],
        'bescheid' => ['widerspruch', 'antrag'],
        'formular' => ['antrag', 'anträge', 'formulare'],
        'papierkram' => ['antrag', 'bürokratie', 'formulare'],
        'behördendschungel' => ['bürokratie', 'antrag'],

        // Ablehnung und Gegenwehr
        'abgelehnt' => ['widerspruch', 'ablehnung', 'bescheid'],
        'ablehnung' => ['widerspruch', 'bescheid'],
        'widerspruch' => ['frist', 'bescheid'],
        'klage' => ['widerspruch', 'sozialgericht', 'gericht'],
        'gericht' => ['sozialgericht', 'klage', 'urteil'],
        'glauben' => ['beweis', 'glaubhaftmachung', 'glaubhaftigkeit', 'nachweis'],
        'beweisen' => ['beweis', 'nachweis', 'glaubhaftmachung'],

        // Geld und Leistungen
        'geld' => ['entschädigung', 'leistungen', 'rente'],
        'entschädigung' => ['sgb xiv', 'opferentschädigung', 'soziales entschädigungsrecht'],
        'rente' => ['erwerbsminderungsrente', 'erwerbsminderung'],
        'zahlung' => ['leistungen', 'entschädigung'],

        // Behinderung und Pflege
        'ausweis' => ['schwerbehindertenausweis', 'grad der behinderung', 'gdb'],
        'behinderung' => ['grad der behinderung', 'gdb', 'schwerbehindertenausweis'],
        'gdb' => ['grad der behinderung', 'schwerbehindertenausweis'],
        'merkzeichen' => ['schwerbehindertenausweis', 'grad der behinderung'],
        'pflege' => ['pflegegrad', 'pflegeversicherung'],
        'pflegestufe' => ['pflegegrad'],

        // Tat und Folgen
        'oeg' => ['opferentschädigungsgesetz', 'soziales entschädigungsrecht'],
        'gewalt' => ['gewalttat', 'entschädigung', 'opferentschädigung'],
        'missbrauch' => ['fsm', 'fonds sexueller missbrauch', 'gewalttat'],
        'trauma' => ['traumafolgestörungen', 'traumaambulanz', 'trauma'],
        'therapie' => ['traumaambulanz', 'traumafolgestörungen'],
        'ptbs' => ['traumafolgestörungen', 'trauma'],

        // Wer steckt dahinter
        'seid' => ['verein', 'vorstand', 'über uns'],
        'euch' => ['verein', 'kontakt'],
        'kontakt' => ['anfragen', 'kontakt', 'austausch'],
        'schreiben' => ['anfragen', 'kontakt'],
        'anrufen' => ['kontakt', 'anfragen'],
        'fragen' => ['anfragen', 'austausch'],

        // Austausch und Verein
        'gruppe' => ['selbsthilfegruppe', 'selbsthilfegruppen'],
        'treffen' => ['selbsthilfegruppen', 'veranstaltungen', 'termine'],
        'reden' => ['selbsthilfegruppen', 'austausch', 'anfragen'],
        'hilfe' => ['anfragen', 'selbsthilfegruppen', 'hilfesystem'],
        'mitmachen' => ['mitgliedschaft', 'arbeitsgruppen', 'mitglied'],
        'spende' => ['spenden', 'unterstützung'],
    ];

    /**
     * Wörter, bei denen wir aufhören zu suchen und Hilfe anbieten.
     *
     * Auch in ein Suchfeld schreiben Menschen, was sie sonst niemandem sagen.
     * Wer „ich will nicht mehr" eintippt, braucht keine Trefferliste, sondern
     * eine Nummer, unter der jemand abnimmt.
     *
     * Bewusst knapp gehalten und auf eindeutige Wendungen beschränkt: Ein
     * Fehlalarm ist hier zwar harmlos (es erscheinen Nummern, die Trefferliste
     * kommt trotzdem darunter), aber ein Kasten, der ständig aufpoppt, wird
     * bald übersehen.
     *
     * @var list<string>
     */
    public const KRISE = [
        'suizid', 'selbstmord', 'suizidgedanken', 'umbringen', 'nicht mehr leben',
        'will nicht mehr', 'kann nicht mehr', 'sterben', 'das leben nehmen',
        'selbstverletzung', 'ritzen', 'keinen ausweg', 'keinen sinn mehr',
    ];

    /** Wörter ohne Aussagekraft — sie würden jede Bewertung verwässern. */
    private const FUELLWOERTER = [
        'der', 'die', 'das', 'den', 'dem', 'des', 'ein', 'eine', 'einen', 'einem',
        'und', 'oder', 'aber', 'ist', 'sind', 'war', 'bin', 'habe', 'hat', 'haben',
        'ich', 'du', 'er', 'sie', 'es', 'wir', 'ihr', 'mir', 'mich', 'dir', 'dich',
        'was', 'wie', 'wo', 'wer', 'wann', 'warum', 'welche', 'welcher', 'welches',
        'für', 'von', 'mit', 'bei', 'aus', 'auf', 'in', 'im', 'zu', 'zum', 'zur',
        'nicht', 'kein', 'keine', 'noch', 'auch', 'nur', 'schon', 'mal', 'man',
    ];

    /** Höchstens so viele Treffer — mehr liest niemand. */
    private const HOECHSTENS = 12;

    /**
     * Höchstens so viele Glossareinträge unter den Treffern (KEV-153). Seit
     * dem Abkürzungsverzeichnis gibt es rund 200; „Krankenkasse“ trifft ein
     * Dutzend davon (AOK, BKK, DAK …) und drängte sonst jede Seite aus der
     * Liste. Für den Rest gibt es den Verweis ins Glossar mit derselben Suche.
     */
    private const HOECHSTENS_GLOSSAR = 5;

    /**
     * Rechtstexte werden nur über Titel und Beschreibung gefunden, nicht über
     * ihren Fliesstext (08.10.2026). Das Beispiel der Suchseite „die glauben
     * mir nicht“ lieferte sonst Impressum (Bild*nachweis*) und
     * Datenschutzerklärung als einzige Treffer.
     */
    private const NUR_TITEL = ['impressum', 'datenschutz'];

    /**
     * Begriffe, die aus der Synonymliste stammen und nicht von der suchenden
     * Person. Sie zählen nur am Wortanfang: „Nachweis“ soll „Nachweise“
     * finden, aber nicht „Bildnachweis“. Was jemand selbst eintippt, sucht
     * weiter auch mitten im Wort („antrag“ in „Rentenantrag“).
     *
     * @var array<string, true>
     */
    private array $nurWortanfang = [];

    /**
     * Die Anfrage in Stücken zum Vergleich mit Abkürzungen, auch Wörter unter
     * drei Zeichen: „AU“, „KK“ und „SG“ sind Abkürzungen, die genau so im
     * Bescheid stehen. Einzeln, zu zweit und zu dritt zusammengezogen, damit
     * „SGB XIV“ und „i. d. R.“ als ganze Abkürzung erkannt werden.
     *
     * @var array{genau: list<string>, klein: list<string>}
     */
    private array $abkuerzungen = ['genau' => [], 'klein' => []];

    /**
     * @return array{treffer: list<array<string, mixed>>, krise: bool, begriffe: list<string>, im_glossar: list<array{titel: string, url: string, anzahl: int}>}
     */
    public function suchen(string $anfrage, string $sprache = 'de'): array
    {
        $krise = $this->kriseErkannt($anfrage);
        $begriffe = $this->begriffe($anfrage);
        $this->abkuerzungen = $this->stuecke($anfrage);

        if ($begriffe === [] && $this->abkuerzungen['klein'] === []) {
            return ['treffer' => [], 'krise' => $krise, 'begriffe' => [], 'im_glossar' => []];
        }

        $glossar = $this->glossar($begriffe, $sprache);
        usort($glossar, fn ($a, $b) => $b['punkte'] <=> $a['punkte']);

        $treffer = array_merge(
            $this->seiten($begriffe, $sprache),
            array_slice($glossar, 0, self::HOECHSTENS_GLOSSAR),
            $this->gruppen($begriffe),
            $this->oeffentlichkeitsarbeit($begriffe),
            $this->fristen($begriffe, $sprache),
        );

        usort($treffer, fn ($a, $b) => $b['punkte'] <=> $a['punkte']);

        return [
            'treffer' => array_slice($treffer, 0, self::HOECHSTENS),
            'krise' => $krise,
            'begriffe' => $begriffe,
            // Wie viele Einträge die Suche in jedem Glossar mit derselben
            // Eingabe zeigt. Für den Verweis „Alle … in „Glossar““ unter den
            // Treffern. Erst ab drei Zeichen: „au“ steckt in jedem dritten Wort.
            'im_glossar' => mb_strlen(GlossaryTerm::kompakt($anfrage)) < 3 ? [] : $this->imGlossar($anfrage, $sprache),
        ];
    }

    /**
     * Passende Einträge je Glossar-Seite (KEV-155), die meisten zuerst.
     *
     * @return list<array{titel: string, url: string, anzahl: int}>
     */
    private function imGlossar(string $anfrage, string $sprache): array
    {
        return GlossaryTerm::veroeffentlicht()->where('locale', $sprache)->get()
            ->filter->passt($anfrage)
            ->groupBy(fn (GlossaryTerm $eintrag) => $this->glossarSeite($eintrag, $sprache)?->getKey() ?? 0)
            ->map(function ($eintraege) use ($sprache) {
                $seite = $this->glossarSeite($eintraege->first(), $sprache);

                return $seite
                    ? ['titel' => $seite->titel, 'url' => $seite->pfad(), 'anzahl' => $eintraege->count()]
                    : null;
            })
            ->filter()
            ->sortByDesc('anzahl')
            ->values()
            ->all();
    }

    /**
     * Die Seite, auf der ein Glossar-Eintrag steht, einmal je Seite gesucht.
     *
     * @var array<string, ?\App\Models\Page>
     */
    private array $glossarSeiten = [];

    private function glossarSeite(GlossaryTerm $eintrag, string $sprache): ?\App\Models\Page
    {
        $schluessel = ($eintrag->seite_gruppe ?? GlossaryTerm::HAUPTSEITE).'|'.$sprache;

        if (! array_key_exists($schluessel, $this->glossarSeiten)) {
            $this->glossarSeiten[$schluessel] = $eintrag->seite($sprache);
        }

        return $this->glossarSeiten[$schluessel];
    }

    /**
     * @return array{genau: list<string>, klein: list<string>}
     */
    private function stuecke(string $anfrage): array
    {
        $woerter = preg_split('/[^\p{L}\p{N}]+/u', trim($anfrage), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $genau = [];

        foreach ($woerter as $i => $wort) {
            for ($laenge = 1; $laenge <= 3 && $i + $laenge <= count($woerter); $laenge++) {
                $genau[] = implode('', array_slice($woerter, $i, $laenge));
            }
        }

        $genau = array_values(array_unique($genau));

        return [
            'genau' => $genau,
            'klein' => array_values(array_unique(array_filter(
                array_map(fn ($stueck) => GlossaryTerm::kompakt($stueck), $genau),
                fn ($stueck) => mb_strlen($stueck) >= 2,
            ))),
        ];
    }

    /**
     * Anfrage in Suchbegriffe zerlegen — inklusive der Synonyme.
     *
     * @return list<string>
     */
    public function begriffe(string $anfrage): array
    {
        $roh = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower(trim($anfrage)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $begriffe = [];
        $eigene = [];
        $synonyme = [];

        foreach ($roh as $wort) {
            // Einzelne Buchstaben und Füllwörter tragen nichts bei. „GdB" ist
            // die Ausnahme, die die Regel bestätigt — deshalb erst ab drei
            // Zeichen aussortieren, und Abkürzungen stehen in den Synonymen.
            if (mb_strlen($wort) < 3 || in_array($wort, self::FUELLWOERTER, true)) {
                continue;
            }

            $begriffe[] = $wort;
            $eigene[] = $wort;

            foreach (self::SYNONYME[$wort] ?? [] as $synonym) {
                $begriffe[] = $synonym;
                $synonyme[] = $synonym;
            }
        }

        $this->nurWortanfang = array_fill_keys(array_diff($synonyme, $eigene), true);

        return array_values(array_unique($begriffe));
    }

    /** Erkennt Wendungen, die auf eine akute Krise hindeuten. */
    public function kriseErkannt(string $anfrage): bool
    {
        $text = mb_strtolower($anfrage);

        foreach (self::KRISE as $wendung) {
            if (str_contains($text, $wendung)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $begriffe
     * @return list<array<string, mixed>>
     */
    private function seiten(array $begriffe, string $sprache): array
    {
        $treffer = [];

        /*
         * `noindex` schliesst hier NICHT aus.
         *
         * Das Kennzeichen heisst „nicht bei Google", nicht „unauffindbar". Die
         * Seiten zu GdB, Pflegegrad und Entschädigungsrecht tragen es, weil ihr
         * Text noch nicht gegengelesen ist — es sind aber genau die Seiten, die
         * Betroffene suchen. Sie aus der eigenen Suche zu nehmen hiesse, die
         * Hälfte der Website vor den eigenen Besuchern zu verstecken; den
         * Entwurfsvermerk sehen sie beim Öffnen ohnehin.
         *
         * Wer eine Seite wirklich verbergen will, nimmt sie auf Entwurf
         * (`published_at` leeren) — dann ist sie überall weg.
         */
        $seiten = Page::veroeffentlicht()
            ->where('locale', $sprache)
            ->with('blocks')
            ->get();

        foreach ($seiten as $seite) {
            if ($seite->slug === 'startseite') {
                continue;   // die findet man über das Logo
            }

            $punkte = 0;
            $fundstelle = null;

            $punkte += 10 * $this->wieOft($begriffe, $seite->titel);
            $punkte += 4 * $this->wieOft($begriffe, (string) $seite->meta_description);

            foreach (in_array($seite->slug, self::NUR_TITEL, true) ? [] : $seite->blocks as $block) {
                $ueberschrift = (string) ($block->data['titel'] ?? '');
                $punkte += 6 * $this->wieOft($begriffe, $ueberschrift);

                $fliesstext = $this->textAus($block->data ?? []);
                $imText = $this->wieOft($begriffe, $fliesstext);
                $punkte += $imText;

                if ($imText > 0 && $fundstelle === null) {
                    $fundstelle = $this->ausschnitt($fliesstext, $begriffe);
                }
            }

            if ($punkte > 0) {
                $treffer[] = [
                    'art' => 'seite',
                    'titel' => $seite->titel,
                    'url' => $seite->pfad(),
                    'bereich' => Seitenkontext::fuer($seite->slug)->bereichName(),
                    'ausschnitt' => $fundstelle ?? (string) $seite->meta_description,
                    'punkte' => $punkte,
                ];
            }
        }

        return $treffer;
    }

    /**
     * @param  list<string>  $begriffe
     * @return list<array<string, mixed>>
     */
    private function glossar(array $begriffe, string $sprache): array
    {
        $treffer = [];

        foreach (GlossaryTerm::where('locale', $sprache)->whereNotNull('published_at')->get() as $eintrag) {
            // Ein Glossarbegriff, der genau getroffen wird, ist fast immer das,
            // wonach jemand sucht — „Was heisst GdB?" ist eine der häufigsten
            // Fragen überhaupt. Deshalb höher bewertet als ein Seitentitel —
            // seit KEV-153 nur, wenn der Eintrag auch etwas erklärt
            // (abkuerzungGetroffen()).
            $punkte = $this->abkuerzungGetroffen((string) $eintrag->kuerzel, filled($eintrag->erklaerung))
                + 14 * $this->wieOftImGlossar($begriffe, $eintrag->begriff.' '.(string) $eintrag->kuerzel)
                + 2 * $this->wieOftImGlossar($begriffe, (string) $eintrag->erklaerung);

            if ($punkte > 0) {
                // Seit KEV-155 gibt es mehrere Glossare: der Link führt auf
                // die Seite, auf der der Eintrag steht, in der Sprache der
                // Suche (sonst landete die englische Suche im deutschen
                // Glossar, 08.10.2026).
                $seite = $this->glossarSeite($eintrag, $sprache);

                $treffer[] = [
                    'art' => 'glossar',
                    'titel' => $eintrag->begriff.($eintrag->kuerzel ? ' ('.$eintrag->kuerzel.')' : ''),
                    'url' => ($seite?->pfad() ?? (\App\Models\Language::finden($sprache)?->pfad('/glossar') ?? '/glossar'))
                        .'#'.$eintrag->slug,
                    'bereich' => $seite?->titel ?? __('Glossar'),
                    'ausschnitt' => Str::limit((string) $eintrag->erklaerung, 160),
                    'punkte' => $punkte,
                ];
            }
        }

        return $treffer;
    }

    /**
     * Wer genau eine Abkürzung eintippt, will genau diese (KEV-153). Gleiche
     * Schreibweise zählt mehr als nur dieselben Buchstaben: „AG 01“ meint die
     * Arbeitsgruppe, nicht „aG“ (außergewöhnliche Gehbehinderung).
     *
     * Voll zählt das nur mit Erklärung. Steht im Eintrag bloß die Auflösung
     * („GdB — Grad der Behinderung“), hilft eine Seite zum Thema mehr; sie
     * soll dann vorn stehen, der Eintrag gleich dahinter.
     */
    private function abkuerzungGetroffen(string $kuerzel, bool $mitErklaerung): int
    {
        $vereinfacht = GlossaryTerm::kompakt($kuerzel);

        if (mb_strlen($vereinfacht) < 2) {
            return 0;
        }

        $buchstaben = preg_replace('/[^\p{L}\p{N}]+/u', '', $kuerzel);

        if (in_array($buchstaben, $this->abkuerzungen['genau'], true)) {
            return $mitErklaerung ? 40 : 20;
        }

        // Bei zwei Buchstaben mit Kleinbuchstaben darin (aG, Bl, Dx) trägt
        // die Schreibweise die Bedeutung. Dort zählt nur die genaue.
        $kurzUndGemischt = mb_strlen($vereinfacht) <= 2 && $buchstaben !== mb_strtoupper($buchstaben);

        if ($kurzUndGemischt || ! in_array($vereinfacht, $this->abkuerzungen['klein'], true)) {
            return 0;
        }

        return $mitErklaerung ? 25 : 10;
    }

    /**
     * Wie wieOft(), aber kurze Suchwörter nur am Wortanfang. Im Glossar
     * stehen rund 200 kurze Einträge; „bsa“ fand sonst „Absatz“ und
     * „Halbsatz“. Ab fünf Zeichen auch mitten im Wort, damit „Krankenkasse“
     * die „Ortskrankenkasse“ findet.
     *
     * @param  list<string>  $begriffe
     */
    private function wieOftImGlossar(array $begriffe, string $text): int
    {
        $vorher = $this->nurWortanfang;

        foreach ($begriffe as $begriff) {
            if (mb_strlen($begriff) < 5) {
                $this->nurWortanfang[$begriff] = true;
            }
        }

        $anzahl = $this->wieOft($begriffe, $text);
        $this->nurWortanfang = $vorher;

        return $anzahl;
    }

    /**
     * @param  list<string>  $begriffe
     * @return list<array<string, mixed>>
     */
    private function gruppen(array $begriffe): array
    {
        $treffer = [];

        foreach (Group::whereNotNull('published_at')->get() as $gruppe) {
            $punkte = 8 * $this->wieOft($begriffe, $gruppe->name.' '.(string) $gruppe->kuerzel)
                + 2 * $this->wieOft($begriffe, (string) $gruppe->teaser.' '.(string) $gruppe->beschreibung);

            if ($punkte > 0) {
                $treffer[] = [
                    'art' => 'gruppe',
                    'titel' => $gruppe->name,
                    // Bis KEV-73 auch für Arbeitsgruppen /selbsthilfegruppen#…
                    'url' => $gruppe->url(),
                    'bereich' => $gruppe->typ === 'selbsthilfe' ? __('Selbsthilfegruppen') : __('Arbeitsgruppen'),
                    'ausschnitt' => Str::limit((string) $gruppe->teaser, 160),
                    'punkte' => $punkte,
                ];
            }
        }

        return $treffer;
    }

    /**
     * Messen, Petitionen, Presse (KEV-134), gewichtet wie die Gruppen.
     *
     * @param  list<string>  $begriffe
     * @return list<array<string, mixed>>
     */
    private function oeffentlichkeitsarbeit(array $begriffe): array
    {
        $treffer = [];

        foreach (OutreachItem::veroeffentlicht()->get() as $eintrag) {
            $punkte = 8 * $this->wieOft($begriffe, $eintrag->titel.' '.(string) $eintrag->ort)
                + 2 * $this->wieOft($begriffe, strip_tags((string) $eintrag->teaser.' '.(string) $eintrag->beschreibung));

            if ($punkte > 0) {
                $treffer[] = [
                    'art' => 'oeffentlichkeitsarbeit',
                    'titel' => $eintrag->titel,
                    'url' => $eintrag->url(),
                    'bereich' => __('Öffentlichkeitsarbeit'),
                    'ausschnitt' => Str::limit($eintrag->anriss(), 160),
                    'punkte' => $punkte,
                ];
            }
        }

        return $treffer;
    }

    /**
     * Die Fristenübersicht (KEV-152): Wer „Widerspruchsfrist“ oder
     * „Fristverlängerung“ sucht, landet auf der Karte, aufgeklappt
     * (aufklapper.js). Höchstens so viele wie aus dem Glossar, „Frist“ trifft
     * sonst jede.
     *
     * @param  list<string>  $begriffe
     * @return list<array<string, mixed>>
     */
    private function fristen(array $begriffe, string $sprache): array
    {
        // Die Seite mit dem Baustein, in der Sprache der Suche, sonst deutsch.
        $seiten = Page::veroeffentlicht()
            ->standardfassung()
            ->whereIn('locale', array_unique([$sprache, \App\Models\Language::standardCode()]))
            ->whereHas('blocks', fn ($q) => $q->where('typ', 'fristen'))
            ->get();
        $seite = $seiten->firstWhere('locale', $sprache) ?? $seiten->first();

        if (! $seite) {
            return [];
        }

        $treffer = [];

        foreach (Frist::veroeffentlicht()->get() as $frist) {
            $text = html_entity_decode(strip_tags(str_replace('<', ' <', (string) $frist->inhalt)));

            $punkte = 12 * $this->wieOft($begriffe, $frist->titel.' '.implode(' ', $frist->schlagworte ?? []))
                + 2 * $this->wieOft($begriffe, (string) $frist->frist.' '.$text);

            if ($punkte > 0) {
                $treffer[] = [
                    'art' => 'frist',
                    'titel' => $frist->titel,
                    'url' => $seite->pfad().'#'.$frist->slug,
                    'bereich' => $seite->titel,
                    'ausschnitt' => Str::limit(filled($frist->frist) ? $frist->frist : $text, 160),
                    'punkte' => $punkte,
                ];
            }
        }

        usort($treffer, fn ($a, $b) => $b['punkte'] <=> $a['punkte']);

        return array_slice($treffer, 0, self::HOECHSTENS_GLOSSAR);
    }

    /**
     * Wie oft kommen die Begriffe im Text vor?
     *
     * Als Teilstring und nicht als ganzes Wort — im Deutschen steckt der
     * gesuchte Begriff oft in einem längeren: Wer „Ausweis" sucht, meint den
     * „Schwerbehindertenausweis", und wer „Pflege" eingibt, den „Pflegegrad".
     * Eine Wortgrenzen-Suche fände beides nicht.
     *
     * @param  list<string>  $begriffe
     */
    private function wieOft(array $begriffe, string $text): int
    {
        if ($text === '') {
            return 0;
        }

        $text = $this->vereinfacht($text);
        $summe = 0;

        foreach ($begriffe as $begriff) {
            $gesucht = $this->vereinfacht($begriff);

            $summe += isset($this->nurWortanfang[$begriff])
                ? preg_match_all('/(?<![\p{L}\p{N}])'.preg_quote($gesucht, '/').'/u', $text)
                : substr_count($text, $gesucht);
        }

        return $summe;
    }

    /**
     * Umlaute und Schärfen abtragen.
     *
     * Wer „schwerbehindertenausweis" ohne Umlaute tippt oder „Entschadigung"
     * schreibt, sucht dasselbe. Auf dem Handy und mit motorischen
     * Einschränkungen passiert das ständig.
     */
    private function vereinfacht(string $text): string
    {
        return strtr(mb_strtolower($text), [
            'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ß' => 'ss',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'á' => 'a', 'à' => 'a',
        ]);
    }

    /**
     * Sämtlichen Fliesstext eines Bausteins einsammeln.
     *
     * Rekursiv über die Bausteindaten: Die Felder heissen je nach Typ anders
     * (`absaetze`, `text`, `zitat`, `karten[].text` …), und eine Liste
     * gepflegter Feldnamen liefe der Wirklichkeit immer hinterher. Zahlen und
     * Wahrheitswerte fliegen raus — `true` ist kein Suchtreffer.
     *
     * @param  array<string|int, mixed>  $daten
     */
    private function textAus(array $daten): string
    {
        $stuecke = [];
        $technik = ['url', 'datei', 'variant', 'icon', 'art', 'arten', 'widget'];

        // Auch Listen unter diesen Schlüsseln fallen weg: Seit KEV-136 nennt
        // `arten` mehrere Arten, und array_walk_recursive sähe deren
        // Einträge nur unter 0, 1, …
        $ohneTechnik = function (array $teil) use (&$ohneTechnik, $technik): array {
            foreach ($teil as $schluessel => $wert) {
                if (in_array($schluessel, $technik, true)) {
                    unset($teil[$schluessel]);
                } elseif (is_array($wert)) {
                    $teil[$schluessel] = $ohneTechnik($wert);
                }
            }

            return $teil;
        };

        // array_walk_recursive nimmt nur eine Variable (Referenz)
        $inhalt = $ohneTechnik($daten);

        array_walk_recursive($inhalt, function ($wert, $schluessel) use (&$stuecke, $technik) {
            // `url` und `variant` sind Technik, kein Inhalt — ein Treffer in
            // einer Adresse wäre für Suchende nicht nachvollziehbar.
            if (is_string($wert) && ! in_array($schluessel, $technik, true)) {
                // *Markierungen* (Fettdruck im Absatz) gehören nicht in den Ausschnitt.
                $stuecke[] = ohne_hervorhebung($wert);
            }
        });

        return implode(' ', $stuecke);
    }

    /**
     * Die Stelle im Text zeigen, an der ein Begriff steht.
     *
     * Wer eine Trefferliste überfliegt, entscheidet an diesem Ausschnitt, ob
     * sich das Anklicken lohnt. Ein abgeschnittener Anfang des Fliesstextes
     * hilft dabei nicht — die Fundstelle schon.
     *
     * @param  list<string>  $begriffe
     */
    private function ausschnitt(string $text, array $begriffe): string
    {
        $vereinfacht = $this->vereinfacht($text);

        foreach ($begriffe as $begriff) {
            $stelle = strpos($vereinfacht, $this->vereinfacht($begriff));

            if ($stelle === false) {
                continue;
            }

            // Etwas vor der Fundstelle anfangen, damit sie im Satz steht und
            // nicht am Rand klebt.
            $von = max(0, $stelle - 60);
            $ausschnitt = mb_substr($text, $von, 200);

            return ($von > 0 ? '… ' : '').trim($ausschnitt).' …';
        }

        return Str::limit($text, 160);
    }
}
