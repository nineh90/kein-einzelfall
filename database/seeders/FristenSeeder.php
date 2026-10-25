<?php

namespace Database\Seeders;

use App\Models\Frist;
use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * „Fristen und Möglichkeiten“ unter „Wissen“ (KEV-152).
 *
 * Aus Taddis Dokument „Fristen“, gebaut nach ihrem Vorschlag: Einleitung,
 * darunter zwei Kacheln, links „Mögliche Fristen“, rechts „Frist bekommen –
 * Was jetzt?“. Beide springen zu ihrem Bereich weiter unten auf derselben
 * Seite (Taddi: „Auf dieser Seite findest du zwei Bereiche“).
 *
 * - Mögliche Fristen: ihr Text, ihr „Wichtig“ als Hinweis-Kasten, darunter
 *   die Fristenübersicht (Baustein `fristen`) mit Suche „Welche Frist suchst
 *   du?“, A–Z und aufklappbaren Karten mit Schlagworten. Die 19 Fristen
 *   (data/fristen.json) pflegt der Verein im Panel unter „Fristen“: Taddis
 *   „Inhalt muss erweiterbar und änderbar sein“.
 * - Frist bekommen: ihr kleiner Leitfaden in acht Schritten (Baustein
 *   `schritte`), danach „Noch wichtig“ mit ihrer Aufzählung.
 *
 * Von uns: die Schlagworte je Frist (Taddi nennt als Beispiele „Behörde“,
 * „Gericht“, „Widerspruch“, „Antrag“; dazu „Klage“ und „Bescheid“), die
 * Überschrift über den Kacheln und deren Linktexte. Korrigiert:
 * „Kontakt@kein-einzelfall.de“ → klein, doppeltes Leerzeichen in „Frist
 * bekommen - Was jetzt?“, Gedankenstrich. In „Fristverlängerung“ und
 * „Überprüfungsantrag“ stehen ihre losen Zeilen („… und …“) als Satz mit
 * Aufzählung, ohne Wortänderung. Bild: ihr „5. Fristen“ (Sanduhr vor
 * Akten) aus dem Drive-Ordner „3. Wissen“, rechts um Wand verlängert.
 *
 * Legt nur an, was fehlt.
 */
class FristenSeeder extends Seeder
{
    public const SLUG = 'fristen';

    public const TITEL = 'Fristen und Möglichkeiten';

    public const META_DESCRIPTION = 'Fristen im Sozialrecht verständlich erklärt: Widerspruch, Klage, '
        .'Fristverlängerung, Sachstandsanfrage und was du tun kannst, wenn du eine Frist bekommst. Von '
        .'KE!N EINZELFALL e.V.';

    /** KEV-252: Taddis grüne Überschrift über der Einleitung. */
    public const EINLEITUNG_TITEL = 'Damit aus einer Frist keine verpasste Chance wird!';

    public const EINLEITUNG = [
        'Fristen können schnell Druck machen – besonders dann, wenn du ohnehin belastet bist oder negative '
            .'Erfahrungen mit Behörden und Verfahren verbindest.',
        'Gerade deshalb ist es wichtig zu wissen: Du musst nicht alles sofort schaffen. Manchmal gibt es '
            .'Möglichkeiten, dir Zeit zu verschaffen, Unterlagen nachzureichen oder eine Frist verlängern zu lassen.',
        'Wir setzen uns dafür ein, dass Hilfesysteme nicht zusätzlich belasten oder retraumatisierend wirken. '
            .'Menschen sollen Unterstützung bekommen, ohne durch unnötigen Druck, unverständliche Abläufe oder '
            .'starre Verfahren noch weiter überfordert zu werden.',
        'Auf dieser Seite findest du zwei Bereiche: Unter „Mögliche Fristen“ kannst du typische Fristen im '
            .'Sozialrecht suchen und nachlesen. Unter „Frist bekommen – Was jetzt?“ zeigen wir dir praktische '
            .'Möglichkeiten und nächste Schritte.',
        'Nutze die Zeit, die dir zusteht, damit du Dinge in Ruhe sortieren und Schritt für Schritt angehen kannst.',
    ];

