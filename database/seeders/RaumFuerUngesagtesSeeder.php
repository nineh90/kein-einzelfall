<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Neuer Bereich „Raum für Ungesagtes“ mit den Unterseiten „Unüberhörbar“
 * (KEV-159), „Blickwinkel“ (KEV-158) und „Wort für Wort“ (KEV-156).
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
 * KEV-156, Taddis Ticket zum ganzen Bereich („Raum für Ungesagtes – Deine
 * Stimme“):
 *
 * - Die Übersicht bekommt ihren Text, „Deine Stimme“ als Unterzeile auf dem
 *   Bild, und ist jetzt für Suchmaschinen offen. Darunter die vier
 *   Kategorien als Kacheln mit Bild und Aufrufzähler (Baustein
 *   `unterseiten`), Taddis Frage nach einem Zähler; ihr Aufruf mit Adresse
 *   und Schlusssatz steht darunter. Korrigiert: „etwas
 *   Sichtbar machen“ → „sichtbar“, „Texte: Gedichte, …“ → „Texte, Gedichte, …“
 *   (Bilder und Lieder sind keine Texte). Adresse und letzter Satz fett wie
 *   auf den Unterseiten.
 * - Ihre Triggerwarnung steht im Feld `triggerwarnung` der Übersicht und gilt
 *   damit für den ganzen Bereich (App\Support\Bereichswarnung).
 * - Wort für Wort, ihre Kategorie 2, mit ihrem Bild (aufgeschlagenes Buch).
 *   Text fehlt noch: veröffentlicht, aber noindex, wie vorher die Übersicht.
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

    public const WORT_SLUG = 'wort-fuer-wort';

    public const WORT_TITEL = 'Wort für Wort';

    public const WORT_META_DESCRIPTION = 'Wort für Wort: Gedichte, Briefe und Texte von Betroffenen bei '
        .'KE!N EINZELFALL e.V.';

    /**
     * KEV-156: Taddis Text für die Übersicht, Absätze von uns gesetzt.
     *
     * In zwei Teilen, die Kacheln dazwischen: In einem Stück hätte der
     * Textbaustein nach vier Absätzen eingeklappt, und gerade Adresse und
     * Schlusssatz lägen hinter „Weiterlesen“. Die Reihenfolge ist ihre.
     */
    public const BEREICH_ABSAETZE = [
        'Für alles, was zu lange keinen Platz hatte.',
        'Manches bleibt verborgen, weil die richtigen Worte fehlen. Manches, weil niemand hinsieht. Und '
            .'manches, weil es lange keinen Ort gab, an dem es sein durfte.',
        'Texte, Gedichte, Briefe, Bilder, Lieder etc.',
        'Hier geben wir dem Raum, was sichtbar werden möchte – Erfahrungen, Gedanken, Gefühle, Kreativität '
            .'und auch das, was Betroffenen begegnet und viel zu oft einfach stehen gelassen wird.',
    ];

    /** Nach den Kacheln: der Aufruf und Taddis Schlusssatz. */
    public const BEREICH_AUFRUF = [
        'Möchtest du uns auch etwas sichtbar machen? Dann schicke uns eine Mail an '
            .'*sichtbarkeit@kein-einzelfall.de*.',
        'Ob anonym, unter einem Pseudonym oder mit deinem Klarnamen – du entscheidest, wie sichtbar du sein '
            .'möchtest.',
        '*Was zu lange keinen Platz hatte, darf hier einen bekommen.*',
    ];

    public const BEREICH_META_DESCRIPTION = 'Raum für Ungesagtes: Wall of Shame, Texte, Bilder und Musik von '
        .'Betroffenen bei KE!N EINZELFALL e.V. Für alles, was zu lange keinen Platz hatte.';

    /** Überschrift über den Kacheln. Von uns. */
    public const KACHELN_TITEL = 'Was du hier findest';

    /** KEV-156: Taddis Triggerwarnung für den Bereich, wörtlich. */
    public const TRIGGERWARNUNG = [
        'Auf dieser Seite kommen Inhalte vor, die sich mit sexualisierter Gewalt, Missbrauch, Gewalt, Trauma, '
            .'psychischer Belastung, Suizidalität, Selbstverletzung oder anderen belastenden Erfahrungen '
            .'beschäftigen.',
        'Die Beiträge stammen aus persönlichen Erfahrungen und können sehr direkt, emotional oder bildhaft sein.',
        'Bitte achte auf dich und entscheide selbst, ob und wann du diese Inhalte lesen, ansehen oder anhören '
            .'möchtest.',
    ];

    public function run(): void
    {
        self::anlegen();
        self::bereichEinrichten();
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

    /**
     * KEV-156: Text, Kacheln und Triggerwarnung der Übersicht, dazu Wort für
     * Wort. Eigene Methode, weil die Spalte `triggerwarnung` erst mit KEV-156
     * kommt: anlegen() läuft schon in den älteren Migrationen.
     *
     * Die Übersicht wird nur gefüllt, solange sie leer ist (Stand KEV-159).
     * Die Warnung nur, solange keine gepflegt ist.
     */
    public static function bereichEinrichten(): void
    {
        self::unterseite(self::WORT_SLUG, self::WORT_TITEL, self::WORT_META_DESCRIPTION, [], noindex: true);

        $bereich = Page::query()
            ->where('slug', self::BEREICH_SLUG)
            ->where('locale', 'de')
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->first();

        if (! $bereich) {
            return;
        }

        if ($bereich->blocks()->doesntExist()) {
            $bereich->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => self::BEREICH_ABSAETZE]]);
            $bereich->blocks()->create(['typ' => 'unterseiten', 'position' => 1, 'data' => [
                'titel' => self::KACHELN_TITEL,
                'aufrufe' => true,
            ]]);
            $bereich->blocks()->create(['typ' => 'text', 'position' => 2, 'data' => [
                'absaetze' => self::BEREICH_AUFRUF,
                'mittig' => true,
            ]]);

            // Mit Text darf die Seite gefunden werden.
            $bereich->update([
                'noindex' => false,
                'meta_description' => $bereich->meta_description ?: self::BEREICH_META_DESCRIPTION,
            ]);
        }

        if (blank($bereich->triggerwarnung)) {
            $bereich->update(['triggerwarnung' => self::TRIGGERWARNUNG]);
        }

        Titelbilder::setzen();
    }

    /** @param  list<string>  $absaetze  leer = ohne Textbaustein, bis der Verein Text schickt */
    private static function unterseite(
        string $slug,
        string $titel,
        string $beschreibung,
        array $absaetze,
        bool $noindex = false,
    ): void {
        $seite = Page::firstOrCreate(
            ['slug' => $slug, 'locale' => 'de'],
            [
                'titel' => $titel,
                'meta_title' => $titel.' - Kein Einzelfall e.V.',
                'meta_description' => $beschreibung,
                'noindex' => $noindex,
                'published_at' => now(),
            ],
        );

        if ($seite->wasRecentlyCreated && $absaetze !== []) {
            $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => $absaetze]]);
        }
    }
}
