<?php

namespace Database\Seeders;

use App\Models\OutreachItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Öffentlichkeitsarbeit (KEV-129): Taddis Text als Beschreibung, darunter
 * drei Abschnitte (Baustein „Öffentlichkeitsarbeit“): „Ehrenamtsmessen“
 * (KEV-131/134), „Petitionen“ (KEV-135) und „Presse“ (KEV-136). Darin stehen
 * die Einträge als kleine Karten; jeder hat eine eigene Seite mit dem ganzen
 * Text und allen Fotos als Galerie (KEV-132). Die Einträge legt der Verein im
 * Panel unter „Öffentlichkeitsarbeit“ an (App\Models\OutreachItem), so wie
 * Taddi es wollte: „Diese Arbeiten erweitern sich.“
 *
 * Die beiden Sätze, die Taddi in Anführungszeichen gesetzt hat, sind fett
 * (*Sternchen*), wie bei allen ihren Texten. Absätze von uns gesetzt.
 *
 * Die Seite selbst gibt es seit KEV-72 als leere Hülle im Menü, für
 * Suchmaschinen gesperrt, bis Text da ist. Jetzt ist er da. Titelbild seit
 * KEV-149 von Taddi (Megafon), vorher der Platzhalter.
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

    /**
     * Abschnitt mit den Messen als Karten. Erst „Messen“ (KEV-134, statt
     * „Einblicke“), seit KEV-131 „Ehrenamtsmessen“ wie in Taddis Ticket, mit
     * ihrer Einleitung und ihrem Bild (leerer Messestand) daneben.
     */
    public const MESSEN = [
        'titel' => 'Ehrenamtsmessen',
        'einleitung' => "Wir zeigen gern, wer wir sind, wofür wir stehen und was wir bewegen möchten. Deshalb sind "
            ."wir mit viel Engagement auf Ehrenamtsmessen unterwegs.\n"
            ."Dort möchten wir sichtbarer werden, neue Menschen kennenlernen, Kontakte knüpfen und ins Gespräch "
            ."kommen – mit allen, die sich für unsere Arbeit interessieren oder sich vielleicht sogar einbringen "
            ."möchten.\n"
            ."Wir freuen uns auf jede Begegnung, aus der etwas Neues entstehen kann.",
        'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmessen.webp',
        'arten' => ['messe'],
    ];

    /**
     * Ehrenamtsmesse Bayreuth (KEV-134): Taddis Text, der Satz in
     * Anführungszeichen fett, drei Fotos vom Stand. Seit dem 22.10.2026 aus
     * Taddis Drive-Ordner „Bayreuth“ in voller Auflösung, ohne Metadaten
     * (vorher Bildschirmfotos davon).
     *
     * Datum 10.10.2026, nicht 10.10.2025 wie in der Überschrift des Tickets:
     * Die Freiwilligen-Messe Bayreuth war am Samstag, 10.10.2026, im
     * Friedrichsforum (Gemeinde Bindlach, Mainwelle), und die iPhone-Fotos im
     * Drive sind am 10.10.2026 um 12:56 und 15:35 Uhr aufgenommen.
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
     * Ehrenamtsmesse Lübeck (KEV-133): Taddis Text, der Satz in
     * Anführungszeichen fett. „in Lübeck“ wie im Ticket (ihre Vorlage hatte
     * „in Berlin“). Datum bestätigt: die iPhone-Fotos sind vom 06.09.2026.
     *
     * Vier von sechs Bildern aus Taddis Drive-Ordner „Lübeck“, ohne
     * Metadaten: der Stand (Bildschirmfoto, nur 796 px), die Halle von oben,
     * Lübeck übers Wasser, das Gebäude. Nicht dabei: zwei fast gleiche
     * Innenaufnahmen, auf einer davon ein fremder Mann groß im Vordergrund.
     * Bildbeschreibungen von uns, ohne Namen.
     */
    public const MESSE_LUEBECK = [
        'slug' => 'ehrenamtsmesse-luebeck-2026',
        'art' => 'messe',
        'titel' => 'Ehrenamtsmesse Lübeck',
        'datum' => '2026-09-06',
        'ort' => 'Lübeck',
        'beschreibung' => '<p>Mit viel Freude und Engagement haben unsere 1. Vorsitzende Tatjana Belmar und unsere '
            .'Kassenwartin Petra Hildebrandt KE!N EINZELFALL e.V. auf der Ehrenamtsmesse in Lübeck präsentiert.</p>'
            .'<p>Für uns war die Messe eine schöne Gelegenheit, unseren Verein vorzustellen, neue Kontakte zu '
            .'knüpfen und bestehende Netzwerke weiter auszubauen.</p>'
            .'<p><strong>Wir haben viele gute Begegnungen mitgenommen und freuen uns schon jetzt auf das nächste '
            .'Jahr.</strong></p>',
        'fotos' => [
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-luebeck-2026-1.webp',
                'alt' => 'Zwei Frauen in T-Shirts mit dem Vereinslogo am Stand von KE!N EINZELFALL e.V.: Eine arbeitet '
                    .'am Laptop, die andere zeigt den Daumen nach oben. Dahinter ein Plakat mit QR-Codes zur '
                    .'Homepage und zum Spenden.',
            ],
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-luebeck-2026-2.webp',
                'alt' => 'Blick von oben in die Messehalle: viele Stände, dazwischen zahlreiche Menschen.',
            ],
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-luebeck-2026-3.webp',
                'alt' => 'Blick über das Wasser auf die Lübecker Altstadt mit zwei Kirchtürmen, am Ufer liegt ein Schiff.',
            ],
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-luebeck-2026-4.webp',
                'alt' => 'Das Gebäude der Messe von außen, mit großer Glasfront unter blauem Himmel.',
            ],
        ],
    ];

    /**
     * Ehrenamtsmesse Berlin (KEV-132): Taddis Text, ohne Hervorhebung (im
     * Ticket nichts in Anführungszeichen). Es war die 19. Berliner
     * Freiwilligenbörse am Roten Rathaus und Neptunbrunnen, laut Plakat in
     * Taddis Drive-Ordner; die Fotos sind vom 25.04.2026.
     *
     * Sechs von vierzehn Bildern aus dem Drive-Ordner „Berlin“, ohne
     * Metadaten. Viele sind Bildschirmfotos aus Instagram-Storys: Beim
     * Teamfoto sind die Ränder abgeschnitten. Nicht dabei: Plakat und Rathaus
     * mit Story-Aufklebern, drei Doppelungen, ein Foto mit gut erkennbarem
     * fremdem Gesicht, eines mit einer markierten Privatperson, eines vom Stand
     * eines anderen Ausstellers. Bildbeschreibungen von uns, ohne Namen.
     */
    public const MESSE_BERLIN = [
        'slug' => 'ehrenamtsmesse-berlin-2026',
        'art' => 'messe',
        'titel' => 'Ehrenamtsmesse Berlin',
        'datum' => '2026-04-25',
        'ort' => 'Berlin',
        'beschreibung' => '<p>Ein schöner Tag auf der Ehrenamtsmesse in Berlin!</p>'
            .'<p>An unserem Stand waren unsere 1. Vorsitzende Tatjana Belmar, unsere Kassenwartin Petra Hildebrandt '
            .'und unser Gründungsmitglied Nicole Khalil vertreten. Nicole vertritt KE!N EINZELFALL e. V. außerdem im '
            .'Berliner Landesbeirat für Menschen mit Behinderungen.</p>'
            .'<p>Wir konnten viele tolle Gespräche führen, neue Kontakte knüpfen und spannende Menschen kennenlernen. '
            .'Unser Stand befand sich draußen – und auch das Wetter hat wunderbar mitgespielt.</p>'
            .'<p>Wir nehmen viele schöne Begegnungen und neue Impulse mit und freuen uns schon jetzt darauf, im '
            .'nächsten Jahr wieder dabei zu sein!</p>',
        'fotos' => [
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-berlin-2026-1.webp',
                'alt' => 'Drei Frauen stehen lächelnd am Stand von KE!N EINZELFALL e.V., hinter ihnen die Banner des '
                    .'Vereins mit einem QR-Code zum Spenden, darüber Bäume in der Sonne.',
            ],
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-berlin-2026-2.webp',
                'alt' => 'Besucherinnen und Besucher im Gespräch am Stand, dahinter eine Frau am Tisch und die Banner '
                    .'des Vereins.',
            ],
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-berlin-2026-3.webp',
                'alt' => 'Der Tisch am Stand mit Flyern des Vereins und kleinen Steinen, eine Hand ordnet die Auslage.',
            ],
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-berlin-2026-4.webp',
                'alt' => 'Flyer des Vereins mit seinen Angeboten, Kontaktdaten und QR-Codes.',
            ],
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-berlin-2026-5.webp',
                'alt' => 'Das Rote Rathaus in Berlin unter blauem Himmel, davor viele Menschen auf dem Platz.',
            ],
            [
                'bild' => '/img/oeffentlichkeitsarbeit/ehrenamtsmesse-berlin-2026-6.webp',
                'alt' => 'Menschen auf dem Messegelände vor der Marienkirche, im Hintergrund ein grünes Zelt.',
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

    /**
     * Abschnitt „Petitionen“ (KEV-135), Text von Taddi, mit ihrem Bild (Unter-
     * schriftenliste) neben der Einleitung. Zwei Untergruppen wie bei ihr:
     * selbst angestoßene und unterstützte Petitionen. „Initiierte“ statt
     * „Initiitiert“, „Unterstützte Petitionen“ in der Mehrzahl wie die erste
     * Gruppe. Zwischen Messen und Presse, wie in Taddis Gliederung.
     */
    public const PETITIONEN = [
        'titel' => 'Petitionen',
        'einleitung' => "Deine Stimme zählt – gemeinsam KE!N EINZELFALL.\n"
            ."Mit unseren Petitionen machen wir auf Missstände aufmerksam, geben wichtigen Anliegen mehr Gewicht "
            ."und setzen uns für Veränderung ein.\n"
            ."Wenn Du unsere Haltung teilst, werde mit Deiner Unterschrift Teil einer gemeinsamen Stimme für Veränderung.",
        'bild' => '/img/oeffentlichkeitsarbeit/petitionen.webp',
        'gruppen' => [
            [
                'titel' => 'Initiierte Petitionen',
                'einleitung' => 'Manchmal reicht es nicht, auf Veränderung zu hoffen – manchmal muss man sie selbst '
                    .'anstoßen. Hier findest Du die Petitionen, die wir als KE!N EINZELFALL e. V. selbst auf den Weg '
                    .'gebracht haben.',
                'arten' => ['petition'],
            ],
            [
                'titel' => 'Unterstützte Petitionen',
                'einleitung' => 'Wir müssen das Rad nicht immer neu erfinden. Hier findest Du Petitionen anderer, '
                    .'deren Anliegen wir teilen und denen wir gerne unsere Stimme geben.',
                'arten' => ['petition_unterstuetzt'],
            ],
        ],
    ];

    /**
     * Eigene Petition auf openPetition (KEV-135), Text von Taddi. Titel mit
     * „!“ wie auf openPetition; Datum ist der Start dort (05.10.2026, läuft
     * bis 04.04.2027, an den Bundestag).
     */
    public const PETITION_VERSORGUNG = [
        'slug' => 'petition-erst-versorgung-sichern',
        'art' => 'petition',
        'titel' => 'Erst Versorgung sichern – dann Pflegeleistungen verändern!',
        'datum' => '2026-10-05',
        'ort' => 'openPetition',
        'beschreibung' => '<p>Mit dieser Petition setzen wir uns dafür ein, dass Veränderungen in der Pflege nicht '
            .'zulasten der Menschen gehen, die auf Unterstützung angewiesen sind. Bevor Leistungen verändert oder '
            .'eingeschränkt werden, muss eine verlässliche und bedarfsgerechte Versorgung sichergestellt sein.</p>'
            .'<p>Wenn Du diese Forderung teilst, freuen wir uns, wenn Du die Petition mit Deiner Unterschrift '
            .'unterstützt.</p>',
        'link_url' => 'https://www.openpetition.de/petition/online/erst-versorgung-sichern-dann-pflegeleistungen-veraendern',
        'link_text' => 'Petition unterschreiben',
    ];

    /**
     * Petition des Tour41 e.V. zum Fonds Sexueller Missbrauch (KEV-135),
     * Text von Taddi. Den Titel nannte sie nicht, er ist der der Petition
     * („Fonds sexueller Missbrauch“ muss erhalten bleiben!, innn.it). Ohne
     * Datum: Der Start steht nirgends. Link wie bei Taddi auf die Seite von
     * Tour41 mit den Neuigkeiten und dem Weg zur Petition.
     */
    public const PETITION_TOUR41 = [
        'slug' => 'petition-tour41-fonds-sexueller-missbrauch',
        'art' => 'petition_unterstuetzt',
        'titel' => '„Fonds Sexueller Missbrauch“ muss erhalten bleiben!',
        'ort' => 'Tour41 e. V.',
        'beschreibung' => '<p>Die Petition des Tour41 e. V. setzt sich für den Erhalt des Fonds sexueller Missbrauch '
            .'(FSM) ein. Der Fonds bietet Betroffenen sexuellen Missbrauchs im familiären Bereich niedrigschwellige '
            .'Unterstützung in Form von Sachleistungen und darf nach Auffassung des Vereins nicht Sparmaßnahmen zum '
            .'Opfer fallen.</p>'
            .'<p>Dieses Anliegen unterstützen wir als KE!N EINZELFALL e.V. ausdrücklich und freuen uns über jede '
            .'weitere Unterschrift.</p>',
        'link_url' => 'https://tour41.net/aktiv/aktuelle-petition-updates/',
        'link_text' => 'Zur Petition bei Tour41',
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

    /**
     * Fügt einen Abschnitt direkt hinter dem ein, der Einträge der Art
     * $nachArt zeigt (Petitionen hinter den Messen, KEV-135). Gesucht wird nach
     * der Art, nicht nach der Überschrift: Die heißt je nach Stand „Messen“
     * oder „Ehrenamtsmessen“ (KEV-131), oder der Verein hat sie geändert.
     * Gibt es keinen, kommt er ans Ende. Steht schon einer mit derselben
     * Überschrift da, passiert nichts.
     *
     * @param  array<string, mixed>  $daten
     */
    public static function abschnittEinfuegen(array $daten, string $nachArt): void
    {
        $seite = Page::where('slug', self::SLUG)->where('locale', 'de')->first();

        if (! $seite) {
            return;
        }

        $abschnitte = $seite->blocks()->where('typ', 'oeffentlichkeitsarbeit')->get();

        if ($abschnitte->contains(fn ($b) => ($b->data['titel'] ?? null) === $daten['titel'])) {
            return;
        }

        $davor = $abschnitte->first(fn ($b) => in_array(
            $nachArt,
            collect(\App\Models\PageBlock::oeffentlichkeitsarbeitGruppen($b->data ?? []))->pluck('arten')->flatten()->all(),
            true,
        ));

        if (! $davor) {
            self::abschnittAnhaengen($daten);

            return;
        }

        $seite->blocks()->where('position', '>', $davor->position)->increment('position');
        $seite->blocks()->create([
            'typ' => 'oeffentlichkeitsarbeit',
            'position' => $davor->position + 1,
            'data' => $daten,
        ]);
    }

    /**
     * KEV-131: Der Abschnitt „Messen“ bekommt Taddis Überschrift
     * „Ehrenamtsmessen“, ihre Einleitung und ihr Bild. Nur wo er noch so
     * dasteht, wie wir ihn angelegt haben (Überschrift „Messen“, ohne
     * Einleitung und Bild); hat der Verein ihn geändert, bleibt er.
     */
    public static function messenAbschnittErgaenzen(): void
    {
        $seite = Page::where('slug', self::SLUG)->where('locale', 'de')->first();

        foreach ($seite?->blocks()->where('typ', 'oeffentlichkeitsarbeit')->get() ?? [] as $block) {
            $data = $block->data ?? [];

            if (($data['titel'] ?? null) !== 'Messen' || filled($data['einleitung'] ?? null) || filled($data['bild'] ?? null)) {
                continue;
            }

            $block->update(['data' => array_merge($data, self::MESSEN)]);
        }
    }
}
