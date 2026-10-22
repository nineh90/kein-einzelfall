<?php

namespace Database\Seeders;

use App\Models\OutreachItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Öffentlichkeitsarbeit (KEV-129): Taddis Text als Beschreibung, darunter der
 * Abschnitt „Messen“ (Baustein „Öffentlichkeitsarbeit“, KEV-134). Darin
 * stehen die Messen als kleine Karten; jede hat eine eigene Seite mit dem
 * ganzen Text und allen Fotos. Petitionen und Presse kommen als weitere
 * Abschnitte dazu. Die Einträge legt der Verein im Panel unter
 * „Öffentlichkeitsarbeit“ an (App\Models\OutreachItem).
 *
 * Die beiden Sätze, die Taddi in Anführungszeichen gesetzt hat, sind fett
 * (*Sternchen*), wie bei allen ihren Texten. Absätze von uns gesetzt.
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

    /** Abschnitt „Messen“: die Messen als Karten (KEV-134, Kevin: „Messen“, nicht „Einblicke“). */
    public const MESSEN = [
        'titel' => 'Messen',
        'art' => 'messe',
    ];

    /**
     * Ehrenamtsmesse Bayreuth (KEV-134): Taddis Text, der Satz in
     * Anführungszeichen fett, drei Fotos vom Stand (von Kevin).
     *
     * Datum 10.10.2026, nicht 10.10.2025 wie in der Überschrift des Tickets:
     * Die Freiwilligen-Messe Bayreuth war am Samstag, 10.10.2026, im
     * Friedrichsforum (Gemeinde Bindlach, Mainwelle); der 10.10.2025 war ein
     * Freitag, eine Messe gab es da nicht. Taddis Vorlage nennt 2026 in der
     * Zwischenzeile. Rückfrage in der Übergabe-Checkliste.
     *
     * Die Bildbeschreibungen sind von uns und nennen keine Namen: Wer auf
     * welchem Foto steht, wissen wir nicht sicher.
     */
    public const MESSE_BAYREUTH = [
        'slug' => 'ehrenamtsmesse-bayreuth-2026',
        'art' => 'messe',
        'titel' => 'Ehrenamtsmesse Bayreuth',
        'datum' => '2026-10-10',
        'ort' => 'Bayreuth',
        'beschreibung' => '<p>Nach langer Anreise präsentierte unsere 1. Vorsitzende Tatjana Belmar gemeinsam mit '
            .'unserer 2. Vorsitzenden Franziska Künstler KE!N EINZELFALL e.V. auf der Ehrenamtsmesse in Bayreuth.</p>'
            .'<p>An unserem Stand entstanden viele wertschätzende Gespräche, neue Begegnungen und schöne Kontakte. '
            .'Wir sind sehr dankbar, Teil dieser besonderen Ehrenamtsmesse gewesen zu sein und unseren Verein dort '
            .'vorstellen zu dürfen.</p>'
            .'<p><strong>Vielen Dank für die vielen offenen Gespräche und das große Interesse an unserer Arbeit.</strong></p>',
        'fotos' => [
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-bayreuth-2026-1.webp',
                'alt' => 'Zwei Frauen in weißen T-Shirts mit dem Vereinslogo stehen hinter dem Stand von '
                    .'KE!N EINZELFALL e.V. Auf dem Tisch liegen Flyer und Süßigkeiten, links steht ein Banner mit '
                    .'den Angeboten des Vereins, rechts ein Aufsteller zur Petition.',
            ],
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-bayreuth-2026-2.webp',
                'alt' => 'Eine Frau im Vereins-T-Shirt sitzt am Stand und schreibt. Vor ihr Flyer, Karten und '
                    .'Süßigkeiten, dahinter das Banner des Vereins.',
            ],
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-bayreuth-2026-3.webp',
                'alt' => 'Aufsteller mit dem Plakat zur Petition „Erst Versorgung sichern. Dann Pflegeleistungen '
                    .'verändern!“ und einem QR-Code zum Unterschreiben.',
            ],
        ],
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
        $seite->blocks()->create(['typ' => 'oeffentlichkeitsarbeit', 'position' => 1, 'data' => self::MESSEN]);

        // Mit Text darf die Seite in die Suchmaschinen (seit KEV-72 gesperrt).
        $seite->forceFill([
            'meta_description' => $seite->meta_description ?: self::META_DESCRIPTION,
            'noindex' => false,
        ])->save();
    }

    /**
     * Legt einen Eintrag an, wenn es seine Adresse noch nicht gibt. Steht
     * sie schon, bleibt der Eintrag, wie der Verein ihn gepflegt hat.
     *
     * @param  array<string, mixed>  $daten
     */
    public static function eintragAnlegen(array $daten): void
    {
        OutreachItem::firstOrCreate(
            ['slug' => $daten['slug']],
            $daten + ['published_at' => now()],
        );
    }
}
