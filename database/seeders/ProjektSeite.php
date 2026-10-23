<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Ein Projekt als Unterseite von „Projekte“ (seit KEV-161, KEV-162): Titel,
 * Unterzeile auf dem Bild und Taddis Text als Folge von Textabschnitten, also
 * ein Artikel mit „Auf dieser Seite“. Ihre Projektdokumentation steht als
 * Abschnitte mit dem Stand als Überzeile (`eyebrow`), neuester oben. Jedes
 * Projekt ist ein Seeder, der hiervon erbt und nur Konstanten und
 * abschnitte() setzt; ins Menü kommt es unter `children` von „Projekte“.
 *
 * Titelbild vorerst für alle Projekte das der Übersicht (Kevin, KEV-161:
 * „Nutze bei Projekte erst mal überall das gleiche Hero Bild“), siehe
 * Titelbilder::DATEI.
 *
 * Legt nur an, was fehlt.
 */
abstract class ProjektSeite extends Seeder
{
    public const SLUG = '';

    public const TITEL = '';

    public const UNTERTITEL = '';

    public const META_DESCRIPTION = '';

    /** @return list<array{eyebrow?: string, titel: string, absaetze: list<string>}> */
    abstract public static function abschnitte(): array;

    public function run(): void
    {
        static::anlegen();
    }

    public static function anlegen(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => static::SLUG, 'locale' => 'de'],
            [
                'titel' => static::TITEL,
                'untertitel' => static::UNTERTITEL,
                'meta_title' => static::TITEL.' - Kein Einzelfall e.V.',
                'meta_description' => static::META_DESCRIPTION,
                'noindex' => false,
                'published_at' => now(),
            ],
        );

        if ($seite->blocks()->doesntExist()) {
            foreach (static::abschnitte() as $position => $abschnitt) {
                $seite->blocks()->create(['typ' => 'text', 'position' => $position, 'data' => array_filter($abschnitt)]);
            }
        }

        Titelbilder::setzen();
    }
}
