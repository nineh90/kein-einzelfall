<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Rückblick auf vergangene Veranstaltungen (KEV-142).
 *
 * Die Seite gibt es seit KEV-72, bisher ohne Inhalt. Jede Veranstaltung ist
 * ein Baustein „Text mit Bild“: Überschrift, Taddis Text, ihr Bild. Für die
 * nächste legt der Verein im Panel einfach einen weiteren an.
 *
 * Der erste: „KE!N EINZELFALL im Dialog – Trauma, Bindung und Beziehung“ vom
 * 10. August 2026, mit Taddis Text und ihrem Bild (zwei Holzstühle im
 * Licht, von Kevin geschickt). Absätze von uns gesetzt. Korrigiert: Komma in
 * „Mit den Fragen, die Menschen, die mit den Folgen von Trauma leben,
 * beschäftigen.“ Der Schlusssatz, der Name der Reihe, fett.
 *
 * Der zweite (KEV-141): „Unsere Veranstaltungen zum Fonds Sexueller
 * Missbrauch (FSM)“ 2025, Taddis Text und ihr Bild („FSM“ im Licht), das
 * Bild links, damit die Abschnitte sich abwechseln. Neueste Veranstaltung
 * oben. Korrigiert: „das ab sofort“ → „dass“ (mit schließendem Gedankenstrich
 * und „dem 31.08.2025“), „So setzen wir im November 2025“ → „setzten“,
 * Komma vor „und orientierten uns“, „Ausfüllhilfe-Veranstaltung“,
 * „behinderungsbedingten“ klein, Anführungszeichen „…“. Ihr P.S. mit dem Link
 * zu den Musterwidersprüchen steht als Hinweis-Kasten darunter, der Link zeigt
 * auf die Dokumente der FSM-Seite (Sprungziel `dokumente`). „Gemeinsam KE!N
 * EINZELFALL.“ fett wie auf ihren anderen Seiten.
 *
 * Der dritte (KEV-140): „Zusammenarbeit mit aus-unserer-sicht im Bereich
 * Soziales Entschädigungsrecht“, Taddis Text aus „0. Aus-unserer-sicht – AG
 * SER u. Workshop“, ihr Bild „Aus unserer Sicht“ (SER, OEG/SGB XIV) aus dem
 * Drive-Ordner „2. Gruppen & Veranstaltungen / 5. Rückblick“. Darunter die
 * zwei Präsentationen vom Fachtag als PDF (Taddi: „als PDF oder
 * Slideshow“; ein PDF zeigt der Browser Folie für Folie). Auf der
 * Workshop-Präsentation sind die Vornamen der zwei weiteren Mitwirkenden
 * entfernt, die Metadaten der Vorlage (fremder Titel und Autor) ersetzt.
 * Protokoll und AG-Zusammenfassung aus demselben Ordner sind interne
 * Arbeitspapiere mit Namen und bleiben draußen.
 * Korrigiert: „Fachtag im November 2025“ → 2024 (Folien und Protokoll:
 * 30.11.2024), „e. V.“ → „e.V.“; die drei Zeilen zum Workshop-Titel sind ein
 * Absatz. Ältester Abschnitt, deshalb unten.
 *
 * Mit Inhalt darf die Seite gefunden werden (vorher noindex wie andere leere
 * Seiten). Legt nur an, was fehlt.
 */
class RueckblickSeeder extends Seeder
{
    public const SLUG = 'rueckblick';

    public const META_DESCRIPTION = 'Rückblick auf Veranstaltungen von KE!N EINZELFALL e.V., zuletzt „KE!N '
        .'EINZELFALL im Dialog – Trauma, Bindung und Beziehung“ mit Lutz-Ulrich Besser.';

    public const TRAUMA_TITEL = 'KE!N EINZELFALL im Dialog – Trauma, Bindung und Beziehung';

    public const TRAUMA_BILD = '/img/rueckblick/trauma-bindung-und-beziehung.webp';

