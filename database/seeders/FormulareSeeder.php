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
                'noindex' => true,
                'published_at' => now(),
            ],
        );

        if ($seite->wasRecentlyCreated) {
            $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => self::ABSAETZE]]);
            $seite->blocks()->create(['typ' => 'download_list', 'position' => 1, 'data' => [
                'titel' => self::LISTE_TITEL,
                'dokumente' => [],
            ]]);
        }

        Titelbilder::setzen();
        Titelbilder::platzhalterSetzen();
    }
}
