<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Projekt „ANUAS“ (KEV-162), die erste Unterseite im Bereich „Projekte“.
 *
 * Taddis Text aus dem Doc „Opferanwalt“ in ihrem Drive-Ordner „1. Opferanwalt“:
 * „Projekt: Opferschutz aus Betroffenenperspektive weiterdenken“, die Arbeit in
 * der ANUAS-Arbeitsgruppe „Mit-Opferrechte“. Ihr Projekttitel steht als
 * Unterzeile auf dem Bild, „ANUAS“ (wie im Ticket) ist Titel und Menüpunkt.
 *
 * Aufbau wie bei ihr: „Worum geht es?“, die Projektdokumentation mit ihren
 * Ständen (neuester oben, der Stand als Überzeile), dann die drei Abschnitte
 * zu Mit-Opfern, Betroffenenexpertise und Ziel. Lauter Textabschnitte, also
 * ein Artikel mit „Auf dieser Seite“ daneben. Ihre Frage „Wie können
 * Institutionen überprüfen …?“ (bei ihr eine Überschrift mitten im Stand
 * Oktober 2026) steht fett im Abschnitt. Absätze teils zusammengezogen, damit
 * kein Abschnitt einklappt; ihr Fettes ist fett. Korrigiert: „ANUAS e. V.“ →
 * „e.V.“.
 *
 * Nicht übernommen: die drei PDFs aus demselben Ordner (Stellungnahme zum
 * „Fachanwalt für Opferrecht“, O-Töne, Erfahrungsberichte, alle 02/2025).
 * Sie gehören zum Projekt „Opferanwalt“ der Projektliste des Vereins (VVT
 * 4.5.11), ANUAS ist 4.5.12, und ihr Briefkopf trägt eine alte Adresse und
 * private Telefonnummern. Frage in der Übergabe-Checkliste. Ein Bild lag
 * nicht im Ordner: Platzhalter.
 *
 * Legt nur an, was fehlt.
 */
class AnuasSeeder extends Seeder
{
    public const SLUG = 'anuas';

    public const TITEL = 'ANUAS';

    public const UNTERTITEL = 'Opferschutz aus Betroffenenperspektive weiterdenken';

    public const META_DESCRIPTION = 'ANUAS-Arbeitsgruppe „Mit-Opferrechte“: KE!N EINZELFALL e.V. bringt '
        .'Betroffenenexpertise in den Fachdialog zu Opferschutz, Mit-Opfern und Sozialem Entschädigungsrecht ein.';

