<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Facades\Schema;

/**
 * Die Titelbilder der Inhaltsseiten (Seitenkopf, rechts neben dem Titel).
 *
 * Mit fal.ai erzeugt, nach der Bildvorgabe des Vereins (Taddi, 24.09.2026):
 * ruhige Stillleben in Sand- und Cremetönen, Licht von links oben, Motiv links
 * unten. Vorlage und Motive stehen in docs/Bildsprache.md.
 *
 * Seit KEV-36 steht das Bild im Seitenkopf als Hintergrund, und jede Seite
 * soll eins haben. Wo es noch kein eigenes gibt (Rechtstexte,
 * Barrierefreiheit, Themenseiten unter „Wissen“), steht vorerst ein
 * neutraler Platzhalter: leere Wand mit Fensterlicht, ohne Motiv. Die
 * richtigen Bilder kommen in späteren Tickets. Nur die Trigger-Warnung (ein
 * Dialog) und die Startseite (eigener Aufmacher) bleiben ohne.
 *
 * Gebraucht von der Migration (bestehende Datenbanken) und vom
 * AltseiteSeeder (frisch aufgebaute).
 */
class Titelbilder
{
    public const ORDNER = '/img/titelbilder';

    /**
     * Seiten mit Titelbild und ihre Unterzeile.
     *
     * Die Unterzeile steht auf dem Bild unter dem Titel, kurz, höchstens zwei
     * Zeilen. Wo es ging, ist es ein Satz des Vereins. Nicht aber die erste
     * Überschrift der Seite, die stünde sonst zweimal untereinander. Die
     * mit „von uns“ markierten sind neu formuliert und stehen zum Gegenlesen
     * in der Übergabe-Checkliste (A14).
     */
    /** @var array<string, ?string> */
    public const SEITEN = [
        'verein' => 'Opferhilfe für soziale Gerechtigkeit!',
        'ueber-uns-vorstand-und-team' => 'Die Menschen hinter unserer Arbeit.',                    // von uns
        'mitgliedschaft' => 'Werde Teil unseres Netzwerks!',
        'spenden' => 'Sei Du dabei, jede Unterstützung zählt, egal wie gering!',
        'unterstuetzung' => 'Wichtiges über Rechte, Anträge und unsere Arbeit.',                  // von uns
        'selbsthilfegruppen' => 'Raum für deine Geschichte – ohne Druck oder Bewertung',
        'arbeitsgruppen' => 'Du möchtest nicht nur zuschauen, sondern etwas mitgestalten?',
        'anfragen' => 'Persönlicher Austausch zu Entschädigung, Schwerbehinderung und Pflegegrad.', // von uns
        'kontakt' => 'Alle Ansprechpartner, Landesstellen und Zuständigkeiten auf einen Blick',
        'wissen' => 'Rechte, Anträge und Hilfesysteme verständlich erklärt.',                     // von uns
        'publikationen' => 'Umfragen und Veröffentlichungen des Vereins.',                         // von uns
        'veranstaltungen' => 'Wissen teilen. Erfahrungen verbinden. Gemeinsam Perspektiven schaffen.',
        'projekte' => 'Woran wir gerade arbeiten.',                                                // von uns
        'kein-einzelfall-im-dialog' => 'Wissenschaftliche Erkenntnisse und gelebte Erfahrung im Austausch.', // von uns
        'soziales-entschaedigungsrecht' => 'Hilfe für Menschen, die durch eine Gewalttat geschädigt wurden.', // von uns
        'traumafolgestoerungen-verstehen' => 'Was eine Traumafolgestörung ist – und wie man passende Hilfe findet.', // von uns
        'trauma-bindung-und-beziehung' => 'Warum ein Trauma Beziehungen verändert – und was dabei hilft.', // von uns
        // KEV-80, Bild von Taddi. Die Seite ist noch Entwurf, bis der Text kommt.
        'beschwerdemanagement' => 'Deine Rückmeldung hilft uns, besser zu werden.',                // von uns
        // KEV-81, Bild von Taddi. Seit KEV-105 mit Text; die Unterzeile ist
        // der ausgeschriebene Name.
        'gremium-ukfb' => 'Unabhängiges Kuratorium für Betroffenenexpertise',                    // von uns
        // KEV-78, Bild von Taddi. Seit KEV-104 mit Text; die Unterzeile ist
        // dessen erster Satz.
        'landesstellen' => 'KE!N EINZELFALL ist nicht nur an einem Ort zuhause.',
        // KEV-79: ein Bild von Taddi für beide, Ordner mit Vereinslogo. Ohne
        // Unterzeile, der Verein hat keine geschickt.
        'istanbul-konvention' => null,
        'kinderkodex' => null,
        // KEV-77: Bild von Taddi statt Platzhalter. Ohne Unterzeile, der
        // Verein hat keine geschickt.
        'satzung' => null,
    ];

