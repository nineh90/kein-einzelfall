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
        // KEV-160: Bild von Taddi (Hand heftet Zettel an eine Wand) statt
        // unseres Holzhaus-Modells, gleicher Dateiname.
        'projekte' => 'Woran wir gerade arbeiten.',                                                // von uns
        // Die Projekte darunter tragen vorerst dasselbe Bild (KEV-161, Kevin:
        // „Nutze bei Projekte erst mal überall das gleiche Hero Bild“). Ihre
        // Unterzeile setzt der jeweilige Seeder (ProjektSeite).
        'anuas' => null,
        '109-sgg-gegengutachten' => null,
        'kein-einzelfall-im-dialog' => 'Wissenschaftliche Erkenntnisse und gelebte Erfahrung im Austausch.', // von uns
        'soziales-entschaedigungsrecht' => 'Hilfe für Menschen, die durch eine Gewalttat geschädigt wurden.', // von uns
        'traumafolgestoerungen-verstehen' => 'Was eine Traumafolgestörung ist – und wie man passende Hilfe findet.', // von uns
        'trauma-bindung-und-beziehung' => 'Warum ein Trauma Beziehungen verändert – und was dabei hilft.', // von uns
        // KEV-80, Bild von Taddi. Die Seite ist noch Entwurf, bis der Text kommt.
        // KEV-213: Umbruch nach dem Komma, Wunsch von Taddi.
        'beschwerdemanagement' => "Deine Rückmeldung hilft uns,\nbesser zu werden.",              // von uns
        // KEV-81, Bild von Taddi. Seit KEV-105 mit Text; die Unterzeile ist
        // der ausgeschriebene Name.
        // KEV-220: Der Name steht seitdem groß als Titel, darunter Taddis Zusatz.
        'gremium-ukfb' => 'Assoziiertes Fachgremium',
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
        // KEV-127: Bild von Taddi statt Platzhalter, Laptop mit Videotreffen.
        // Ohne Unterzeile, der Verein hat keine geschickt.
        'gruppen-und-veranstaltungen' => null,
        // KEV-149: Bild von Taddi statt Platzhalter, Megafon auf Holztisch.
        // Ohne Unterzeile, der Verein hat keine geschickt.
        'oeffentlichkeitsarbeit' => null,
        // KEV-238: Taddis Bilder der drei Themen, Motiv links, Fläche rechts
        'ehrenamtsmessen' => null,
        'petitionen' => null,
        'presse' => null,
        // KEV-147: Bild von Taddi statt Platzhalter, Leinenbücher mit „2025“.
        // Ohne Unterzeile, der Verein hat keine geschickt.
        'taetigkeits-und-jahresberichte' => null,
        // Bilder von Taddi aus ihrem Drive-Ordner „1. Verein“ statt
        // Platzhalter: Schutz- und Wertekonzept KEV-145, Red Flags KEV-146.
        // Ohne Unterzeile, Red Flags seit KEV-210 mit Taddis zweiter Zeile.
        'schutz-und-wertekonzept' => null,
        'red-flags' => 'Red Flags & Green Lights',
        // KEV-144: Bild von Taddi (Drive-Ordner „3. Wissen“), Papierstapel
        // mit Stempel. Ohne Unterzeile.
        'atteste' => null,
        // KEV-153: Bild von Taddi (Drive-Ordner „3. Wissen“, „Abkürzungen
        // SER“), Zettelkasten. Unterzeile seit KEV-255 aus dem Ticket.
        'glossar' => 'Damit aus Buchstaben wieder Bedeutung wird.',
        // KEV-151: Bild von Taddi („4. Formulare“), Formular mit Vereinslogo
        // und Füller. Unterzeile seit KEV-248 aus dem Ticket.
        'formulare' => 'Übersichtlich an einem Ort',
        // KEV-152: Bild von Taddi („5. Fristen“), Sanduhr vor Akten.
        // Unterzeile seit KEV-251 aus dem Ticket.
        'fristen' => 'Fristen verstehen. Möglichkeiten erkennen. Rechtzeitig handeln.',
        // KEV-137: Bild von Taddi, aufgeschlagenes Fotoalbum mit Bildern von
        // Veranstaltungen. Ohne Unterzeile, ihre Einleitung steht darunter.
        'rueckblick' => null,
        // KEV-154: Bild von Taddi („3. Abkürzungen sexueller Kontext“),
        // Buchstabenwürfel.
        // KEV-256: Unterzeile aus dem Ticket.
        'abkuerzungen-im-sexuellen-kontext' => 'Kürzel verstehen, benennen und darüber sprechen.',
        // KEV-258: Bild von Taddi („Behörden und Bescheide“), Schreiben und
        // Holzstempel, statt Platzhalter. Unterzeile aus dem Ticket.
        'behoerden-und-bescheide' => 'Verstehen, was entschieden wurde',
        // KEV-259: Bild von Taddi („Gericht und Verfahren“), Richterhammer und
        // Akten. Unterzeile aus dem Ticket.
        'gericht-und-verfahren' => 'Damit juristische Begriffe verständlicher werden',
        // KEV-260: Bild von Taddi („Medizinische Berichte & Gutachten“), Mappe
        // mit Stethoskop. Unterzeile aus dem Ticket.
        'medizinische-berichte-und-gutachten' => 'Befunde verstehen. Gutachten besser einordnen.',
        // KEV-261: Bild von Taddi („Sozialrecht & Hilfesystem“), Kasten mit
        // Registermappen. Unterzeile aus dem Ticket.
        'sozialrecht-und-hilfesystem' => 'Leistungen verstehen. Zuständigkeiten einordnen. Unterstützung finden.',
        // KEV-159: Bilder von Taddi (Drive-Ordner „4. Raum für Ungesagtes“).
        // Offene Tür für den Bereich, Tonbandgerät für Unüberhörbar.
        //
        // Unterzeilen seit KEV-156: „Deine Stimme“ aus Taddis Titel des
        // Bereichs („Raum für Ungesagtes – Deine Stimme“), bei den vier
        // Kategorien ihre Beschreibung aus dem Ticket („Musik: Musikstücke von
        // Betroffenen“). Sie stehen auch auf den Kacheln der Übersicht.
        'raum-fuer-ungesagtes' => 'Deine Stimme',
        'unueberhoerbar' => 'Musikstücke von Betroffenen',
        // KEV-158: Bild von Taddi, Staffelei mit leerer Leinwand.
        'blickwinkel' => 'Bilder von Betroffenen',
        // KEV-157: Bild von Taddi „1 Wall of Shame“, Stuhl vor einer Wand
        // mit Schatten von Sprechblasen. Zu ihr hat Taddi keine Beschreibung
        // geschickt, die Unterzeile ist aus ihrer Einleitung.
        'wall-of-shame' => 'Sätze, die Betroffene zu hören bekommen haben',                      // von uns
        // KEV-156: Bild von Taddi „2. Wort für Wort“, aufgeschlagenes Buch auf
        // zwei alten Büchern.
        'wort-fuer-wort' => 'Gedichte, Briefe und Texte von Betroffenen',
    ];

    /**
     * Seiten, deren Bild nicht <slug>.webp heißt, weil sie es sich teilen.
     *
     * @var array<string, string>
     */
    public const DATEI = [
        'istanbul-konvention' => 'ordner-mit-logo.webp',
        'kinderkodex' => 'ordner-mit-logo.webp',
        // KEV-161: die Projekte vorerst mit dem Bild der Übersicht
        'anuas' => 'projekte.webp',
        '109-sgg-gegengutachten' => 'projekte.webp',
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
        // KEV-254: Im Drive liegt für die Übersicht noch keins.
        'klartext-statt-fachsprache',
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
     *  - Mitgliedschaft: der Antrag mit Stift, fast so hoch wie das Bild
     *    (KEV-61). Seit KEV-91 auf 35 % statt 0 %: Oben ausgerichtet fehlte
     *    ab 1440 px der Stift. Bei 35 % bleiben Logo und „Antrag auf
     *    Mitgliedschaft“ ganz, darüber darf es nicht gehen, sonst schneidet
     *    16:9 das Logo an. Ganz passt der Antrag in keinen breiten Kopf.
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
     *  - Öffentlichkeitsarbeit: Megafon auf Holztisch, Trichter zwischen 26
     *    und 74 % der Höhe (KEV-149). Unten ausgerichtet fehlte ab 1440 px
     *    der obere Rand des Trichters, bei 2560 px der halbe Trichter.
     *  - Tätigkeits- und Jahresberichte: Leinenbücher (KEV-147). Das Bild ist
     *    rechts um Wand verlängert (2,58 : 1), die Bücher stehen zwischen 20
     *    und 82 % der Höhe; mit 60 % bleiben sie von 1024 bis 2560 px ganz.
     *  - Red Flags: zwei Türen, eine offen (KEV-146).
     *    Rechts um Wand verlängert wie die Jahresberichte; Türen zwischen 12
     *    und 74 % der Höhe, mit 30 % ganz von 1024 bis 2560 px.
     *  - Schutz- und Wertekonzept: Holzdach über Holzfiguren (KEV-145),
     *    zwischen 36 und 76 % der Höhe. Unten ausgerichtet fehlte ab 1440 px
     *    das Dach.
     *  - Atteste: Papierstapel und Holzstempel (KEV-144). Rechts um Wand
     *    verlängert wie die Jahresberichte, Motiv zwischen 36 und 90 % der
     *    Höhe; mit 85 % bleibt über dem Stempel auch bei 2560 px Luft.
     *  - Glossar: Zettelkasten aus Holz (KEV-153). Rechts um Wand verlängert,
     *    Karten ab 31 %, Kastenboden bei 97 % der Höhe; mit 90 % bleiben
     *    Karten und Griff auch bei 2560 px ganz.
     *  - Raum für Ungesagtes: offene Tür mit Licht (KEV-159). Rechts um Wand
     *    verlängert, Türrahmen ab 3 %, Türschwelle bei 78 % der Höhe; mit
     *    25 % bleibt die Tür von oben bis zur Schwelle zu sehen.
     *  - Unüberhörbar: Tonbandgerät und Kassette (KEV-159). Rechts um Wand
     *    verlängert, Motiv zwischen 14 und 89 % der Höhe, mit 60 % fast ganz.
     *  - Blickwinkel: Staffelei mit leerer Leinwand (KEV-158). Rechts um Wand
     *    verlängert; Staffelei von 3 bis 92 % der Höhe, zu hoch für jeden
     *    Kopf. Mit 35 % bleibt die Leinwand ganz, unten fehlen bei 2560 px
     *    die Beinenden.
     *  - Wall of Shame: Stuhl, darüber Schatten von Sprechblasen (KEV-157).
     *    Rechts um Wand verlängert; Schatten ab 2 %, Stuhlbeine bei 83 % der
     *    Höhe. Mit 45 % bleibt der Stuhl ganz, oben fehlt bei 2560 px ein
     *    Stück der obersten Sprechblase.
     *  - Wort für Wort: aufgeschlagenes Buch auf zwei alten Büchern (KEV-156).
     *    Rechts um Wand verlängert, Bücher zwischen 47 und 86 % der Höhe.
     *  - Abkürzungen im sexuellen Kontext: Kiste mit Buchstabenwürfeln
     *    (KEV-154). Rechts um Wand verlängert, Kiste ab 15 %, Würfel bis 97 %
     *    der Höhe; ausgerichtet auf 85 %.
     *  - Behörden & Bescheide: Schreiben mit Holzstempel (KEV-258). Rechts
     *    um Fläche verlängert, Stempel ab 7 %, Blatt bis 93 % der Höhe;
     *    Mit 10 % bleibt der Stempel bis 2560 px ganz, unten fällt dort ein
     *    Teil des Blatts weg.
     *  - Gericht & Verfahren: Richterhammer auf Block, davor Akten (KEV-259).
     *    Rechts um Wand verlängert, Hammer ab 30 %, Akten bis 85 % der Höhe;
     *    Mit 60 % bleibt der Hammer auch bei 2560 px ganz.
     *  - Medizinische Berichte & Gutachten: Mappe mit Stethoskop (KEV-260).
     *    Rechts um Fläche verlängert, Stethoskop ab 11 %, Mappe bis 95 % der
     *    Höhe. Mit 20 % bleibt das Stethoskop auch bei 2560 px ganz, unten
     *    fällt dort die Mappe weg.
     *  - Sozialrecht & Hilfesystem: Kasten mit Registermappen (KEV-261).
     *    Rechts um Wand verlängert, Register ab 26 % der Höhe, der Kasten
     *    läuft unten aus dem Bild. Mit 40 % bleiben die oberen Register
     *    auch bei 2560 px ganz, unten fällt dort nur Kasten weg.
     *  - Fristen: Sanduhr vor Aktenstapel (KEV-152). Rechts um Wand
     *    verlängert, Sanduhr zwischen 18 und 86 % der Höhe; 65 %.
     *  - Wissen: Bücherstapel und aufgeschlagenes Buch (KEV-143, Bild von
     *    Taddi statt unseres Leinenbands). Rechts um Wand verlängert, Bücher
     *    zwischen 25 und 84 % der Höhe; mit 60 % auch bei 1920 px ganz.
     *  - Formulare: Formular mit Vereinslogo und Füller (KEV-151). Rechts um
     *    Wand verlängert, Blatt von 12 bis 92 % der Höhe; mit 40 % bleibt
     *    oben das Logo auf dem Formular zu sehen.
     *  - Projekte: Hand heftet einen Zettel an eine Wand voller Zettel und
     *    Fäden (KEV-160). Rechts um Wand verlängert, Zettel über die ganze
     *    Höhe, die Hand zwischen 23 und 55 %; mit 40 % bleibt sie im Bild.
     *  - Rückblick: aufgeschlagenes Fotoalbum, davor ein loses Foto (KEV-137).
     *    Rechts um Wand verlängert, Album zwischen 7 und 81 %, das lose Foto
     *    bis 97 % der Höhe; mit 40 % bleibt das Album ganz.
     *  - Ehrenamtsmessen, Petitionen, Presse (KEV-238): Messestand, Blatt mit
     *    Unterschriften, Zeitungsstapel mit Logo, alle links. Das Logo sitzt
     *    zwischen 20 und 50 % der Höhe, die Unterschriften zwischen 10 und
     *    95 %, der Stand füllt die Höhe. Unten ausgerichtet fehlten Logo und
     *    die obersten Unterschriften.
     *  - Bürokratie-Labyrinth (Gruppe, KEV-228): Labyrinth aus Papier links,
     *    zwischen 17 und 75 % der Höhe; mit 45 % bleibt es ganz.
     *  - Seelenfarben (Gruppe, KEV-229): Pinsel und Aquarell-Kreis oben links,
     *    der Kreis zwischen 12 und 75 % der Höhe. Unten ausgerichtet fehlte
     *    ab 1400 px sein oberer Rand.
     *  - Veranstaltungen: zwei Holzstühle und ein Tisch mit zwei Mikrofonen,
     *    zwischen 41 und 84 % der Höhe (KEV-148). Unten ausgerichtet fehlten
     *    ab 1440 px die Stuhllehnen, bei 2560 px auch die Mikrofone.
     *
     * @var array<string, int>
     */
    public const FOKUS = [
        self::ORDNER.'/mitgliedschaft.webp' => 35,
        self::ORDNER.'/arbeitsgruppen.webp' => 50,
        self::ORDNER.'/beschwerdemanagement.webp' => 35,
        self::ORDNER.'/gremium-ukfb.webp' => 70,
        self::ORDNER.'/landesstellen.webp' => 50,
        self::ORDNER.'/ordner-mit-logo.webp' => 42,
        self::ORDNER.'/gruppen-und-veranstaltungen.webp' => 40,
        self::ORDNER.'/oeffentlichkeitsarbeit.webp' => 50,
        self::ORDNER.'/veranstaltungen.webp' => 75,
        self::ORDNER.'/taetigkeits-und-jahresberichte.webp' => 60,
        self::ORDNER.'/red-flags.webp' => 30,
        self::ORDNER.'/schutz-und-wertekonzept.webp' => 65,
        self::ORDNER.'/atteste.webp' => 85,
        self::ORDNER.'/glossar.webp' => 90,
        self::ORDNER.'/raum-fuer-ungesagtes.webp' => 25,
        self::ORDNER.'/unueberhoerbar.webp' => 60,
        self::ORDNER.'/blickwinkel.webp' => 35,
        self::ORDNER.'/wall-of-shame.webp' => 45,
        self::ORDNER.'/wort-fuer-wort.webp' => 80,
        self::ORDNER.'/abkuerzungen-im-sexuellen-kontext.webp' => 85,
        self::ORDNER.'/sozialrecht-und-hilfesystem.webp' => 40,
        self::ORDNER.'/medizinische-berichte-und-gutachten.webp' => 20,
        self::ORDNER.'/gericht-und-verfahren.webp' => 60,
        self::ORDNER.'/behoerden-und-bescheide.webp' => 10,
        self::ORDNER.'/fristen.webp' => 65,
        self::ORDNER.'/formulare.webp' => 40,
        self::ORDNER.'/wissen.webp' => 60,
        self::ORDNER.'/rueckblick.webp' => 40,
        self::ORDNER.'/projekte.webp' => 40,
        self::ORDNER.'/gruppe-seelenfarben.webp' => 40,
        self::ORDNER.'/gruppe-buerokratie-labyrinth.webp' => 45,
        self::ORDNER.'/ehrenamtsmessen.webp' => 55,
        self::ORDNER.'/petitionen.webp' => 40,
        self::ORDNER.'/presse.webp' => 40,
    ];

    /**
     * Bilder mit dem Motiv in der oberen Hälfte. Auf dem Handy steht der
     * Text sonst oben auf dem Bild und läge über dem Motiv; bei diesen
     * steht er unten (KEV-79).
     *
     * Ordner mit Logo (Istanbul-Konvention, Kinderkodex): Das Logo sitzt
     * zwischen 28 und 62 % der Höhe, darunter nur Ordnerkante und Tisch.
     *
     * Verein, Über uns: Oben steht das aufgelegte Logo (LOGO_DARAUF), der
     * Text darunter.
     *
     * Mitgliedschaft (KEV-91): Der Antrag füllt auf dem Handy die ganze Höhe,
     * oben lag der Titel auf „Antrag auf Mitgliedschaft“. Unten liegt er nur
     * auf dem weich gezeichneten Kleingedruckten.
     *
     * Projekte (KEV-160): Die Hand mit dem Zettel sitzt oben links, zwischen
     * 23 und 55 % der Höhe. Oben verschwand sie auf dem Handy unter dem Titel.
     */
    public const TEXT_UNTEN = [
        self::ORDNER.'/ordner-mit-logo.webp',
        self::ORDNER.'/verein.webp',
        self::ORDNER.'/ueber-uns-vorstand-und-team.webp',
        self::ORDNER.'/mitgliedschaft.webp',
        self::ORDNER.'/projekte.webp',
    ];

    /**
     * Bilder, auf die der Seitenkopf das echte Vereinslogo legt (KEV-75).
     *
     * Taddis Bild für /verein hatte das Logo eingebaut. Je nach Breite schnitt
     * der Kopf es ab oder der Text lag darauf. Jetzt ist das Bild nur noch der
     * Hintergrund (Logo herausgerechnet), und das Logo steht als eigenes
     * Element darüber: ab „md“ links neben dem Text, auf dem Handy über ihm.
     * Beide stehen im Seitenfluss und können sich so nicht überlagern.
     *
     * Über uns genauso (KEV-181, Taddi: „etwas tiefer, mittig“): Dort saß das
     * Logo aufgemalt links oben im Bild und wurde je nach Breite angeschnitten.
     */
    public const LOGO_DARAUF = [
        self::ORDNER.'/verein.webp',
        self::ORDNER.'/ueber-uns-vorstand-und-team.webp',
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
    /**
     * Waagerechter Ausschnitt in Prozent, wo nicht der linke Rand (0 %). Wirkt
     * nur, wo das Bild breiter ist als der Kopf, also auf Handy und Tablet.
     *
     * Red Flags (KEV-210): Das Bild ist links um Wand verlängert, damit die
     * Türen auf dem Desktop weiter rechts stehen (Taddi: „der helle Bereich ist
     * sehr groß“). Auf dem Handy zeigte der linke Rand dann nur noch die Wand.
     *
     * @var array<string, int>
     */
    public const FOKUS_X = [
        self::ORDNER.'/red-flags.webp' => 40,
    ];

    public static function fokus(?string $bild): string
    {
        return (self::FOKUS_X[$bild] ?? 0).'% '.(self::FOKUS[$bild] ?? 100).'%';
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

            // Ohne Unterzeile (null) nichts zu tun. PHP 8.5 meldet null als
            // Schlüssel im Wörterbuch sonst als veraltet.
            if ($untertitel === null) {
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
