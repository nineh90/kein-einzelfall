<?php

namespace App\Support;

/**
 * Korrekturen an Texten der Altseite und an unseren eigenen (Prüfung der
 * Firma, 08.10.2026): Tippfehler, beim Import zusammengeklebte Sätze und
 * Zeilen, veraltete Gesetzesangaben.
 *
 * Eine Stelle für zwei Wege: Der AltseiteSeeder wendet sie beim Import an
 * (frische Installation), die Migration `texte_korrigieren` auf bestehenden
 * Datenbanken. Ersetzt wird nur exakt der alte Wortlaut. Hat der Verein eine
 * Stelle im Panel schon selbst geändert, findet die Korrektur nichts mehr und
 * lässt sie stehen.
 */
class Textpflege
{
    /**
     * Ersetzungen innerhalb eines Textes, je Seite. Gilt für jedes Textfeld
     * eines Bausteins, auch für Überschriften.
     *
     * @var array<string, array<string, string>>
     */
    public const ERSETZUNGEN = [
        'impressum' => [
            // § 5 TMG gilt seit 14.05.2024 als § 5 DDG, § 55 RStV seit 2020
            // als § 18 Medienstaatsvertrag.
            'Angaben gemäß § 5 TMG' => 'Angaben gemäß § 5 DDG',
            'Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV' => 'Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV',
            // Wie auf Start- und Spendenseite.
            'BIC: GENO DEF1 SLR' => 'BIC: GENODEF1SLR',
        ],
        'datenschutz' => [
            // Seit 14.05.2024 heisst das TTDSG TDDDG, § 25 bleibt § 25.
            '§ 25 Abs. 1 TTDSG' => '§ 25 Abs. 1 TDDDG',
        ],
        'verein' => [
            'Unsere Vorstandsebene und unsere Mitglieder besteht aus' => 'Unsere Vorstandsebene und unsere Mitglieder bestehen aus',
        ],
        'mitgliedschaft' => [
            // Das Dokument heisst „Beitrags- und Mitgliederordnung“.
            'Beitritts- und Mitgliedsordnung' => 'Beitrags- und Mitgliederordnung',
            'Beitritts- und Mitgliederordnung' => 'Beitrags- und Mitgliederordnung',
        ],
        'das-hilfesystem' => [
            'Referent: Quen Winter' => 'Referentin: Quen Winter',
        ],
        'buerokratie-labyrinth' => [
            'Referent: Tatjana Belmar, weitere Referenten willkommen' => 'Referentin: Tatjana Belmar, weitere Referentinnen und Referenten willkommen',
            'Datum: coming soon' => 'Datum: folgt',
        ],
        'traumafolgestoerungen-verstehen' => [
            'REFERENT GESUCHT' => 'Referentin oder Referent gesucht',
            'Datum: coming soon' => 'Datum: folgt',
            'Uhrzeit: coming soon' => 'Uhrzeit: folgt',
        ],
        'barrierefreiheit' => [
            // Unser Text. Der Knopf sitzt am linken Rand, nicht neben dem Notausgang.
            'Neben dem Notausgang findest du ein rundes Symbol.' => 'Am linken Bildschirmrand findest du ein rundes Symbol, auf dem Handy heißt es „Darstellung“ in der Leiste unten.',
            'den Knopf „Notausgang".' => 'den Knopf „Notausgang“.',
        ],
        'trigger-warnung' => [
            // Unser Text. Das Opfer-Telefon in der Fusszeile ist nur 7–22 Uhr erreichbar.
            'Die Notfallnummern stehen am Ende jeder Seite und sind rund um die Uhr erreichbar.' => 'Die Notfallnummern stehen am Ende jeder Seite. Die TelefonSeelsorge (116 123) ist rund um die Uhr erreichbar.',
        ],
    ];