    /**
     * Seiten, deren Bild nicht <slug>.webp heißt, weil sie es sich teilen.
     *
     * @var array<string, string>
     */
    public const DATEI = [
        'istanbul-konvention' => 'ordner-mit-logo.webp',
        'kinderkodex' => 'ordner-mit-logo.webp',
    ];

    public static function datei(string $slug): string
    {
        return self::ORDNER.'/'.(self::DATEI[$slug] ?? "{$slug}.webp");
    }

    /**
     * Setzt die Bilder. Die Übersetzungen einer Seite bekommen dasselbe —
     * gefunden über die Übersetzungsgruppe, nicht über den Slug, der je
     * Sprache ein anderer sein darf. Das Bild zeigt keinen Text.
     *
     * Schon gepflegte Titelbilder bleiben stehen.
     */
    /** Vorerst mit dem Platzhalter, bis eigene Bilder kommen (KEV-36). */
    public const PLATZHALTER_SEITEN = [
        'barrierefreiheit', 'buerokratie-labyrinth', 'das-hilfesystem', 'datenschutz',
        'erwerbsminderungsrente', 'fsm-erweitertes-hilfesystem', 'grad-der-behinderung',
        'impressum', 'opferentschaedigungsgesetz',
        'persoenliches-budget', 'pflegegrad',
        // KEV-72, bis Taddi Bilder schickt
        'gruppen-und-veranstaltungen', 'oeffentlichkeitsarbeit', 'rueckblick',
        // KEV-103, bis Taddi ein Bild schickt
        'taetigkeits-und-jahresberichte',
        // KEV-97, bis Taddi Bilder schickt
        'schutz-und-wertekonzept', 'red-flags',
    ];

    public const PLATZHALTER = self::ORDNER.'/platzhalter.webp';

    /**
     * Wie hoch im Bild das Motiv sitzt, in Prozent von oben. Danach richtet
     * der Seitenkopf das Bild aus (object-position), denn je nach Breite
     * schneidet er oben oder unten etwas ab.
     *
     * Ohne Eintrag 100 %, also unten: Dort sitzt laut Bildvorgabe das Motiv,
     * was wegfällt, ist leere Wand. Taddis eigene Bilder halten sich nicht
     * immer daran:
     *
     *  - Mitgliedschaft: der Antrag (KEV-61). Oben steht „Antrag auf
     *    Mitgliedschaft“, unten nur das (weich gezeichnete) Kleingedruckte.
     *  - Arbeitsgruppen: Tisch mit Mappe, Karten und Stiften (KEV-83). Unten
     *    steht nur eine unscharfe Stuhllehne.
     *  - Beschwerdemanagement: Briefschlitz mit Umschlag, etwas über der
     *    Mitte (KEV-80). Bei 50 % fehlte auf breiten Bildschirmen der obere
     *    Rand des Schlitzes.
     *  - Ordner mit Logo (Istanbul-Konvention, Kinderkodex, KEV-79): Das
     *    Logo sitzt bei 28 bis 62 %. Unten ausgerichtet war es ab 1440 px
     *    weg.
     *  - Gremium UKFB: runder Tisch mit Stühlen im unteren Drittel (KEV-81).
     *    Ganz unten ausgerichtet fiele auf breiten Bildschirmen die
     *    Tischplatte weg, übrig blieben Stuhlbeine.
     *  - Landesstellen: Deutschlandkarte mit Holzkugeln, fast so hoch wie
     *    das Bild (KEV-78). Unten ausgerichtet blieb auf breiten
     *    Bildschirmen nur Süddeutschland übrig.
     *
     * @var array<string, int>
     */
    public const FOKUS = [
        self::ORDNER.'/mitgliedschaft.webp' => 0,
        self::ORDNER.'/arbeitsgruppen.webp' => 50,
        self::ORDNER.'/beschwerdemanagement.webp' => 35,
        self::ORDNER.'/gremium-ukfb.webp' => 70,
        self::ORDNER.'/landesstellen.webp' => 50,
        self::ORDNER.'/ordner-mit-logo.webp' => 42,
    ];

