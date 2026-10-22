<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Seite „Atteste“ unter Wissen (KEV-144), Text von Taddi. Den Satz in
 * Anführungszeichen wollte sie hervorgehoben (*Sternchen*), Absätze von uns
 * gesetzt.
 *
 * Darunter die 27 Attestvorlagen der Altseite (Reihe 6.5.1, Stand 01/26). Sie
 * lagen dort in der Medienbibliothek, waren aber auf keiner Seite verlinkt;
 * es sind genau die „Attestvorlagen und Formulierungshilfen“, die der Text
 * ankündigt: je ein Mustertext für die Ärztin oder den Arzt, mit Erklärung,
 * ohne persönliche Daten. Die Titel sind Taddis Kurznamen aus der Bibliothek,
 * Tippfehler korrigiert, wo nötig ergänzt („Maske“ → „Maskenpflicht,
 * Befreiung“). Reihenfolge wie bei ihr, alphabetisch.
 *
 * Legt nur an, was fehlt. Gibt es die Seite schon, bleibt sie, wie sie ist.
 */
class AttesteSeeder extends Seeder
{
    public const SLUG = 'atteste';

    public const TITEL = 'Atteste';

    public const ABSAETZE = [
        'Manchmal fehlen die Worte, um zu erklären, warum etwas nicht geht, anders gehen muss oder besondere '
            .'Unterstützung notwendig ist. Ein Attest kann dabei helfen, genau das verständlich zu machen – ohne '
            .'sich immer wieder selbst erklären zu müssen.',
        'Hier findest Du verschiedene Attestvorlagen und Formulierungshilfen für unterschiedliche Situationen '
            .'und Bedarfe, die individuell mit den behandelnden Fachpersonen abgestimmt werden können.',
        '*Damit Du nicht jedes „Warum“ selbst erklären musst.*',
    ];

    public const META_DESCRIPTION = 'Attestvorlagen und Formulierungshilfen von KE!N EINZELFALL e.V. für '
        .'unterschiedliche Situationen und Bedarfe, zum Abstimmen mit den behandelnden Fachpersonen.';

    /** Dateiname ohne „6.5.1.-Info-Attest-“ und „.pdf“ => Titel in der Liste. */
    public const VORLAGEN = [
        'Alltaghsbeeintraechtigungen' => 'Alltagsbeeinträchtigungen',
        'Ausfallgebuehr' => 'Ausfallgebühr bei kurzfristigen Terminabsagen',
        'Begeitperson' => 'Begleitperson',
        'Einzelzimmer' => 'Einzelzimmer',
        'Erwerbsminderung' => 'Erwerbsminderung',
        'Fahrdienst' => 'Fahrdienst',
        'Geschlechtsauswahl' => 'Geschlecht der behandelnden Person',
        'Gruppenunfaehigkeit' => 'Gruppenunfähigkeit',
        'Hausbesuche' => 'Hausbesuche',
        'Home-Office' => 'Homeoffice',
        'Kundenkontakt' => 'Kundenkontakt reduzieren',
        'Maske' => 'Maskenpflicht, Befreiung',
        'Nachteilsausgleich' => 'Nachteilsausgleich in Schule, Ausbildung und Studium',
        'Nachtschicht' => 'Nachtschicht, Befreiung',
        'Oeffentliche-Verkehrsmittel' => 'Öffentliche Verkehrsmittel',
        'Reha-Unfaehigkeit' => 'Reha-Unfähigkeit',
        'Teilhabebeeintraechtigng' => 'Teilhabebeeinträchtigung (GdB-Verfahren)',
        'Telefonische-Nichterreichbarkeit' => 'Telefonische Nichterreichbarkeit',
        'Traumasensibler-Umgang' => 'Traumasensibler Umgang',
        'Verhandlungsfaehigkeit' => 'Verhandlungsunfähigkeit',
        'Verminderte-Belastbarkeit' => 'Verminderte Belastbarkeit',
        'Vertrauensperson' => 'Vertrauensperson',
        'Vorsprechen' => 'Vorsprechen, Unzumutbarkeit',
        'Wegeunfaehigkeit' => 'Wegeunfähigkeit',
        'Wiedereingliederung' => 'Wiedereingliederung',
        'Wohnraum-geschuetzter-' => 'Wohnraum, geschützter',
        'Wohnungswechsel' => 'Wohnungswechsel',
    ];

    public const LISTE_TITEL = 'Attestvorlagen und Formulierungshilfen';

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
            $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => self::ABSAETZE]]);
            $seite->blocks()->create(['typ' => 'download_list', 'position' => 1, 'data' => self::dokumente()]);
        }

        Titelbilder::setzen();
    }

    /**
     * Ohne Größe: Die Liste liest sie aus der Datei, und so stimmt sie auch,
     * wenn der Verein eine Vorlage im Panel durch eine neuere ersetzt.
     *
     * @return array{titel: string, dokumente: list<array{titel: string, url: string}>}
     */
    public static function dokumente(): array
    {
        $dokumente = [];

        foreach (self::VORLAGEN as $name => $titel) {
            $dokumente[] = ['titel' => $titel, 'url' => '/dokumente/2026/07/6.5.1.-Info-Attest-'.$name.'.pdf'];
        }

        return ['titel' => self::LISTE_TITEL, 'dokumente' => $dokumente];
    }
}
