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
        'arten' => ['messe'],
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

    /**
     * Abschnitt „Presse“ (KEV-136): Presseartikel und Interviews, Einleitung
     * von Taddi. Jede Zeile ein Absatz, die Adresse wird zum Link.
     */
    public const PRESSE = [
        'titel' => 'Presse',
        'arten' => ['presse', 'interview'],
        'einleitung' => "Hier findest du eine Auswahl unserer Presseartikel, Interviews und Berichte über "
            ."KE!N EINZELFALL e.V. und unsere Arbeit.\n"
            ."Wir freuen uns über journalistisches Interesse, Anfragen und die Möglichkeit, wichtige Themen "
            ."sichtbar zu machen und Betroffenen eine Stimme zu geben.\n"
            ."Presseanfragen bitte an: presse@kein-einzelfall.de",
    ];

    /**
     * stern-Artikel zum Fonds Sexueller Missbrauch (KEV-136), Text von Taddi.
     *
     * Datum 25.06.2025: Im Ticket stand 25.05.25, Taddis Drive-Ordner heißt
     * „25.06.25 Fonds Sexueller Missbrauch“. Der Juni passt zum Ablauf (Antrags-
     * stopp im Juni 2025, Reaktion der Missbrauchsbeauftragten am 27.06.).
     * stern.de selbst ließ sich nicht abrufen. Rückfrage in der Checkliste.
     *
     * Ohne Foto: Taddis Bildschirmfoto zeigt ein Foto aus dem Artikel, „© Broeg“.
     * Das darf der Verein nur mit Erlaubnis zeigen (Checkliste); hochladen kann
     * er es dann selbst.
     */
    public const PRESSE_STERN = [
        'slug' => 'stern-fonds-sexueller-missbrauch-2025',
        'art' => 'presse',
        'titel' => 'Staat streicht Hilfen für Betroffene sexualisierter Gewalt',
        'datum' => '2025-06-25',
        'ort' => 'stern',
        'beschreibung' => '<p>Der Fonds Sexueller Missbrauch hat für viele Betroffene dort geholfen, wo andere '
            .'Systeme zu langsam, zu kompliziert oder nicht ausreichend erreichbar waren. Nun wird genau diese '
            .'Unterstützung schrittweise beendet.</p>'
            .'<p>Der Artikel im stern zeigt, welche Folgen das für Betroffene haben kann – und warum die Entscheidung '
            .'weit mehr bedeutet als eine haushaltspolitische Kürzung. Auch KE!N EINZELFALL kommt darin zu Wort und '
            .'macht deutlich, was der Fonds im Alltag tatsächlich bewirken konnte.</p>'
            .'<p>Warum der Wegfall für viele Menschen so gravierend ist und welche Lücke dadurch entsteht, lest ihr '
            .'im vollständigen Artikel.</p>',
        'link_url' => 'https://www.stern.de/panorama/wissen/fonds--sexueller-missbrauch--wird-gestrichen--die-opfer-sind-enttaeuscht-35920302.html',
        'link_text' => 'Zum Artikel im stern',
    ];

    /**
     * Besuch bei Trude Kuh (KEV-136), Text von Taddi.
     *
     * Das Video kommt später (Franzi). Bis dahin fehlt Taddis Satz „Aber
     * schaut selbst – im Video bekommt ihr den besten Eindruck davon.“, er
     * zeigte sonst auf nichts. Stattdessen der Link zum Beitrag bei Trude Kuh
     * TELEVISION; dessen Datum (09.12.2025) steht hier, Taddi nannte keins.
     */
    public const PRESSE_TRUDE_KUH = [
        'slug' => 'besuch-bei-trude-kuh-2025',
        'art' => 'interview',
        'titel' => 'Unser Besuch bei Trude Kuh',
        'datum' => '2025-12-09',
        'ort' => 'Trude Kuh TELEVISION',
        'beschreibung' => '<p>Wir hatten die Gelegenheit, unseren Verein bei Trude Kuh vorzustellen – und sind sehr '
            .'dankbar für diese besondere Erfahrung.</p>'
            .'<p>Uns erwarteten ein unglaublich netter und kompetenter Kontakt, ein professionelles Studio und eine '
            .'Atmosphäre, in der wir uns von Anfang an wohlgefühlt haben.</p>',
        'link_url' => 'https://www.trude-kuh.de/tatjana-belmar-und-petra-hildebrandt-von-kein-einzelfall-zu-gast-bei-trude-kuh/',
        'link_text' => 'Zum Beitrag bei Trude Kuh',
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

    /**
     * Hängt einen Abschnitt „Öffentlichkeitsarbeit“ unten an die deutsche
     * Seite an, wenn es noch keinen mit derselben Überschrift gibt.
     *
     * @param  array<string, mixed>  $daten
     */
    public static function abschnittAnhaengen(array $daten): void
    {
        $seite = Page::where('slug', self::SLUG)->where('locale', 'de')->first();

        if (! $seite || $seite->blocks()->where('typ', 'oeffentlichkeitsarbeit')->get()
            ->contains(fn ($b) => ($b->data['titel'] ?? null) === $daten['titel'])) {
            return;
        }

        $seite->blocks()->create([
            'typ' => 'oeffentlichkeitsarbeit',
            'position' => (int) $seite->blocks()->max('position') + 1,
            'data' => $daten,
        ]);
    }

    /**
     * Abschnitte aus der Zeit vor KEV-136 nannten ihre Art unter `art`
     * („messe“). Seitdem eine Liste unter `arten`, denn `data.art` gehört im
     * Panel dem Hinweis-Baustein. Stellt alle Sprachfassungen um.
     */
    public static function artenUmstellen(): void
    {
        foreach (\App\Models\PageBlock::where('typ', 'oeffentlichkeitsarbeit')->get() as $block) {
            $data = $block->data ?? [];

            if (! array_key_exists('art', $data)) {
                continue;
            }

            $data['arten'] = \App\Models\PageBlock::oeffentlichkeitsarbeitArten($data);
            unset($data['art']);
            $block->update(['data' => $data]);
        }
    }
}
