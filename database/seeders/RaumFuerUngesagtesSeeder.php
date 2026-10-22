<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Neuer Bereich „Raum für Ungesagtes“ mit den Unterseiten „Unüberhörbar“
 * (KEV-159) und „Blickwinkel“ (KEV-158).
 *
 * Laut Taddis Drive-Ordner „4. Raum für Ungesagtes“ bekommt der Bereich eine
 * Übersicht („Deine Stimme – Raum für Ungesagtes“) und vier Unterseiten: Wall
 * of Shame, Wort für Wort, Blickwinkel und Unüberhörbar. Unüberhörbar kam als
 * erstes Ticket.
 *
 * - Die Übersicht hat noch keinen Text. Wie bei Rückblick (KEV-72) ist sie
 *   trotzdem veröffentlicht, damit der Menüpunkt nicht ins Leere führt, aber
 *   für Suchmaschinen gesperrt, bis Text da ist. Mit Taddis Bild (offene Tür).
 * - Unüberhörbar mit Taddis Text, die Stellen in Gänsefüßchen fett
 *   (*Sternchen*), Absätze von uns gesetzt. Die Adresse wird zum Mailto-Link.
 *   Mit Taddis Bild (Tonbandgerät und Kassette).
 * - Blickwinkel genauso gebaut, Bild: Staffelei mit leerer Leinwand.
 *
 * Legt nur an, was fehlt.
 */
class RaumFuerUngesagtesSeeder extends Seeder
{
    public const BEREICH_SLUG = 'raum-fuer-ungesagtes';

    public const BEREICH_TITEL = 'Raum für Ungesagtes';

    public const SLUG = 'unueberhoerbar';

    public const TITEL = 'Unüberhörbar';

    public const ABSAETZE = [
        'Manches findet seinen Ausdruck erst im Klang. Hier bekommen selbst geschriebene Lieder, vertonte '
            .'Tagebucheinträge, eigene Musikstücke und andere musikalische Werke ihren Raum.',
        'Persönlich, kreativ, leise oder kraftvoll – so unterschiedlich wie das, was dahintersteht.',
        'Möchtest du uns auch etwas mitteilen? Dann schick uns deinen Sound gern an '
            .'*sichtbarkeit@kein-einzelfall.de*.',
        'Ob anonym, unter einem Pseudonym oder mit deinem Klarnamen – du entscheidest, wie sichtbar du sein '
            .'möchtest.',
        '*Was lange ungesagt blieb, darf unüberhörbar werden.*',
    ];

    public const META_DESCRIPTION = 'Unüberhörbar: selbst geschriebene Lieder, vertonte Tagebucheinträge und '
        .'eigene Musikstücke von Betroffenen bei KE!N EINZELFALL e.V. Schick uns deinen Sound, gern auch anonym.';

    public const BLICKWINKEL_SLUG = 'blickwinkel';

    public const BLICKWINKEL_TITEL = 'Blickwinkel';

    public const BLICKWINKEL_ABSAETZE = [
        'Manches lässt sich nicht in Worte fassen – aber zeigen. Hier finden Bilder, Zeichnungen, Fotografien '
            .'und andere visuelle Arbeiten ihren Platz.',
        'Sie zeigen Erlebtes, Gefühle, Gedanken und Perspektiven auf eine ganz eigene Weise.',
        'Möchtest du uns auch etwas mitteilen? Dann schick uns deine Bilder gern an '
            .'*sichtbarkeit@kein-einzelfall.de*.',
        'Ob anonym, unter einem Pseudonym oder mit deinem Klarnamen – du entscheidest, wie sichtbar du sein '
            .'möchtest.',
        '*Manchmal zeigt ein Bild mehr von einer Wahrheit, als Worte es je könnten.*',
    ];

    public const BLICKWINKEL_META_DESCRIPTION = 'Blickwinkel: Bilder, Zeichnungen, Fotografien und andere '
        .'visuelle Arbeiten von Betroffenen bei KE!N EINZELFALL e.V. Schick uns deine Bilder, gern auch anonym.';

    public function run(): void
    {
        self::anlegen();
    }

    public static function anlegen(): void
    {
        Page::firstOrCreate(
            ['slug' => self::BEREICH_SLUG, 'locale' => 'de'],
            [
                'titel' => self::BEREICH_TITEL,
                'meta_title' => self::BEREICH_TITEL.' - Kein Einzelfall e.V.',
                'noindex' => true,
                'published_at' => now(),
            ],
        );

        self::unterseite(self::SLUG, self::TITEL, self::META_DESCRIPTION, self::ABSAETZE);
        self::unterseite(self::BLICKWINKEL_SLUG, self::BLICKWINKEL_TITEL, self::BLICKWINKEL_META_DESCRIPTION, self::BLICKWINKEL_ABSAETZE);

        Titelbilder::setzen();
    }

    /** @param  list<string>  $absaetze */
    private static function unterseite(string $slug, string $titel, string $beschreibung, array $absaetze): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => $slug, 'locale' => 'de'],
            [
                'titel' => $titel,
                'meta_title' => $titel.' - Kein Einzelfall e.V.',
                'meta_description' => $beschreibung,
                'published_at' => now(),
            ],
        );

        if ($seite->wasRecentlyCreated) {
            $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => $absaetze]]);
        }
    }
}