    public const TRAUMA_ABSAETZE = [
        'Mit „Trauma, Bindung und Beziehung“ startete am 10. August 2026 unsere Veranstaltungsreihe „KE!N '
            .'EINZELFALL im Dialog – Wissen trifft Erfahrung“. Unser Anliegen war es, fachliches Wissen und '
            .'gelebte Erfahrung miteinander zu verbinden.',
        'Denn Trauma betrifft nicht nur einzelne Erinnerungen oder Symptome. Es kann sich auch auf Vertrauen, '
            .'Nähe, Sicherheit und Beziehungen auswirken – und damit auf Partnerschaften, Familien, Freundschaften '
            .'und den gesamten Alltag.',
        'Als Referenten durften wir Lutz-Ulrich Besser, Facharzt für Psychiatrie und Psychotherapie sowie Gründer '
            .'und Leiter des Zentrums für Psychotraumatologie und Traumatherapie Niedersachsen, begrüßen. In seinem '
            .'Impulsvortrag erklärte er verständlich, warum traumatische Erfahrungen Bindung und Beziehung verändern '
            .'können und weshalb Reaktionen, die von außen manchmal schwer nachvollziehbar wirken, aus '
            .'traumatherapeutischer Sicht sehr verständlich sein können.',
        'Im Anschluss stand der gemeinsame Austausch im Mittelpunkt. Fragen aus dem Publikum und bereits im Vorfeld '
            .'eingereichte Fragen konnten aufgegriffen und aus unterschiedlichen Blickwinkeln betrachtet werden.',
        'Besonders wichtig war uns auch hier ein Rahmen, der möglichst wenig Druck erzeugt. Die Teilnahme war '
            .'kostenfrei, auf Wunsch anonym, Kamera und Mikrofon mussten nicht eingeschaltet werden und Fragen '
            .'konnten auch ausschließlich über den Chat gestellt werden.',
        'Für uns war dieser Abend ein gelungener Auftakt für ein Format, das genau das verbinden soll, was oft '
            .'getrennt voneinander betrachtet wird: Fachwissen trifft auf Erfahrung. Mit den Fragen, die Menschen, '
            .'die mit den Folgen von Trauma leben, beschäftigen.',
        'Denn manchmal verändert schon das Verstehen den Blick auf sich selbst und aufeinander.',
        '*KE!N EINZELFALL im Dialog – Wissen trifft Erfahrung.*',
    ];

    public const FSM_TITEL = 'Unsere Veranstaltungen zum Fonds Sexueller Missbrauch (FSM)';

    public const FSM_BILD = '/img/rueckblick/fsm.webp';

    public const FSM_ABSAETZE = [
        'Die plötzlichen Veränderungen beim Fonds Sexueller Missbrauch (FSM) haben viele Betroffene nicht nur '
            .'verunsichert – sie haben Vertrauen erschüttert.',
        'Neue Fristen, Jahrestranchen, der Wegfall von Vorauszahlungen und komplizierte Übergangsregelungen waren '
            .'schon Anfang 2025 schwer genug zu verstehen. Viele Betroffene wussten plötzlich nicht mehr, welche '
            .'Regelung für sie gilt, was noch beantragt werden kann und wie bereits bewilligte Leistungen überhaupt '
            .'noch genutzt werden können.',
        'Deshalb haben wir mehrere kostenfreie Online-Veranstaltungen zum Thema „FSM vs. offene Fragen“ angeboten. '
            .'Wir wollten nicht nur Informationen weitergeben, sondern das sortieren, was durch viele neue Regelungen '
            .'unübersichtlich geworden war. Gemeinsam haben wir geschaut, wann eine Bewilligung erfolgt ist, welche '
            .'Fristen gelten, welche Tranchen betroffen sind und was sich konkret verändert hat.',
        'Aus diesen Veranstaltungen entstanden dann unsere „Ausfüllhilfen für Neuanträge“, die wir ebenso mehrfach '
            .'kostenfrei angeboten haben. Wir gingen die Anträge gemeinsam Schritt für Schritt durch, beantworteten '
            .'Fragen und versuchten, aus einem belastenden und komplizierten Verfahren etwas zu machen, das ein '
            .'bisschen weniger überwältigend wirkte, und orientierten uns an der Antragsfrist vom 31.08.2025.',
        'Wir machten Mut, wir motivierten, wir haben geholfen, Neuanträge zu stellen.',
        'Dann kam der 24. Juni 2025.',
        'Plötzlich gab der FSM bekannt, dass Erstanträge rückwirkend nur noch berücksichtigt werden sollten, wenn '
            .'sie bis zum 19.03.2025 eingegangen waren – obwohl zuvor mit einer deutlich späteren Antragsfrist, dem '
            .'31.08.2025, gearbeitet worden war –, und dass ab sofort keine weiteren Neuanträge mehr möglich waren.',
        'Für viele Betroffene war das mehr als ein Schock!',
        'Wir waren erschüttert und schämten uns, noch so viele Betroffene nach dem 19.03.25 motiviert und '
            .'unterstützt zu haben, diesen belastenden Antrag zu stellen, der nun nicht mehr zugelassen wurde.',
        'Menschen haben ihren Mut zusammengenommen, sich wieder mit ihrer Geschichte beschäftigt, Unterlagen '
            .'zusammengesucht und Anträge ausgefüllt – oft unter großer psychischer Belastung. Und dann mussten sie '
            .'erfahren, dass ihr Antrag wegen eines rückwirkend gesetzten Stichtages nicht mehr berücksichtigt wird.',
        'Auch wir waren darüber enttäuscht, wütend und fassungslos. Denn genau ein Hilfesystem für Menschen mit '
            .'schweren Gewalterfahrungen sollte verlässlich, transparent und traumasensibel handeln – und nicht '
            .'zusätzlich das Gefühl erzeugen, wieder keine Kontrolle zu haben und wieder im Stich gelassen zu werden.',
        'Aus einer geplanten Ausfüllhilfe-Veranstaltung wurde deshalb nun ein Raum für „Frust und Fragen“. Hier '
            .'durfte alles Platz haben: Enttäuschung, Wut, Unsicherheit, offene Fragen und die Sorge darüber, wie es '
            .'jetzt weitergeht. Gleichzeitig haben wir gemeinsam geschaut, welche Möglichkeiten noch bestehen und wie '
            .'Betroffene ihre Rechte weiter wahrnehmen können.',
        'So setzten wir im November 2025 unsere Veranstaltungen fort. Unter dem Motto „Wir lassen uns nicht zum '
            .'Schweigen bringen – wir kämpfen weiter!“ informierten wir über neue Entwicklungen, bestehende '
            .'Unterstützungsmöglichkeiten und die weitere Abwicklung des Fonds.',
        'Bei allen diesen Veranstaltungen war uns besonders wichtig, dass niemand zusätzliche Hürden überwinden '
            .'muss: kostenfrei, ohne Mitgliedschaft, ohne Klarnamenzwang, Kamera und Mikrofon freiwillig und Fragen '
            .'jederzeit über den Chat möglich.',
        'Unsere FSM-Veranstaltungen waren deshalb viel mehr als reine Informationsabende. Sie waren Orte zum '
            .'Verstehen, Sortieren, Auffangen, Fragenstellen und Gehörtwerden.',
        'Und sie haben uns noch einmal sehr deutlich gezeigt: Hilfe darf nicht selbst zur nächsten Belastung '
            .'werden. Wer Unterstützung verspricht, muss auch dafür sorgen, dass Menschen sich auf diese '
            .'Unterstützung verlassen können.',
        '*Gemeinsam KE!N EINZELFALL.*',
    ];

