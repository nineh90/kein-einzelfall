<?php

namespace Database\Seeders;

use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Ein Glossar als eigene Seite (seit KEV-155): Seite mit Einleitung und dem
 * Baustein „Glossar“, dazu die Einträge aus einer JSON-Datei, dieser Seite
 * zugeordnet. Jedes von Taddis Glossar-Dokumenten ist ein Seeder, der hiervon
 * erbt und nur seine Konstanten setzt.
 *
 * Legt nur an, was fehlt: Seite, Bausteine, Warnung und Einträge, die der
 * Verein schon geändert hat, bleiben, wie sie sind.
 */
abstract class GlossarSeite extends Seeder
{
    public const SLUG = '';

    public const TITEL = '';

    /** @var list<string> */
    public const ABSAETZE = [];

    public const META_DESCRIPTION = '';

    /** Datei unter database/seeders/data/. */
    public const DATEI = '';

    /** @var list<string> eigene Triggerwarnung vor der Seite, leer = keine */
    public const TRIGGERWARNUNG = [];

    public function run(): void
    {
        static::anlegen();
    }

    /** @return list<array{slug: string, kuerzel?: string, begriff: string, erklaerung?: string}> */
    public static function eintraege(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/'.static::DATEI)), true);
    }

    public static function anlegen(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => static::SLUG, 'locale' => 'de'],
            [
                'titel' => static::TITEL,
                'meta_title' => static::TITEL.' - Kein Einzelfall e.V.',
                'meta_description' => static::META_DESCRIPTION,
                'published_at' => now(),
            ],
        );

        if ($seite->wasRecentlyCreated) {
            $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => static::ABSAETZE]]);
            $seite->blocks()->create(['typ' => 'glossar', 'position' => 1, 'data' => []]);

            if (static::TRIGGERWARNUNG !== []) {
                $seite->update(['triggerwarnung' => static::TRIGGERWARNUNG]);
            }
        }

        foreach (static::eintraege() as $eintrag) {
            GlossaryTerm::firstOrCreate(
                ['locale' => 'de', 'slug' => $eintrag['slug']],
                [
                    'kuerzel' => $eintrag['kuerzel'] ?? null,
                    'begriff' => $eintrag['begriff'],
                    'erklaerung' => $eintrag['erklaerung'] ?? null,
                    'seite_gruppe' => $seite->uebersetzungs_gruppe,
                    'published_at' => now(),
                ],
            );
        }

        Titelbilder::setzen();
        Titelbilder::platzhalterSetzen();
    }
}
