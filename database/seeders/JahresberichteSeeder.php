<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Seite „Tätigkeits- und Jahresberichte“ unter Verein (KEV-103), Text von
 * Taddi. Absätze von uns gesetzt.
 *
 * Berichte liegen noch keine bei uns. Laut Text beginnen sie mit 2025; die
 * PDFs hängt der Verein im Panel als Baustein „Dokumente“ an, sobald es sie
 * gibt (Rückfrage in der Übergabe-Checkliste). Bis Taddi ein Bild schickt,
 * steht der Platzhalter oben.
 *
 * Legt nur an, was fehlt. Gibt es die Seite schon, bleibt sie, wie sie ist.
 */
class JahresberichteSeeder extends Seeder
{
    public const SLUG = 'taetigkeits-und-jahresberichte';

    public const TITEL = 'Tätigkeits- und Jahresberichte';

    public const ABSAETZE = [
        'Hinter jedem Jahr stehen Menschen, Begegnungen, Ideen und viele Schritte, die gemeinsam gegangen wurden.',
        'In unseren Tätigkeits- und Jahresberichten halten wir fest, was KE!N EINZELFALL e.V. bewegt, aufgebaut '
            .'und weiterentwickelt hat. Sie geben Einblick in Projekte, Angebote, Veranstaltungen und '
            .'Entwicklungen – aber auch in die Geschichten, Erfahrungen und das Engagement, die unsere Arbeit tragen.',
        'Beginnend mit dem Jahr 2025 findest du hier unsere Berichte künftig fortlaufend ergänzt. Jeder Bericht '
            .'erzählt ein Stück unserer gemeinsamen Entwicklung – von dem, was war, was daraus entstanden ist und '
            .'was noch vor uns liegt.',
    ];

    public const META_DESCRIPTION = 'Die Tätigkeits- und Jahresberichte von KE!N EINZELFALL e.V.: Projekte, '
        .'Angebote, Veranstaltungen und Entwicklungen des Vereins, ab 2025 fortlaufend.';

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
        }

        Titelbilder::platzhalterSetzen();
    }
}