    public const KACHELN_TITEL = 'Was du hier findest';

    public const KACHELN = [
        [
            'icon' => 'search',
            'titel' => 'Mögliche Fristen',
            'text' => 'Widerspruch, Klage, Fristverlängerung oder Sachstandsanfrage – welche Frist gilt eigentlich wann?',
            'url' => '#moegliche-fristen',
            'link' => 'Zur Fristenübersicht',
        ],
        [
            'icon' => 'notebook-pen',
            'titel' => 'Frist bekommen – Was jetzt?',
            'text' => 'Du hast eine Frist gesetzt bekommen und weißt gerade nicht genau, was du tun sollst?',
            'url' => '#frist-bekommen',
            'link' => 'Zum Leitfaden',
        ],
    ];

    public const UEBERSICHT_TITEL = 'Mögliche Fristen';

    public const UEBERSICHT = [
        'Widerspruch, Klage, Fristverlängerung oder Sachstandsanfrage – welche Frist gilt eigentlich wann?',
        'Im Sozialrecht begegnen dir viele unterschiedliche Fristen. Manche sind gesetzlich festgelegt, andere '
            .'werden von Behörden oder Gerichten gesetzt. Und manchmal gibt es überhaupt keine feste '
            .'Bearbeitungsfrist.',
        'Mit unserer Fristenübersicht möchten wir dir helfen, schneller zu finden, was du gerade brauchst.',
        'Suchst du eine bestimmte Frist oder Situation? Nutze unsere Suche oder wähle den passenden Begriff von '
            .'A bis Z.',
    ];

    public const WICHTIG = 'Diese Übersicht dient der allgemeinen Orientierung und ersetzt keine Rechtsberatung. Je '
        .'nach Verfahren, Bescheid, Bekanntgabe oder persönlicher Situation können Besonderheiten gelten. Prüfe '
        .'deshalb immer das konkrete Schreiben, das du erhalten hast. Wenn du dir unsicher bist, nimm gern Kontakt '
        .'mit uns auf unter kontakt@kein-einzelfall.de. Gemeinsam schauen wir uns deinen Bescheid an und sortieren, '
        .'was jetzt wichtig ist.';

    public const LEITFADEN_TITEL = 'Frist bekommen – Was jetzt?';

    public const LEITFADEN_EINLEITUNG = 'Du hast eine Frist gesetzt bekommen und weißt gerade nicht genau, was du '
        .'tun sollst? Dann hilft es, erst einmal zu prüfen, um welche Frist es sich handelt, wann sie endet und ob du '
        .'reagieren oder eine Verlängerung beantragen kannst. Hier ein kleiner Leitfaden.';

    public const SCHRITTE = [
        ['titel' => 'Eingangstag notieren', 'text' => 'Schreib dir auf, wann das Schreiben tatsächlich bei dir angekommen ist.'],
        ['titel' => 'Rechtsbehelfsbelehrung lesen', 'text' => 'Dort findest du häufig die wichtigste Information über Rechtsbehelf und Frist.'],
        ['titel' => 'Frist sofort im Kalender eintragen', 'text' => 'Nicht erst dann, wenn du entschieden hast, was du tun möchtest.'],
        ['titel' => 'Umschlag und Nachweise aufbewahren', 'text' => 'Gerade der tatsächliche Zugang eines Schreibens kann später wichtig werden.'],
        ['titel' => 'Fehlende Unterlagen frühzeitig mitteilen', 'text' => 'Warte nicht bis zum letzten Tag.'],
        ['titel' => 'Fristverlängerung rechtzeitig beantragen', 'text' => 'Wenn es sich um eine von der Behörde gesetzte Frist handelt und du mehr Zeit brauchst.'],
        ['titel' => 'Gesetzliche Fristen zuerst sichern', 'text' => 'Bei Widerspruch oder Klage lieber rechtzeitig einlegen und eine ausführliche Begründung später nachreichen, statt die Frist verstreichen zu lassen.'],
        ['titel' => 'Versand nachweisen können', 'text' => 'Bei wichtigen Schreiben solltest du nachvollziehen können, wann und auf welchem Weg du sie übermittelt hast.'],
    ];