    /** @return list<array{eyebrow?: string, titel: string, absaetze: list<string>}> */
    public static function abschnitte(): array
    {
        return [
            [
                'titel' => 'Worum geht es?',
                'absaetze' => [
                    'Was brauchen Opfer und Mit-Opfer nach schwerer Gewalt wirklich – und was passiert, wenn '
                        .'Hilfesysteme zwar vorhanden sind, Betroffene darin aber trotzdem keinen verlässlichen Zugang zu '
                        .'Unterstützung finden?',
                    'Im Rahmen der *ANUAS-Arbeitsgruppe „Mit-Opferrechte“* bringen wir unsere Erfahrungen aus der '
                        .'Opferhilfe und unsere Betroffenenexpertise in den fachlichen Austausch ein. Dabei beschäftigen '
                        .'wir uns mit der Frage, wie Opferschutz, Soziales Entschädigungsrecht, medizinische und '
                        .'psychotherapeutische Versorgung sowie institutionelle Verfahren so gestaltet werden können, '
                        .'dass sie Betroffene tatsächlich unterstützen – und nicht durch komplizierte Verfahren, fehlende '
                        .'Zuständigkeiten oder mangelnde Traumasensibilität zusätzlich belasten.',
                    'Unser Ziel ist ein Hilfesystem, in dem *nicht nur über Betroffene gesprochen wird, sondern mit '
                        .'ihnen.*',
                ],
            ],
            [
                'titel' => 'Projektdokumentation',
                'absaetze' => [],
            ],
            [
                'eyebrow' => 'Stand 18.10.2026',
                'titel' => 'Unsere Stellungnahme zum Fachdialog „Mit-Opferrechte“',
                'absaetze' => [
                    'Nach dem Fachdialog der ANUAS-Arbeitsgruppe vom 13. Oktober 2026 haben wir unsere Positionen noch '
                        .'einmal in einer eigenen Stellungnahme zusammengefasst.',
                    'Für uns steht dabei besonders im Mittelpunkt: *Traumasensibilität darf nicht vom persönlichen '
                        .'Engagement einzelner Fachkräfte abhängen. Sie muss Teil professioneller Standards werden.*',
                    'Gewalterfahrungen können langfristige Auswirkungen auf psychische und körperliche Gesundheit, '
                        .'Beziehungen, Arbeitsfähigkeit, soziale Teilhabe und die gesamte Lebensgestaltung haben. Diese '
                        .'Zusammenhänge müssen bei Versorgung, Begutachtung und sozialrechtlichen Verfahren '
                        .'berücksichtigt werden.',
                    'Genauso wichtig ist uns, dass Betroffene nicht nur als *Akte, Diagnose oder hilfebedürftige '
                        .'Person* gesehen werden. Menschen bringen trotz – und manchmal auch gerade durch – ihre '
                        .'Erfahrungen Wissen, Fähigkeiten, Ressourcen und Kompetenzen mit.',
                    'Deshalb setzen wir uns für eine Unterstützung ein, die nicht nur Defizite betrachtet, sondern '
                        .'*Selbstbestimmung und vorhandene Ressourcen stärkt*.',
                ],
            ],
            [
                'eyebrow' => 'Stand Oktober 2026',
                'titel' => 'Forderungen für einen traumasensiblen Opferschutz',
                'absaetze' => [
                    'Aus unserer Arbeit und den Diskussionen innerhalb der Arbeitsgruppe haben wir konkrete Forderungen '
                        .'zur Verbesserung der Versorgung und des institutionellen Opferschutzes formuliert.',
                    "Dazu gehören insbesondere:\n"
                        ."- verbindliche Traumasensibilität in Aus-, Fort- und Weiterbildung,\n"
                        ."- eine ganzheitliche Betrachtung langfristiger Gewaltfolgen,\n"
                        ."- verständliche und transparente Verfahren,\n"
                        ."- Schutz vor institutioneller sekundärer Viktimisierung,\n"
                        ."- bessere Therapie- und Versorgungsübergänge,\n"
                        .'- eine stärkere Vernetzung von Psychotherapie, Psychiatrie, Rehabilitation, '
                        ."Sozialleistungsträgern und Opferhilfe,\n"
                        ."- Lotsen und qualifizierte Begleitung durch komplizierte Verfahren,\n"
                        .'- sowie die systematische Beteiligung von Menschen mit eigener Gewalterfahrung.',
                    'Ein besonderer Schwerpunkt liegt auf der Frage: *Wie können Institutionen überprüfen, ob ihre '
                        .'Verfahren für Betroffene tatsächlich funktionieren?*',
                    'Deshalb setzen wir uns für eine *Betroffenenverträglichkeitsprüfung* ein.',
                    'Nicht nur Gesetze und Verwaltungsabläufe müssen formal richtig sein. Es muss auch geprüft werden, '
                        .'*welche Wirkung sie in der Realität auf die Menschen haben, für die sie geschaffen wurden.*',
                ],
            ],
            [
                'eyebrow' => 'Stand 13.10.2026',
                'titel' => 'Fachdialog der ANUAS-Arbeitsgruppe „Mit-Opferrechte“',
                'absaetze' => [
                    'Am 13. Oktober 2026 nehmen wir erneut am Fachdialog der ANUAS-Arbeitsgruppe *„Mit-Opferrechte“* '
                        .'teil.',
                    'Ausgangspunkt ist unter anderem ein laufendes Petitionsverfahren des Bundesverbandes ANUAS e.V. zur '
                        .'Situation von Mit-Opfern von Tötungsdelikten.',
                    "Dabei geht es nicht nur um einzelne Leistungen. Im Mittelpunkt stehen grundsätzliche Fragen:\n"
                        ."- Werden Opferrechte in der Praxis tatsächlich erreicht?\n"
                        ."- Funktioniert das Soziale Entschädigungsrecht für die Menschen, die darauf angewiesen sind?\n"
                        ."- Wo entstehen durch Behörden und Institutionen zusätzliche Belastungen?\n"
                        ."- Wie können Mit-Opfer besser wahrgenommen und unterstützt werden?\n"
                        .'- Und wie können Betroffene selbst an der Weiterentwicklung dieser Systeme beteiligt werden?',
                    'Auch die Umsetzung und Evaluation des SGB XIV, Amtsermittlung, Verfahrensgestaltung, '
                        .'traumasensible Anwendungshinweise und die Beteiligung von Betroffenenorganisationen werden '
                        .'dabei thematisiert.',
                ],
            ],
            [
                'eyebrow' => 'Stand September 2026',
                'titel' => 'Schreiben des BMAS wird zum Ausgangspunkt weiterer Diskussionen',
                'absaetze' => [
                    'Im Zusammenhang mit den Anliegen von ANUAS teilt das Bundesministerium für Arbeit und Soziales mit, '
                        .'dass Fragen zur Situation von Mit-Opfern bereits mehrfach geprüft worden seien – unter anderem '
                        .'im Zusammenhang mit dem Gesetzgebungsverfahren zum SGB XIV. Auch Stellungnahmen im Rahmen der '
                        .'Verbändebeteiligung seien geprüft worden.',
                    'Aus Sicht des Ministeriums habe sich daraus jedoch kein weitergehender Handlungsauftrag ergeben.',
                    'Für die Arbeitsgruppe wird genau dies zu einem wichtigen Ausgangspunkt: *Reicht es, wenn ein '
                        .'Problem geprüft wurde – oder muss auch betrachtet werden, ob die bestehenden Regelungen in der '
                        .'Praxis tatsächlich funktionieren?*',
                    'Gerade diese Frage beschäftigt uns immer wieder.',
                ],
            ],
            [
                'eyebrow' => 'Stand 23.09.2025',
                'titel' => 'Betroffene berichten selbst',
                'absaetze' => [
                    'Ein besonders prägender Moment der Arbeitsgruppe ist der Bericht eines Ehepaars, das durch einen '
                        .'Terroranschlag in Hamburg seinen Sohn verloren hat. Ihre Schilderungen machen deutlich, wie '
                        .'weitreichend die Folgen einer Gewalttat auch für Angehörige sein können.',
                    "Es geht nicht nur um Trauer. Es geht um:\n"
                        ."- psychische und körperliche Folgen,\n"
                        ."- finanzielle Veränderungen,\n"
                        ."- Einschränkungen der Arbeitsfähigkeit,\n"
                        ."- Orientierung im Sozialen Entschädigungsrecht,\n"
                        ."- therapeutische Unterstützung,\n"
                        ."- Behördenkontakte,\n"
                        .'- und die Frage, wer eigentlich dabei hilft, all diese verschiedenen Systeme miteinander zu '
                        .'verbinden.',
                    'Besonders deutlich wird: *Mit-Opfer können nach einer schweren Gewalttat häufig nicht einfach '
                        .'selbst recherchieren, Anträge stellen, Zuständigkeiten klären und sich durch unterschiedliche '
                        .'Hilfesysteme bewegen.*',
                    'Gerade in dieser Situation braucht es Unterstützung, Orientierung und verlässliche Ansprechpartner.',
                ],
            ],
            [
                'eyebrow' => 'Stand 23.09.2025',
                'titel' => 'Unsere Auseinandersetzung mit dem Sozialen Entschädigungsrecht',
                'absaetze' => [
                    'Aus dem Austausch heraus beschäftigen wir uns intensiv mit den Möglichkeiten und Grenzen des *OEG '
                        .'beziehungsweise des SGB XIV*.',
                    "Wir schauen unter anderem auf:\n"
                        ."- Entschädigungsleistungen,\n"
                        ."- Bedarfsermittlung,\n"
                        ."- medizinische und psychotherapeutische Versorgung,\n"
                        ."- Rehabilitation,\n"
                        ."- mögliche Teilhabeleistungen,\n"
                        ."- Grad der Schädigungsfolgen,\n"
                        ."- Pflege und Behinderung,\n"
                        ."- psychosoziale Prozessbegleitung,\n"
                        ."- Traumaambulanzen,\n"
                        .'- sowie weitere Unterstützungsangebote.',
                    'Dabei zeigt sich immer wieder ein Unterschied zwischen *„Eine Leistung existiert gesetzlich“* und '
                        .'*„Eine betroffene Person kann diese Leistung tatsächlich erreichen.“*',
                    'Genau diese Lücke möchten wir sichtbar machen.',
                ],
            ],
            [
                'titel' => 'Mit-Opfer sind nicht „nur Angehörige“',
                'absaetze' => [
                    'Ein weiterer wichtiger Schwerpunkt unserer Arbeit ist die Anerkennung der besonderen Situation von '
                        .'*Mit-Opfern*.',
                    'Der Verlust eines nahestehenden Menschen durch Gewalt oder das unmittelbare Miterleben einer '
                        .'schweren Gewalttat kann selbst tiefgreifende gesundheitliche und soziale Folgen haben.',
                    'Deshalb setzen wir uns dafür ein, dass Mit-Opfer nicht nur als Randgruppe bestehender Hilfesysteme '
                        .'betrachtet werden.',
                    "Sie brauchen:\n"
                        ."- frühzeitige Information,\n"
                        ."- verständliche Aufklärung über ihre Rechte,\n"
                        ."- Unterstützung bei Anträgen,\n"
                        ."- therapeutische und psychosoziale Begleitung,\n"
                        .'- und eine verlässliche Lotsenfunktion.',
                ],
            ],
            [
                'titel' => 'Betroffenenexpertise gehört an den Tisch',
                'absaetze' => [
                    'Ein zentraler Bestandteil des Projekts ist für uns die Beteiligung von Menschen mit eigener '
                        .'Gewalterfahrung.',
                    "Betroffene kennen:\n"
                        ."- die Realität von Behördenverfahren,\n"
                        ."- die Belastungen durch Begutachtungen,\n"
                        ."- Versorgungslücken,\n"
                        ."- unverständliche Zuständigkeiten,\n"
                        .'- und die Auswirkungen, die Entscheidungen von Institutionen auf den Alltag haben.',
                    'Dieses Wissen ersetzt keine fachliche oder wissenschaftliche Expertise. Aber es ergänzt sie um eine '
                        .'Perspektive, die sonst fehlt. Deshalb setzen wir uns dafür ein, *Betroffenenexpertise '
                        .'gleichberechtigt in fachliche Entwicklungsprozesse einzubeziehen.*',
                    'Besonders wertvoll erleben wir dabei den offenen Austausch mit Fachpersonen, die bereit sind, '
                        .'zuzuhören und unterschiedliche Perspektiven miteinander zu verbinden.',
                    '*Auf Augenhöhe bedeutet für uns nicht, dass alle dieselbe Rolle haben – sondern dass Wissen und '
                        .'Erfahrung gegenseitig ernst genommen werden.*',
                ],
            ],
            [
                'titel' => 'Unser Ziel',
                'absaetze' => [
                    'Wir möchten dazu beitragen, dass Opferschutz nicht nur auf dem Papier funktioniert.',
                    'Unser Ziel ist ein Hilfesystem, das *traumasensibel, verständlich, verlässlich, vernetzt und an den '
                        .'tatsächlichen Bedürfnissen der Menschen orientiert ist.*',
                    'Und wir möchten, dass Betroffene nicht erst dann gehört werden, wenn etwas schiefgelaufen ist. Sie '
                        .'sollten bereits bei der Entwicklung von Standards, Verfahren und Hilfsangeboten beteiligt werden.',
                    'Denn echter Opferschutz beginnt für uns dort, *wo Betroffene nicht länger nur Gegenstand fachlicher '
                        .'Diskussionen sind, sondern mit ihrem Wissen, ihren Erfahrungen und ihrer Expertise an '
                        .'Veränderungen mitwirken.*',
                    '*Gemeinsam KE!N EINZELFALL.*',
                ],
            ],
        ];
    }

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
                'untertitel' => self::UNTERTITEL,
                'meta_title' => self::TITEL.' - Kein Einzelfall e.V.',
                'meta_description' => self::META_DESCRIPTION,
                'noindex' => false,
                'published_at' => now(),
            ],
        );

        if ($seite->blocks()->doesntExist()) {
            foreach (self::abschnitte() as $position => $abschnitt) {
                $seite->blocks()->create(['typ' => 'text', 'position' => $position, 'data' => array_filter($abschnitt)]);
            }
        }

        Titelbilder::platzhalterSetzen();
    }
}