    /**
     * Bilder mit dem Motiv in der oberen Hälfte. Auf dem Handy steht der
     * Text sonst oben auf dem Bild und läge über dem Motiv; bei diesen
     * steht er unten (KEV-79).
     *
     * Ordner mit Logo (Istanbul-Konvention, Kinderkodex): Das Logo sitzt
     * zwischen 28 und 62 % der Höhe, darunter nur Ordnerkante und Tisch.
     *
     * Verein: Oben steht das aufgelegte Logo (LOGO_DARAUF), der Text darunter.
     */
    public const TEXT_UNTEN = [
        self::ORDNER.'/ordner-mit-logo.webp',
        self::ORDNER.'/verein.webp',
    ];

    /**
     * Bilder, auf die der Seitenkopf das echte Vereinslogo legt (KEV-75).
     *
     * Taddis Bild für /verein hatte das Logo eingebaut. Je nach Breite schnitt
     * der Kopf es ab oder der Text lag darauf. Jetzt ist das Bild nur noch der
     * Hintergrund (Logo herausgerechnet), und das Logo steht als eigenes
     * Element darüber: ab „md“ links neben dem Text, auf dem Handy über ihm.
     * Beide stehen im Seitenfluss und können sich so nicht überlagern.
     */
    public const LOGO_DARAUF = [
        self::ORDNER.'/verein.webp',
    ];

    public static function logoDarauf(?string $bild): bool
    {
        return in_array($bild, self::LOGO_DARAUF, true);
    }

    public static function textUnten(?string $bild): bool
    {
        return in_array($bild, self::TEXT_UNTEN, true);
    }

    /** Für object-position: links, in der Höhe nach FOKUS. */
    public static function fokus(?string $bild): string
    {
        return '0% '.(self::FOKUS[$bild] ?? 100).'%';
    }

    /**
     * Setzt den Platzhalter, nur wo noch gar kein Titelbild steht. Ohne
     * Unterzeile: Die gehört zum eigenen Bild und kommt mit ihm.
     */
    public static function platzhalterSetzen(): void
    {
        foreach (self::PLATZHALTER_SEITEN as $slug) {
            $gruppe = Page::query()
                ->where('slug', $slug)
                ->where('locale', 'de')
                ->where('fassung', Page::FASSUNG_STANDARD)
                ->value('uebersetzungs_gruppe');

            if ($gruppe) {
                Page::where('uebersetzungs_gruppe', $gruppe)
                    ->whereNull('titelbild')
                    ->update(['titelbild' => self::PLATZHALTER]);
            }
        }
    }

    public static function platzhalterEntfernen(): void
    {
        Page::where('titelbild', self::PLATZHALTER)->update(['titelbild' => null]);
    }

    public static function setzen(): void
    {
        $woerterbuch = json_decode(
            (string) @file_get_contents(base_path('database/seeders/data/uebersetzungen.json')),
            true,
        ) ?? [];

        foreach (self::SEITEN as $slug => $untertitel) {
            $gruppe = Page::query()
                ->where('slug', $slug)
                ->where('locale', 'de')
                ->where('fassung', Page::FASSUNG_STANDARD)
                ->value('uebersetzungs_gruppe');

            if (! $gruppe) {
                continue;
            }

            Page::where('uebersetzungs_gruppe', $gruppe)
                ->whereNull('titelbild')
                ->update(['titelbild' => self::datei($slug)]);

            // Die Spalte kam später (Migration vom 24.09.2026, 14 Uhr). Läuft
            // die ältere Titelbild-Migration auf frischer Datenbank, gibt es
            // sie noch nicht; die neue ruft setzen() danach noch einmal auf.
            if (! Schema::hasColumn('pages', 'untertitel')) {
                continue;
            }

            // Die Unterzeile ist Text und je Sprache eine andere. Fehlt die
            // Übersetzung, bleibt sie leer, statt Deutsch auf eine englische
            // Seite zu schreiben.
            Page::where('uebersetzungs_gruppe', $gruppe)
                ->whereNull('untertitel')
                ->get()
                ->each(function (Page $seite) use ($untertitel, $woerterbuch) {
                    $text = $seite->locale === 'de'
                        ? $untertitel
                        : ($woerterbuch[$untertitel][$seite->locale] ?? null);

                    if ($text) {
                        $seite->update(['untertitel' => $text]);
                    }
                });
        }
    }

    /**
     * Titelbild einer Seite, die nicht aus der Seitentabelle gezeigt wird
     * (etwa /veranstaltungen, eine eigene Übersicht). Gepflegt wird es
     * trotzdem an der gleichnamigen Seite im Panel.
     */
    public static function fuer(string $slug): ?string
    {
        return Page::query()
            ->where('slug', $slug)
            ->where('locale', 'de')
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->value('titelbild');
    }

    public static function entfernen(): void
    {
        Page::where('titelbild', 'like', self::ORDNER.'/%')->update(['titelbild' => null]);
    }
}
