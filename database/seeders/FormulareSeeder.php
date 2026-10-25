<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * „Formulare“ unter „Wissen“ (KEV-150).
 *
 * Taddis Text, die Formulare selbst folgen später. Darunter steht schon eine
 * leere Dokumentenliste „Formulare zum Herunterladen“: Sie ist unsichtbar,
 * solange nichts drin ist (PageBlock::zeigtNichts), und der Verein lädt die
 * Formulare im Panel genau dort hoch.
 *
 * Bis dahin für Suchmaschinen gesperrt: Die Seite verspricht Formulare, die
 * noch nicht da sind. Im Menü nach „Atteste“ und vor „Fristen“, wie Taddis
 * Nummern im Drive (1. Atteste, 4. Formulare, 5. Fristen).
 *
 * KEV-250: Taddis Formulare aus dem Drive („Documente“ › „3. Wissen“ ›
 * „Formulare Download“), in vier Listen nach Thema. Ihr Wunsch: am PC
 * ausfüllen und schicken, oder ausdrucken. Die Formulare haben dafür
 * Felder bekommen (bin/formular-ausfuellbar), gedruckt sehen sie aus wie bei
 * ihr. Darüber ein Abschnitt von uns, wie das geht und wohin damit. Die
 * Word-Fassungen aus dem Drive sind nicht dabei: PDF reicht für beides, und
 * Word-Dateien tragen Bearbeitungsspuren mit. Die Seite ist seitdem für
 * Suchmaschinen offen.
 *
 * Legt nur an, was fehlt.
 */
class FormulareSeeder extends Seeder
{
    public const SLUG = 'formulare';

    public const TITEL = 'Formulare';

    public const ABSAETZE = [
        'Manchmal braucht es einfach den nächsten Schritt – und dafür das richtige Formular.',
        'Hier findest du die Formulare, die du für die Zusammenarbeit mit KE!N EINZELFALL e.V. benötigen kannst – '
            .'zum Beispiel Schweigepflichtentbindungen, Vollmachten, Mitgliedsanträge und weitere Unterlagen.',
        'Damit du nicht lange suchen musst, findest du hier alles übersichtlich und direkt zugänglich.',
    ];

    public const LISTE_TITEL = 'Formulare zum Herunterladen';

    /** KEV-250: von uns, zum Gegenlesen in der Übergabe-Checkliste. */
    public const ANLEITUNG_TITEL = 'So nutzt du die Formulare';

    public const ANLEITUNG = [
        '*Am PC ausfüllen:* Lade das Formular herunter und öffne es mit einem PDF-Programm, zum Beispiel dem '
            .'kostenlosen Adobe Acrobat Reader, oder direkt im Browser. Formulare mit dem Zusatz „ausfüllbar“ '
            .'haben Felder zum Hineinschreiben und Kästchen zum Ankreuzen. Speichere das ausgefüllte Formular und '
            .'schick es uns.',
        '*Auf Papier ausfüllen:* Jedes Formular lässt sich auch ausdrucken und von Hand ausfüllen.',
        '*Unterschrift:* Vollmacht und Schweigepflichtentbindung brauchen deine eigenhändige Unterschrift, sonst '
            .'nehmen Behörden, Ärztinnen und Ärzte sie oft nicht an. Fülle sie am PC aus, drucke sie aus, '
            .'unterschreibe und schick uns einen Scan oder ein Foto.',
        '*Schicken:* per E-Mail an *kontakt@kein-einzelfall.de* oder per Post an KE!N EINZELFALL e.V., '
            .'Schiffbeker Höhe 30, 22119 Hamburg. In den Formularen stehen oft Gesundheitsdaten, und eine E-Mail '
            .'ist nicht verschlüsselt. Wenn dir das lieber ist, nimm die Post.',
    ];

