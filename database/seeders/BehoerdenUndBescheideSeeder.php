<?php

namespace Database\Seeders;

use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Zweites Glossar „Behörden & Bescheide“ unter „Wissen“ (KEV-155).
 *
 * Aus Taddis Dokument „3. Behörden und Bescheide“: die Einleitung (fett wie
 * bei ihr, die Adresse wird zum Mailto-Link) und 128 Begriffe mit ihrer
 * Erklärung (data/glossar-behoerden.json). Anders als im ersten Glossar sind
 * das keine Abkürzungen, sondern Wörter aus Bescheiden, jedes erklärt.
 *
 * - Die Seite trägt Einleitung und den Baustein „Glossar“; die Einträge sind
 *   ihr zugeordnet (`seite_gruppe`). So pflegt der Verein beides wie beim
 *   ersten Glossar im Panel.
 * - Bei Taddi beginnt jede Erklärung mit „Einfach erklärt:“. Das steht 128-mal
 *   gleich da und ist weggelassen; die Einleitung sagt es einmal.
 * - Ihre Reihenfolge war fast alphabetisch, das Glossar sortiert selbst.
 * - Ein eigenes Bild gibt es noch nicht, bis dahin der Platzhalter.
 *
 * Legt nur an, was fehlt.
 */
class BehoerdenUndBescheideSeeder extends Seeder
{
    public const SLUG = 'behoerden-und-bescheide';

    public const TITEL = 'Behörden & Bescheide';

    public const ABSAETZE = [
        'Hier findest du typische Begriffe aus Bescheiden und Behördenschreiben – von *Anhörung* über '
            .'*Mitwirkungspflicht* bis *Widerspruchsbescheid*.',
        'Kurz erklärt, verständlich formuliert und so aufgebaut, dass du schnell nachschauen kannst, was ein '
            .'Begriff bedeutet.',
        '*Fehlt dir hier etwas?* Dann schick uns gern eine Mail an *kontakt@kein-einzelfall.de*. Wir ergänzen '
            .'die Liste und lassen sie gemeinsam mit euch weiter wachsen.',
        '*Gemeinsam KE!N EINZELFALL*',
    ];

    public const META_DESCRIPTION = 'Behörden & Bescheide: Begriffe aus Bescheiden und Behördenschreiben, von '
        .'Anhörung bis Widerspruchsbescheid, einfach erklärt. Von KE!N EINZELFALL e.V.';

    public function run(): void
    {
        self::anlegen();
    }

    /** @return list<array{slug: string, begriff: string, erklaerung: string}> */
    public static function eintraege(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/glossar-behoerden.json')), true);
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
            $seite->blocks()->create(['typ' => 'glossar', 'position' => 1, 'data' => []]);
        }

        foreach (self::eintraege() as $eintrag) {
            GlossaryTerm::firstOrCreate(
                ['locale' => 'de', 'slug' => $eintrag['slug']],
                [
                    'begriff' => $eintrag['begriff'],
                    'erklaerung' => $eintrag['erklaerung'],
                    'seite_gruppe' => $seite->uebersetzungs_gruppe,
                    'published_at' => now(),
                ],
            );
        }

        Titelbilder::platzhalterSetzen();
    }
}
