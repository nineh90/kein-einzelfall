<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * „Wall of Shame“ unter „Raum für Ungesagtes“ (KEV-157).
 *
 * Aus Taddis Dokument „Text Wall of Shame“: die Einleitung (fett wie bei
 * ihr) und 145 Sätze, die Betroffene von Fachkräften, Behörden, Gerichten
 * und Gutachtern zu hören bekommen haben (data/wall-of-shame.json, ohne die
 * äußeren Anführungszeichen, die setzt die Darstellung).
 *
 * Darstellung nach ihrem Wunsch: eine rissige Wand mit Papierstapel, jeder
 * Klick auf den Stapel zeigt den nächsten Satz und setzt einen Zählstrich,
 * immer vier und ein Querstrich wie im Logo (Baustein `wall_of_shame`,
 * resources/js/wall-of-shame.js). Die Wand ist ihr Bild „Verwitterte Wand
 * mit altem Papierstapel“, das Titelbild ihr „1 Wall of Shame“.
 *
 * Korrigiert: „Gutachter“ → „Gutachtern“, „. . .“ → „…“.
 *
 * Legt nur an, was fehlt.
 */
class WallOfShameSeeder extends Seeder
{
    public const SLUG = 'wall-of-shame';

    public const TITEL = 'Wall of Shame';

    public const ABSAETZE = [
        'Manche Sätze vergisst man nicht. Nicht, weil sie helfen – sondern weil sie verletzen, entwerten, '
            .'beschämen oder einfach sprachlos machen …',
        'Auf unserer *Wall of Shame* sammeln wir Aussagen, die Betroffene von Fachkräften, Behörden, Gerichten, '
            .'Gutachtern, Anwälten oder anderen Stellen tatsächlich zu hören bekommen haben.',
        'Wir machen diese Sätze sichtbar, weil sie zeigen, wo Verständnis fehlt, Vorurteile wirken und Worte '
            .'zusätzliche Verletzungen hinterlassen können.',
        '*Nicht die Betroffenen sollten sich für diese Aussagen schämen müssen!*',
    ];

    /** Steht auf dem Zettel, wenn alle Sätze gelesen sind. */
    public const SCHLUSS = 'Nicht die Betroffenen sollten sich für diese Aussagen schämen müssen!';

    public const WAND = '/img/wall-of-shame/wand.webp';

    public const META_DESCRIPTION = 'Wall of Shame: Sätze, die Betroffene von Fachkräften, Behörden, Gerichten '
        .'und Gutachtern zu hören bekommen haben. Gesammelt von KE!N EINZELFALL e.V.';

    public function run(): void
    {
        self::anlegen();
    }

    /** @return list<string> */
    public static function saetze(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/wall-of-shame.json')), true);
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
            $seite->blocks()->create(['typ' => 'wall_of_shame', 'position' => 1, 'data' => [
                'saetze' => self::saetze(),
                'bild' => self::WAND,
                'schluss' => self::SCHLUSS,
            ]]);
        }

        Titelbilder::setzen();
    }
}