    /**
     * Die Listen. Taddis Dateien, im Ordner public/dokumente/2026/10. Die
     * Satzung ist die von der Seite „Satzung“.
     *
     * @return list<array{titel: string, dokumente: list<array{titel: string, url: string}>}>
     */
    public static function listen(): array
    {
        $d = fn (string $titel, string $datei) => ['titel' => $titel, 'url' => '/dokumente/2026/10/'.$datei];

        return [
            ['titel' => 'Mitgliedschaft', 'dokumente' => [
                $d('Antrag auf Mitgliedschaft (ausfüllbar)', 'Mitgliedsantrag.pdf'),
                $d('Ausfüllhilfe zum Mitgliedsantrag', 'Mitgliedsantrag-Ausfuellhilfe.pdf'),
                $d('Mitglieds- und Beitragsordnung', 'Mitglieds-und-Beitragsordnung.pdf'),
                ['titel' => 'Satzung', 'url' => '/dokumente/2026/05/26.04.02.-Satzung-II.pdf'],
            ]],
            ['titel' => 'Vollmacht und Schweigepflichtentbindung', 'dokumente' => [
                $d('Vollmacht und Schweigepflichtentbindung in einem (ausfüllbar)', 'Vollmacht-und-Schweigepflichtentbindung.pdf'),
                $d('Vollmacht (ausfüllbar)', 'Vollmacht.pdf'),
                $d('Schweigepflichtentbindung (ausfüllbar)', 'Schweigepflichtentbindung.pdf'),
            ]],
            ['titel' => 'Freigaben für Bilder, Texte und Tonaufnahmen', 'dokumente' => [
                $d('Freigabe für Bilder (ausfüllbar)', 'Freigabe-Bilder.pdf'),
                $d('Freigabe für Texte (ausfüllbar)', 'Freigabe-Texte.pdf'),
                $d('Freigabe für Tonaufnahmen (ausfüllbar)', 'Freigabe-Tonaufnahmen.pdf'),
                $d('Freigabe zur Verarbeitung und Speicherung deiner Unterlagen (ausfüllbar)', 'Freigabe-Verarbeitung-und-Speicherung.pdf'),
            ]],
            ['titel' => 'Gruppen und Veranstaltungen', 'dokumente' => [
                $d('Gruppenregeln', 'Gruppenregeln.pdf'),
                $d('Teilnahme- und Verschwiegenheitserklärung', 'Teilnahme-und-Verschwiegenheitserklaerung.pdf'),
                $d('Teilnahme- und Verschwiegenheitserklärung für Selbsthilfegruppen', 'Teilnahme-und-Verschwiegenheitserklaerung-Selbsthilfegruppen.pdf'),
                $d('Leitfaden für Selbsthilfegruppen', 'Leitfaden-Selbsthilfegruppen.pdf'),
                $d('Leitfaden für Arbeitsgruppen', 'Leitfaden-Arbeitsgruppen.pdf'),
            ]],
        ];
    }

    /** Text, Anleitung und die vier Listen, ab Position 0. */
    public static function bausteine(): array
    {
        return [
            ['typ' => 'text', 'data' => ['absaetze' => self::ABSAETZE]],
            ['typ' => 'text', 'data' => ['titel' => self::ANLEITUNG_TITEL, 'absaetze' => self::ANLEITUNG]],
            ...array_map(fn ($liste) => ['typ' => 'download_list', 'data' => $liste], self::listen()),
        ];
    }

    public const META_DESCRIPTION = 'Formulare für die Zusammenarbeit mit KE!N EINZELFALL e.V., zum Beispiel '
        .'Schweigepflichtentbindungen, Vollmachten und Mitgliedsanträge.';

    public function run(): void
    {
        self::anlegen();
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
            foreach (self::bausteine() as $position => $baustein) {
                $seite->blocks()->create($baustein + ['position' => $position]);
            }
        }

        Titelbilder::setzen();
        Titelbilder::platzhalterSetzen();
    }
}
