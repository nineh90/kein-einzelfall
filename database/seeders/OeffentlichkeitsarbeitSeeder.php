<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Öffentlichkeitsarbeit (KEV-129): Taddis Text als Beschreibung, darunter der
 * Baustein „Einträge mit Datum“ für Messen, Petitionen, Presseartikel und
 * Interviews. Den füllt der Verein selbst, die Liste wächst mit („Diese
 * Arbeiten erweitern sich“). Solange es keinen Eintrag gibt, zeigt die Seite
 * an der Stelle nichts.
 *
 * Die beiden Sätze, die Taddi in Anführungszeichen gesetzt hat, sind fett
 * (*Sternchen*), wie bei allen ihren Texten. Absätze von uns gesetzt, die
 * Überschrift „Einblicke“ über den Einträgen ist von uns (Panel).
 *
 * Die Seite selbst gibt es seit KEV-72 als leere Hülle im Menü, für
 * Suchmaschinen gesperrt, bis Text da ist. Jetzt ist er da. Bild bleibt der
 * Platzhalter (Taddi: „Bilder nehmen wir Platzhalter“).
 *
 * Füllt nur eine leere Seite. Hat der Verein schon etwas eingetragen, gehört
 * sie ihm.
 */
class OeffentlichkeitsarbeitSeeder extends Seeder
{
    public const SLUG = 'oeffentlichkeitsarbeit';

    public const TITEL = 'Öffentlichkeitsarbeit';

    public const ABSAETZE = [
        '*Veränderung beginnt dort, wo Themen sichtbar werden, Menschen zuhören und Stimmen gehört werden.*',
        'Mit unserer Öffentlichkeitsarbeit machen wir auf Anliegen aufmerksam, die Betroffene und ihre '
            .'Angehörigen bewegen. Wir zeigen Präsenz auf Ehrenamtsmessen, bringen mit Petitionen wichtige '
            .'Themen in die Öffentlichkeit und geben in Presseartikeln und Interviews Einblicke in unsere '
            .'Arbeit, unsere Erfahrungen und unsere Haltung.',
        'Dabei geht es uns nicht nur um Reichweite, sondern um Begegnung, Austausch und darum, Menschen '
            .'miteinander zu verbinden.',
        '*Sichtbarkeit schafft Verständnis – und Verständnis kann Veränderung möglich machen.*',
    ];

    /** Der Baustein für die einzelnen Einträge, noch ohne Einträge. */
    public const EINTRAEGE = [
        'titel' => 'Einblicke',
    ];

    public const META_DESCRIPTION = 'Öffentlichkeitsarbeit von KE!N EINZELFALL e.V.: Ehrenamtsmessen, '
        .'Petitionen, Presseartikel und Interviews. Für Sichtbarkeit, Verständnis und Veränderung.';

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
                'published_at' => now(),
            ],
        );

        $leer = $seite->blocks()->get()->every(fn ($b) => $b->typ === 'text' && blank($b->data));

        if (! $leer) {
            return;
        }

        $seite->blocks()->delete();
        $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => self::ABSAETZE]]);
        $seite->blocks()->create(['typ' => 'eintraege', 'position' => 1, 'data' => self::EINTRAEGE]);

        // Mit Text darf die Seite in die Suchmaschinen (seit KEV-72 gesperrt).
        $seite->forceFill([
            'meta_description' => $seite->meta_description ?: self::META_DESCRIPTION,
            'noindex' => false,
        ])->save();
    }
}