    public const NOCH_WICHTIG_TITEL = 'Noch wichtig';

    public const NOCH_WICHTIG = [
        "Fristen können unter anderem davon abhängen:\n"
            ."- um welches Verfahren es geht,\n"
            ."- welcher Sozialleistungsträger zuständig ist,\n"
            ."- um welche Art von Bescheid es sich handelt,\n"
            ."- wann und wie ein Schreiben bekanntgegeben wurde,\n"
            ."- ob die Rechtsbehelfsbelehrung korrekt ist,\n"
            ."- ob eine gesetzliche oder behördlich gesetzte Frist vorliegt,\n"
            ."- ob besondere gesetzliche Vorschriften gelten oder\n"
            .'- ob im konkreten Einzelfall besondere Umstände bestehen.',
        'Diese Übersicht dient ausschließlich der allgemeinen Orientierung und stellt keine Rechtsberatung dar.',
        'Wenn du unsicher bist, lass eine laufende Frist möglichst frühzeitig prüfen. Dafür kannst du auch gern mit '
            .'uns Kontakt aufnehmen unter kontakt@kein-einzelfall.de.',
        'Denn manchmal entscheidet nicht nur der Inhalt eines Schreibens darüber, wie es weitergeht – sondern auch '
            .'der Zeitpunkt.',
    ];

    public function run(): void
    {
        self::anlegen();
    }

    /** @return list<array{slug: string, titel: string, frist: ?string, schlagworte: list<string>, inhalt: string}> */
    public static function fristen(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/fristen.json')), true);
    }

    public static function anlegen(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => self::SLUG, 'locale' => 'de'],
            [
                'titel' => self::TITEL,
                'meta_title' => self::TITEL.' - Kein Einzelfall e.V.',
                'meta_description' => self::META_DESCRIPTION,
                'published_at' => now(),
            ],
        );

        if ($seite->wasRecentlyCreated) {
            $bausteine = [
                ['typ' => 'text', 'data' => ['titel' => self::EINLEITUNG_TITEL, 'absaetze' => self::EINLEITUNG]],
                ['typ' => 'quick_access', 'data' => ['titel' => self::KACHELN_TITEL, 'karten' => self::KACHELN]],
                ['typ' => 'text', 'data' => [
                    'titel' => self::UEBERSICHT_TITEL,
                    'anker' => 'moegliche-fristen',
                    'absaetze' => self::UEBERSICHT,
                ]],
                ['typ' => 'hinweis', 'data' => ['art' => 'wichtig', 'text' => self::WICHTIG]],
                ['typ' => 'fristen', 'data' => []],
                ['typ' => 'schritte', 'data' => [
                    'titel' => self::LEITFADEN_TITEL,
                    'anker' => 'frist-bekommen',
                    'einleitung' => self::LEITFADEN_EINLEITUNG,
                    'schritte' => self::SCHRITTE,
                ]],
                ['typ' => 'text', 'data' => ['titel' => self::NOCH_WICHTIG_TITEL, 'absaetze' => self::NOCH_WICHTIG]],
            ];

            foreach ($bausteine as $position => $baustein) {
                $seite->blocks()->create($baustein + ['position' => $position]);
            }
        }

        foreach (self::fristen() as $frist) {
            Frist::firstOrCreate(['slug' => $frist['slug']], $frist + ['published_at' => now()]);
        }

        Titelbilder::setzen();
    }
}
