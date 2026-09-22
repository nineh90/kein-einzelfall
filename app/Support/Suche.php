<?php

namespace App\Support;

use App\Models\GlossaryTerm;
use App\Models\Group;
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
        'glauben' => ['beweis', 'glaubhaftmachung', 'nachweis'],
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
     * @return array{treffer: list<array<string, mixed>>, krise: bool, begriffe: list<string>}
     */
    public function suchen(string $anfrage, string $sprache = 'de'): array
    {
        $krise = $this->kriseErkannt($anfrage);
        $begriffe = $this->begriffe($anfrage);

        if ($begriffe === []) {
            return ['treffer' => [], 'krise' => $krise, 'begriffe' => []];
        }

        $treffer = array_merge(
            $this->seiten($begriffe, $sprache),
            $this->glossar($begriffe, $sprache),
            $this->gruppen($begriffe),
        );

        usort($treffer, fn ($a, $b) => $b['punkte'] <=> $a['punkte']);

        return [
            'treffer' => array_slice($treffer, 0, self::HOECHSTENS),
            'krise' => $krise,
            'begriffe' => $begriffe,
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

        foreach ($roh as $wort) {
            // Einzelne Buchstaben und Füllwörter tragen nichts bei. „GdB" ist
            // die Ausnahme, die die Regel bestätigt — deshalb erst ab drei
            // Zeichen aussortieren, und Abkürzungen stehen in den Synonymen.
            if (mb_strlen($wort) < 3 || in_array($wort, self::FUELLWOERTER, true)) {
                continue;
            }

            $begriffe[] = $wort;

            foreach (self::SYNONYME[$wort] ?? [] as $synonym) {
                $begriffe[] = $synonym;
            }
        }

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

            foreach ($seite->blocks as $block) {
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
            // Fragen überhaupt. Deshalb höher bewertet als ein Seitentitel.
            $punkte = 14 * $this->wieOft($begriffe, $eintrag->begriff.' '.(string) $eintrag->kuerzel)
                + 2 * $this->wieOft($begriffe, (string) $eintrag->erklaerung);

            if ($punkte > 0) {
                $treffer[] = [
                    'art' => 'glossar',
                    'titel' => $eintrag->begriff.($eintrag->kuerzel ? ' ('.$eintrag->kuerzel.')' : ''),
                    'url' => '/glossar#'.$eintrag->slug,
                    'bereich' => 'Glossar',
                    'ausschnitt' => Str::limit((string) $eintrag->erklaerung, 160),
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
                    'url' => '/selbsthilfegruppen#'.$gruppe->slug,
                    'bereich' => 'Selbsthilfegruppen',
                    'ausschnitt' => Str::limit((string) $gruppe->teaser, 160),
                    'punkte' => $punkte,
                ];
            }
        }

        return $treffer;
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
            $summe += substr_count($text, $this->vereinfacht($begriff));
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

        array_walk_recursive($daten, function ($wert, $schluessel) use (&$stuecke) {
            // `url` und `variant` sind Technik, kein Inhalt — ein Treffer in
            // einer Adresse wäre für Suchende nicht nachvollziehbar.
            if (is_string($wert) && ! in_array($schluessel, ['url', 'variant', 'icon', 'art', 'widget'], true)) {
                $stuecke[] = $wert;
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