    /**
     * Ganze Absätze, die beim Import zu einer Zeile zusammengeklebt wurden
     * (Adressen ohne Zeilenumbruch: „Schiffbeker Höhe 3022119 Hamburg“).
     *
     * @var array<string, array<string, list<string>>>
     */
    public const ABSAETZE = [
        'impressum' => [
            'KE!N EINZELFALL e.V.Schiffbeker Höhe 3022119 Hamburg' => ['KE!N EINZELFALL e.V.', 'Schiffbeker Höhe 30', '22119 Hamburg'],
            'Tatjana BelmarSchiffbeker Höhe 3022119 Hamburg' => ['Tatjana Belmar', 'Schiffbeker Höhe 30', '22119 Hamburg'],
        ],
        'datenschutz' => [
            'Zuständig für unseren Verein:Der Hamburgische Beauftragte für Datenschutz und InformationsfreiheitLudwig-Erhard-Straße 22D-20459 Hamburg' => [
                'Zuständig für unseren Verein:',
                'Der Hamburgische Beauftragte für Datenschutz und Informationsfreiheit',
                'Ludwig-Erhard-Straße 22',
                '20459 Hamburg',
            ],
        ],
    ];

    /**
     * Zwischenüberschriften, die am Absatz darunter kleben
     * („Dauer der SpeicherungDie Daten werden …“). Sie werden ein eigener,
     * fett gesetzter Absatz.
     *
     * @var array<string, list<string>>
     */
    public const ZWISCHENTITEL = [
        'datenschutz' => [
            'Tool für die Einwilligung zum Setzen von Cookies',
            'Rechtsgrundlage für die Datenverarbeitung',
            'Zweck der Datenverarbeitung',
            'Dauer der Speicherung',
            'Widerspruchs- und Beseitigungsmöglichkeit',
        ],
    ];

    /** Schlüssel, deren Werte Adressen oder Kennungen sind, kein Text. */
    private const KEIN_TEXT = ['url', 'bild', 'src', 'widget', 'iban', 'bic', 'icon', 'variant', 'art', 'typ'];

    /**
     * Satzzeichen ohne folgendes Leerzeichen vor einem neuen Satz:
     * „hinweisen.Wichtig“, „werden?Es“, „Wichtig:Es“. Beim Import der
     * Altseite gingen Zeilenumbrüche verloren.
     *
     * Nur Kleinbuchstabe, Satzzeichen, Grossbuchstabe, Kleinbuchstabe:
     * „e.V.“, „z.B.“ und Adressen wie kein-einzelfall.de bleiben unberührt.
     */
    public static function luecken(string $text): string
    {
        return preg_replace('/(?<=[a-zäöüß])([.?!:])(?=[A-ZÄÖÜ][a-zäöüß])/u', '$1 ', $text);
    }

    /**
     * Alle Korrekturen für die Bausteindaten einer Seite.
     *
     * @param  array<string|int, mixed>  $data
     * @return array<string|int, mixed>
     */
    public static function bausteinDaten(array $data, string $slug): array
    {
        if (isset($data['absaetze']) && is_array($data['absaetze'])) {
            $data['absaetze'] = self::absaetze($data['absaetze'], $slug);
        }

        return self::felder($data, $slug);
    }

    /** Für Fliesstext ausserhalb der Bausteine, etwa Teamprofile. */
    public static function text(string $text, string $slug = ''): string
    {
        $text = self::luecken($text);

        return strtr($text, self::ERSETZUNGEN[$slug] ?? []);
    }

    /**
     * @param  list<mixed>  $absaetze
     * @return list<mixed>
     */
    private static function absaetze(array $absaetze, string $slug): array
    {
        $neu = [];

        foreach ($absaetze as $absatz) {
            if (! is_string($absatz)) {
                $neu[] = $absatz;

                continue;
            }

            if (isset(self::ABSAETZE[$slug][$absatz])) {
                array_push($neu, ...self::ABSAETZE[$slug][$absatz]);

                continue;
            }

            foreach (self::ZWISCHENTITEL[$slug] ?? [] as $titel) {
                if (preg_match('/^'.preg_quote($titel, '/').'(?=[A-ZÄÖÜ])/u', $absatz)) {
                    $neu[] = '*'.$titel.'*';
                    $absatz = mb_substr($absatz, mb_strlen($titel));
                    break;
                }
            }

            $neu[] = $absatz;
        }

        return $neu;
    }

    /**
     * @param  array<string|int, mixed>  $data
     * @return array<string|int, mixed>
     */
    private static function felder(array $data, string $slug): array
    {
        foreach ($data as $schluessel => $wert) {
            if (is_array($wert)) {
                $data[$schluessel] = self::felder($wert, $slug);
            } elseif (is_string($wert) && ! in_array($schluessel, self::KEIN_TEXT, true)) {
                $data[$schluessel] = self::text($wert, $slug);
            }
        }

        return $data;
    }
}