    /** Taddis P.S., als Kasten unter dem Abschnitt. Überschrift von uns. */
    public const FSM_PS_TITEL = 'Ablehnungsbescheid bekommen?';

    public const FSM_PS = 'Falls du einen Ablehnungsbescheid zum behinderungsbedingten Mehrbedarf oder zu deinem '
        .'Neuantrag nach dem 19.03.25 bekommen hast, kannst du einen Widerspruch einlegen. Vorlagen für den '
        .'Widerspruch und unsere Stellungnahmen dazu findest du auf unserer '
        .'[Seite zum Fonds Sexueller Missbrauch](/fsm-erweitertes-hilfesystem#dokumente).';

    public const AUS_TITEL = 'Zusammenarbeit mit aus-unserer-sicht im Bereich Soziales Entschädigungsrecht: SGB XIV / OEG';

    public const AUS_BILD = '/img/rueckblick/aus-unserer-sicht.webp';

    public const AUS_ABSAETZE = [
        '*KE!N EINZELFALL e.V.* hatte die Möglichkeit, sich im Rahmen der Arbeitsgruppen von *aus-unserer-sicht '
            .'e.V.* intensiv mit dem Sozialen Entschädigungsrecht und insbesondere mit dem Wechsel vom OEG zum '
            .'SGB XIV und der Umsetzung des SGB XIV auseinanderzusetzen.',
        'Unsere 1. Vorsitzende Tatjana Belmar nahm aktiv an der *Arbeitsgruppe Soziales Entschädigungsrecht (AG '
            .'SER)* teil. Im Mittelpunkt der Arbeitsgruppe standen die Erfahrungen von Betroffenen mit dem bisherigen '
            .'Opferentschädigungsrecht und dem neuen Sozialen Entschädigungsrecht sowie die Frage, wie Verfahren '
            .'betroffenenorientierter, transparenter und weniger belastend gestaltet werden können.',
        'Ein wichtiger Bestandteil dieser Zusammenarbeit war der Fachtag im November 2024. Dort führte Tatjana '
            .'Belmar für KE!N EINZELFALL e.V. den Workshop *„SGB XIV – eine erste Auswertung des Sozialen '
            .'Entschädigungsrechts aus unserer Sicht!“* durch.',
        'Im Workshop wurden gemeinsam Erfahrungen mit dem Übergang vom OEG zum SGB XIV betrachtet. Thematisiert '
            .'wurden unter anderem Antragsverfahren, bürokratische Abläufe, Veränderungen durch das neue Recht, '
            .'bestehende Hürden sowie positive und negative Erfahrungen von Betroffenen. Ziel war es, '
            .'Erfahrungswissen sichtbar zu machen und daraus Rückmeldungen und mögliche Verbesserungsansätze für die '
            .'weitere Umsetzung des Sozialen Entschädigungsrechts abzuleiten.',
        'Aus der Arbeit in der AG entwickelte sich anschließend die Idee für ein weiterführendes Projekt. Geplant war '
            .'eine bundesweite, anonymisierte und strukturierte Auswertung von Erfahrungen und Unterlagen aus '
            .'OEG-/SGB-XIV-Verfahren. Dabei sollten nicht einzelne Fälle bewertet, sondern wiederkehrende Muster von '
            .'Best Practice und Worst Case sichtbar gemacht werden.',
        "Untersucht werden sollten insbesondere:\n"
            ."- strukturelle Missstände und Verfahrensfehler,\n"
            ."- Unterschiede zwischen den Bundesländern,\n"
            ."- Erfahrungen mit Gutachten und Begutachtungsverfahren,\n"
            ."- Fristen und Bearbeitungsabläufe,\n"
            ."- Beispiele guter Praxis,\n"
            .'- sowie Möglichkeiten, das Hilfesystem besser auf die Bedürfnisse von Betroffenen vorzubereiten.',
        'Für dieses Vorhaben sollte zunächst ein belastbarer Erhebungs- und Projektplan entwickelt werden. Dazu '
            .'gehörten unter anderem die Entwicklung von Auswertungskategorien, Fragen der Anonymisierung und des '
            .'Datenschutzes, die rechtliche Absicherung der Datennutzung sowie die Planung der späteren Auswertung und '
            .'Veröffentlichung.',
        'In die Überlegungen flossen auch bereits vorhandene Erfahrungen und Vorarbeiten von KE!N EINZELFALL e.V. '
            .'ein, unter anderem bestehende Kontakte zu Betroffenen sowie vorbereitete Anfragen nach dem '
            .'Informationsfreiheitsgesetz.',
        'Das langfristige Ziel war, Erfahrungen nicht nur zu sammeln, sondern daraus konkrete Erkenntnisse über '
            .'strukturelle Probleme und funktionierende Lösungsansätze abzuleiten. Diese sollten anschließend unter '
            .'anderem an zuständige Landesbehörden, Opferbeauftragte, Bundesministerien und Einrichtungen des '
            .'Hilfesystems weitergegeben werden.',
        '*aus-unserer-sicht e.V. löste sich zum 11.02.2026 auf*, sodass die geplante Zusammenarbeit und das '
            .'ausgearbeitete Projekt in dieser Form nicht fortgeführt werden konnten. Der KE!N EINZELFALL e.V. hat '
            .'Teile des Projektes in eigener Regie weitergeführt.',
        'Trotzdem war diese Zeit für uns sehr wertvoll. Wir konnten Erfahrungen sammeln, uns mit anderen '
            .'Betroffenen und Engagierten vernetzen, unterschiedliche Perspektiven kennenlernen und unsere eigene '
            .'Arbeit im Bereich des Sozialen Entschädigungsrechts weiterentwickeln.',
        '*Wir danken aus-unserer-sicht e.V. für die Möglichkeit zur Mitarbeit, für die gemeinsamen Erfahrungen und '
            .'für den Raum, in dem Betroffenenwissen eingebracht und weiterentwickelt werden konnte.*',
        'Auch wenn dieses Projekt nicht gemeinsam zu Ende geführt werden konnte, bleiben die gewonnenen Erfahrungen '
            .'und viele der daraus entstandenen Ideen Teil unserer weiteren Arbeit.',
        '*Gemeinsam KE!N EINZELFALL*',
    ];

    /** Die Präsentationen vom Fachtag, als PDF. Überschrift und Titel von uns. */
    public const AUS_DOKUMENTE_TITEL = 'Präsentationen vom Fachtag am 30.11.2024';

    public const AUS_DOKUMENTE = [
        [
            'titel' => 'Workshop „SGB XIV / Soziales Entschädigungsrecht – ein knappes Jahr später!“ (18 Folien)',
            'url' => '/dokumente/2026/10/aus-unserer-sicht-fachtag-2024-workshop-sgb-xiv.pdf',
        ],
        [
            'titel' => 'Ergebnisse der AG SGB XIV/SER (5 Folien)',
            'url' => '/dokumente/2026/10/aus-unserer-sicht-fachtag-2024-ag-ser.pdf',
        ],
    ];

    /**
     * Die Abschnitte in der Reihenfolge der Seite, neueste Veranstaltung oben.
     *
     * @return list<array{titel: string, absaetze: list<string>, bild: string, bild_seite: string, danach?: array}>
     */
    public static function abschnitte(): array
    {
        return [
            [
                'titel' => self::TRAUMA_TITEL,
                'absaetze' => self::TRAUMA_ABSAETZE,
                'bild' => self::TRAUMA_BILD,
                'bild_seite' => 'rechts',
            ],
            [
                'titel' => self::FSM_TITEL,
                'absaetze' => self::FSM_ABSAETZE,
                'bild' => self::FSM_BILD,
                'bild_seite' => 'links',
                'danach' => ['typ' => 'hinweis', 'data' => ['titel' => self::FSM_PS_TITEL, 'text' => self::FSM_PS]],
            ],
            [
                'titel' => self::AUS_TITEL,
                'absaetze' => self::AUS_ABSAETZE,
                'bild' => self::AUS_BILD,
                'bild_seite' => 'rechts',
                'danach' => ['typ' => 'download_list', 'data' => [
                    'titel' => self::AUS_DOKUMENTE_TITEL,
                    'dokumente' => self::AUS_DOKUMENTE,
                ]],
            ],
        ];
    }

    public function run(): void
    {
        self::anlegen();
    }

    public static function anlegen(): void
    {
        $seite = Page::query()
            ->where('slug', self::SLUG)
            ->where('locale', 'de')
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->first();

        if (! $seite) {
            return;
        }

        $warLeer = $seite->blocks->isEmpty();
        $position = (int) $seite->blocks->max('position');

        foreach (self::abschnitte() as $abschnitt) {
            $vorhanden = $seite->blocks->contains(fn ($b) => ($b->data['titel'] ?? null) === $abschnitt['titel']);

            if ($vorhanden) {
                continue;
            }

            $seite->blocks()->create([
                'typ' => 'text_media',
                'position' => $seite->blocks()->exists() ? ++$position : $position,
                'data' => [
                    'titel' => $abschnitt['titel'],
                    'absaetze' => $abschnitt['absaetze'],
                    // Stimmungsbilder, sagen nichts aus: ohne Bildbeschreibung.
                    'bild' => $abschnitt['bild'],
                    'bild_seite' => $abschnitt['bild_seite'],
                ],
            ]);

            if (isset($abschnitt['danach'])) {
                $seite->blocks()->create($abschnitt['danach'] + ['position' => ++$position]);
            }
        }

        self::sprungzielDokumente();

        if ($warLeer) {
            $seite->update([
                'noindex' => false,
                'meta_description' => $seite->meta_description ?: self::META_DESCRIPTION,
            ]);
        }
    }

    /**
     * Der Link im P.S. zeigt auf die Dokumente der FSM-Seite. Ein festes
     * Sprungziel, damit er auch auf Englisch und nach einer geänderten
     * Überschrift trifft. Nur, wenn dort noch keins steht.
     *
     * Öffentlich, weil auch der AltseiteSeeder es aufruft: Auf frischer
     * Datenbank gibt es die FSM-Seite erst nach den Migrationen.
     */
    public static function sprungzielDokumente(): void
    {
        $deutsch = Page::where('slug', 'fsm-erweitertes-hilfesystem')->where('locale', 'de')->with('blocks')->first();
        $block = $deutsch?->blocks->first(fn ($b) => $b->typ === 'text'
            && str_starts_with((string) ($b->data['titel'] ?? ''), 'Dokumente von uns'));

        if (! $block) {
            return;
        }

        // Die Übersetzungen tragen dieselben Bausteine an derselben Stelle,
        // aber mit übersetzter Überschrift.
        Page::where('uebersetzungs_gruppe', $deutsch->uebersetzungs_gruppe)->with('blocks')->get()
            ->flatMap->blocks
            ->filter(fn ($b) => $b->typ === 'text' && $b->position === $block->position)
            ->reject(fn ($b) => filled($b->data['anker'] ?? null))
            ->each(fn ($b) => $b->update(['data' => $b->data + ['anker' => 'dokumente']]));
    }
}
