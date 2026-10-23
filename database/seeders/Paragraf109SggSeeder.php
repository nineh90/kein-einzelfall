<?php

namespace Database\Seeders;

/**
 * Projekt „§ 109 SGG Gegengutachten“ (KEV-161) unter „Projekte“, nach ANUAS
 * wie in der Projektliste des Vereins (VVT 4.5.21 nach 4.5.12).
 *
 * Taddis Text aus ihrem Doc „§109 SGG Gegengutachten“: „Worum geht es?“, ihre
 * Projektdokumentation vom 08.09.2026 zurück bis 17.10.2025, „Das
 * Kernproblem“ und das Ziel. Ihre Überschrift „§ 109 SGG – Wenn ein Recht an
 * der Suche nach Sachverständigen scheitert“ steht als Unterzeile auf dem
 * Bild. Ihr Fettes ist fett, Aufzählungen sind Listen; kurze Absätze sind
 * zusammengezogen, damit kein Abschnitt einklappt.
 *
 * Geändert: „Als Möglichkeiten zur eigenen Suche werden Betroffene weiterhin
 * unter anderem auf: [Liste] verwiesen.“ → „Zur eigenen Suche verweist das
 * BMAS Betroffene weiterhin unter anderem auf: [Liste]“, sonst stünde
 * „verwiesen.“ allein unter der Liste. Im Kernproblem „einen bestimmten Arzt
 * benennen zu lassen“ → „anhören zu lassen“ (benennen tun Betroffene selbst,
 * so steht es auch in „Worum geht es?“). Beides in der Übergabe-Checkliste.
 */
class Paragraf109SggSeeder extends ProjektSeite
{
    public const SLUG = '109-sgg-gegengutachten';

    public const TITEL = '§ 109 SGG Gegengutachten';

    public const UNTERTITEL = 'Wenn ein Recht an der Suche nach Sachverständigen scheitert';

    public const META_DESCRIPTION = '§ 109 SGG: Betroffene dürfen im Sozialgerichtsverfahren einen Arzt ihrer Wahl '
        .'benennen, finden aber kaum Sachverständige. KE!N EINZELFALL e.V. dokumentiert die Suche und die Antworten.';

    public static function abschnitte(): array
    {
        return [
            [
                'titel' => 'Worum geht es?',
                'absaetze' => [
                    'Im Sozialen Entschädigungsrecht können Gutachten eine entscheidende Rolle dafür spielen, ob '
                        .'Gewalterfahrungen und gesundheitliche Folgen anerkannt werden.',
                    'Im sozialgerichtlichen Verfahren gibt *§ 109 SGG* Betroffenen die Möglichkeit, die Anhörung eines '
                        .'von ihnen benannten Arztes zu beantragen. Gerade nach einem für Betroffene negativen Gutachten '
                        .'kann diese Möglichkeit von großer Bedeutung sein.',
                    'In der Praxis sind wir jedoch auf eine grundlegende Schwierigkeit gestoßen: *Betroffene müssen '
                        .'einen geeigneten Sachverständigen benennen – finden aber insbesondere für aussagepsychologische '
                        .'Glaubhaftigkeitsfragen kaum jemanden, der die fachlichen Voraussetzungen erfüllt und einen '
                        .'solchen Auftrag tatsächlich übernimmt.*',
                    'Aus einem konkreten Problem entstand deshalb ein größeres Recherche- und Aufklärungsprojekt.',
                    "Unser Ziel ist es herauszufinden:\n"
                        ."- Wo können Betroffene geeignete Sachverständige finden?\n"
                        ."- Wer ist für eine verlässliche Orientierung zuständig?\n"
                        .'- Was geschieht mit dem gesetzlich vorgesehenen Gutachterverzeichnis im Sozialen '
                        ."Entschädigungsrecht?\n"
                        .'- Und wie kann ein Recht nach § 109 SGG tatsächlich genutzt werden, wenn der Zugang zu '
                        .'geeigneten Fachpersonen in der Praxis kaum möglich ist?',
                ],
            ],
            [
                'titel' => 'Projektdokumentation',
                'absaetze' => [],
            ],
            [
                'eyebrow' => 'Stand 08.09.2026',
                'titel' => 'Unsere Suche geht weiter',
                'absaetze' => [
                    'Auch Monate nach Beginn unserer bundesweiten Recherche ist die Frage des tatsächlichen Zugangs zu '
                        .'geeigneten Sachverständigen nicht abschließend geklärt.',
                    'Auf eine unserer Anfragen teilt uns das *Sozialgericht Augsburg* mit, dass es die Fragen selbst '
                        .'nicht beantworten und auch keine Sachverständigenlisten übersenden könne. Unsere Anfrage wird '
                        .'deshalb an das *Bayerische Landessozialgericht* weitergeleitet.',
                    'Für uns zeigt auch diese Rückmeldung erneut das grundlegende Problem: *Betroffene werden von Stelle '
                        .'zu Stelle verwiesen, während sie selbst gegenüber dem Gericht einen konkreten Sachverständigen '
                        .'benennen müssen.*',
                ],
            ],
            [
                'eyebrow' => 'Stand 31.08.2026',
                'titel' => 'Erneute Antwort des Bundesministeriums für Arbeit und Soziales',
                'absaetze' => [
                    'Nach unserer weiteren Nachfrage erhalten wir erneut eine schriftliche Antwort des '
                        .'*Bundesministeriums für Arbeit und Soziales (BMAS)*. Das Ministerium bestätigt nochmals, dass '
                        .'es ein Gutachterverzeichnis erstellen wird.',
                    'Gleichzeitig weist das BMAS darauf hin, dass ein solches Verzeichnis lediglich über die *Eignung* '
                        .'von Gutachterinnen und Gutachtern Auskunft geben könne – nicht darüber, ob diese tatsächlich '
                        .'verfügbar seien oder einen Auftrag übernehmen könnten.',
                    "Zur eigenen Suche verweist das BMAS Betroffene weiterhin unter anderem auf:\n"
                        ."- Ärztekammern,\n"
                        ."- Fachverbände,\n"
                        .'- sowie Rechtsanwältinnen und Rechtsanwälte für Sozialrecht.',
                    'Für uns bleibt damit eine entscheidende Frage offen: *Wie soll ein Betroffener ein gesetzliches '
                        .'Recht praktisch nutzen, wenn er zwar selbst einen Sachverständigen benennen muss, aber keine '
                        .'zentrale Stelle sagen kann, wer tatsächlich für einen solchen Auftrag zur Verfügung steht?*',
                ],
            ],
            [
                'eyebrow' => 'Stand 21.07.2026',
                'titel' => 'Wir fragen erneut nach',
                'absaetze' => [
                    'Die Antwort des BMAS vom Juli klärt für uns zwar erstmals eindeutig, wer künftig für das '
                        .'Gutachterverzeichnis zuständig ist. Sie löst jedoch das eigentliche Problem nicht.',
                    'Deshalb fragen wir erneut nach: Was bedeutet ein zukünftiges Verzeichnis für Betroffene, die '
                        .'*jetzt* einen Sachverständigen benötigen?',
                    'Wie soll der Zugang konkret funktionieren?',
                    'Und wie wird verhindert, dass ein gesetzliches Verfahrensrecht faktisch daran scheitert, dass '
                        .'niemand gefunden werden kann?',
                ],
            ],
            [
                'eyebrow' => 'Stand 16.07.2026',
                'titel' => 'BMAS bestätigt: Das vorgesehene Gutachterverzeichnis existiert noch nicht',
                'absaetze' => [
                    'Das Bundesministerium für Arbeit und Soziales beantwortet unsere erneute Anfrage schriftlich. '
                        .'Dabei erhalten wir eine für unser Projekt besonders wichtige Bestätigung: Bis Ende Juni 2026 '
                        .'war die *Bundesstelle für Soziale Entschädigung* mit dem Führen eines Verzeichnisses von im '
                        .'Sozialen Entschädigungsrecht erfahrenen medizinischen Gutachterinnen und Gutachtern beauftragt.',
                    'Das BMAS teilt uns jedoch ausdrücklich mit: *Ein solches Verzeichnis wurde bis dahin nicht '
                        .'erstellt.* Mit dem Übergang der Aufgabe auf das BMAS zum Juli 2026 konnte deshalb auch kein '
                        .'bestehendes Verzeichnis übernommen werden.',
                    'Das Ministerium erklärt, dass nun umfangreiche Recherchearbeiten für die Erstellung eines '
                        .'Gutachterverzeichnisses laufen. Wann dieses Verzeichnis öffentlich zugänglich sein wird, '
                        .'könne zu diesem Zeitpunkt jedoch noch nicht gesagt werden.',
                    'Damit bestätigt sich eine unserer zentralen Beobachtungen: *Eine gesetzlich vorgesehene '
                        .'Orientierungsmöglichkeit für Betroffene stand in der Praxis über einen langen Zeitraum nicht '
                        .'zur Verfügung.*',
                ],
            ],
            [
                'eyebrow' => 'Stand Juni 2026',
                'titel' => 'Zuständigkeit verändert sich',
                'absaetze' => [
                    'Im Laufe unserer Recherche wird deutlich, dass sich auch die Zuständigkeit für das '
                        .'Gutachterverzeichnis verändert.',
                    'Die Aufgabe war ursprünglich in *§ 124 Abs. 4 Nr. 5 SGB XIV* bei der Bundesstelle für Soziale '
                        .'Entschädigung verortet. Im Zuge gesetzlicher Änderungen wird diese Aufgabe dort jedoch '
                        .'gestrichen beziehungsweise neu zugeordnet.',
                    'Für Betroffene entsteht dadurch zunächst eine zusätzliche Unsicherheit: *Wer führt das Verzeichnis '
                        .'künftig überhaupt?*',
                    'Wir wenden uns deshalb erneut an das Bundesministerium für Arbeit und Soziales und bitten um '
                        .'konkrete Klärung.',
                ],
            ],
            [
                'eyebrow' => 'Stand April 2026',
                'titel' => 'Antworten aus der Sozialgerichtsbarkeit',
                'absaetze' => [
                    'Auf unsere bundesweiten Anfragen erreichen uns verschiedene Rückmeldungen von Sozialgerichten.',
                    "Wir hatten die Gerichte insbesondere gefragt:\n"
                        ."- ob sie geeignete Sachverständige für entsprechende Begutachtungen kennen,\n"
                        ."- ob sie Orientierung geben können,\n"
                        .'- und wie Betroffene einen geeigneten Sachverständigen für einen Antrag nach § 109 SGG finden '
                        .'sollen.',
                    'Die eingehenden Antworten dokumentieren wir fortlaufend. Dabei wird zunehmend deutlich, dass es '
                        .'*keine für Betroffene leicht zugängliche, bundesweit einheitliche Anlaufstelle* gibt, über die '
                        .'sich die benötigten Sachverständigen zuverlässig finden lassen.',
                ],
            ],
            [
                'eyebrow' => 'Stand 03.04.2026',
                'titel' => 'Wir wenden uns an das Bundesministerium',
                'absaetze' => [
                    'Das Problem lässt sich nach unserer bisherigen Recherche nicht durch einzelne Anfragen lösen. '
                        .'Deshalb richten wir eine umfangreiche Anfrage an das *Bundesministerium für Arbeit und '
                        .'Soziales*.',
                    "Wir möchten insbesondere wissen:\n"
                        .'- Wie soll künftig sichergestellt werden, dass Betroffene geeignete Sachverständige nach '
                        ."§ 109 SGG finden?\n"
                        ."- Wer wird das vorgesehene Gutachterverzeichnis führen?\n"
                        ."- Welche Alternative gibt es, solange dieses Verzeichnis nicht verfügbar ist?\n"
                        .'- Können Betroffene zumindest eine Orientierung darüber erhalten, welche Sachverständigen in '
                        .'vergleichbaren Verfahren bereits eingesetzt wurden?',
                    'Für uns steht dabei nicht ein einzelnes Gutachten im Mittelpunkt. Es geht um eine *strukturelle '
                        .'Frage des Zugangs zu Rechten im Sozialen Entschädigungsrecht.*',
                ],
            ],
            [
                'eyebrow' => 'Stand 31.03.2026',
                'titel' => 'Bundesweite Recherche beginnt',
                'absaetze' => [
                    "Wir beginnen eine umfangreiche Recherche bei:\n"
                        ."- Sozialgerichten,\n"
                        ."- Ärztekammern,\n"
                        ."- psychologischen und rechtspsychologischen Fachpersonen,\n"
                        ."- Fachverbänden,\n"
                        ."- Institutionen,\n"
                        ."- Ministerien,\n"
                        .'- und weiteren Stellen.',
                    'Parallel fragen wir gezielt Sachverständige an, die aufgrund ihrer Qualifikation grundsätzlich für '
                        .'aussagepsychologische beziehungsweise rechtspsychologische Fragestellungen infrage kommen '
                        .'könnten. Die Antworten fallen sehr unterschiedlich aus.',
                    "Wir erfahren unter anderem:\n"
                        ."- Einige Fachpersonen erstellen grundsätzlich keine Glaubhaftigkeitsgutachten.\n"
                        ."- Andere nehmen ausschließlich Aufträge von Gerichten oder Behörden an.\n"
                        .'- Manche erstellen zwar entsprechende Gutachten, stehen jedoch nicht für privat initiierte '
                        ."Begutachtungen zur Verfügung.\n"
                        ."- Andere haben keine freien Kapazitäten.\n"
                        .'- Ärztekammern verweisen teilweise auf andere Kammern, Fachgesellschaften oder psychologische '
                        ."Register.\n"
                        .'- Für aussagepsychologische Fragestellungen wird häufig auf speziell qualifizierte '
                        .'Rechtspsychologinnen und Rechtspsychologen verwiesen.',
                    'Damit wird genau das Spannungsfeld sichtbar, das unser Projekt ausgelöst hat: *Fachlich geeignete '
                        .'Personen gibt es – aber das bedeutet noch lange nicht, dass Betroffene sie für ein Verfahren '
                        .'nach § 109 SGG tatsächlich benennen können.*',
                ],
            ],
            [
                'eyebrow' => 'Stand 30.03.2026',
                'titel' => 'Die Bundesstelle kann weiterhin niemanden benennen',
                'absaetze' => [
                    'Nach erneuter Nachfrage bei der *Bundesstelle für Soziale Entschädigung (BfSE)* erhalten wir die '
                        .'Information, dass es keinen neuen Sachstand zum Gutachterverzeichnis gibt. Auch unabhängig von '
                        .'diesem Verzeichnis könne uns dort niemand für die gesuchte Form der Glaubhaftigkeitsbegutachtung '
                        .'benannt werden.',
                    'Gleichzeitig erfahren wir, dass die Aufgabe des Führens dieses Verzeichnisses im Zuge einer '
                        .'gesetzlichen Änderung aus dem Aufgabenbereich der Bundesstelle herausgenommen werden soll.',
                    'Damit entsteht für uns eine neue Frage: *Wenn die bisher zuständige Stelle das Verzeichnis nicht '
                        .'aufgebaut hat und die Aufgabe nun verlagert wird – wer sorgt dann dafür, dass Betroffene '
                        .'tatsächlich Zugang zu geeigneten Sachverständigen bekommen?*',
                ],
            ],
            [
                'eyebrow' => 'Stand 17.10.2025',
                'titel' => 'Das vorgesehene Gutachterverzeichnis befindet sich noch „in der Entwicklung“',
                'absaetze' => [
                    'Bereits 2025 fragen wir bei der Bundesstelle für Soziale Entschädigung nach dem in § 124 SGB XIV '
                        .'vorgesehenen Gutachterverzeichnis.',
                    'Uns wird mitgeteilt, dass sich die Umsetzung noch in der Entwicklung befinde. Aufgrund umfangreicher '
                        .'Abstimmungen könne kein konkreter Zeitpunkt genannt werden, zu dem das Verzeichnis zur '
                        .'Verfügung stehen werde.',
                    'Damit beginnt für uns eine Recherche, die weit über eine einfache Suche nach einer Liste '
                        .'hinausgehen sollte.',
                ],
            ],
            [
                'titel' => 'Das Kernproblem',
                'absaetze' => [
                    'Unser Projekt macht ein grundlegendes Spannungsfeld sichtbar: Auf der einen Seite besteht im '
                        .'sozialgerichtlichen Verfahren eine gesetzliche Möglichkeit, nach *§ 109 SGG einen bestimmten '
                        .'Arzt anhören zu lassen*.',
                    'Auf der anderen Seite kann gerade bei speziellen Fragestellungen – beispielsweise einer '
                        .'aussagepsychologischen Glaubhaftigkeitsbegutachtung im Zusammenhang mit Gewalterfahrungen – die '
                        .'Suche nach einer geeigneten Person äußerst schwierig sein.',
                    'Hinzu kommt: Das im Sozialen Entschädigungsrecht vorgesehene Gutachterverzeichnis stand während '
                        .'unserer Recherche noch nicht zur Verfügung.',
                    'Damit kann aus einem theoretisch vorhandenen Recht eine praktische Hürde werden.',
                ],
            ],
            [
                'titel' => 'Was wir mit diesem Projekt erreichen möchten',
                'absaetze' => [
                    'Wir wollen nicht bei der Feststellung stehen bleiben, dass es schwierig ist. Wir möchten sichtbar '
                        ."machen, *wo genau das System für Betroffene nicht funktioniert*. Deshalb:\n"
                        ."- dokumentieren wir die Antworten der zuständigen Stellen,\n"
                        ."- fragen wir bei Ministerien und Gerichten nach,\n"
                        ."- sammeln wir mögliche Anlaufstellen,\n"
                        ."- verfolgen wir die Entwicklung des Gutachterverzeichnisses,\n"
                        .'- und machen auf strukturelle Lücken aufmerksam.',
                    'Langfristig wünschen wir uns eine *transparente und bundesweit einheitliche Orientierung*, mit der '
                        .'Betroffene tatsächlich erkennen können: *Wer ist für welche Begutachtung geeignet – und wer '
                        .'steht dafür tatsächlich zur Verfügung?*',
                    'Denn ein Recht darf nicht davon abhängen, ob ein betroffener Mensch die Kraft, das Wissen und die '
                        .'Möglichkeiten hat, selbst über Monate oder Jahre nach einer geeigneten Fachperson zu suchen.',
                    '*Rechte müssen nicht nur bestehen. Sie müssen auch erreichbar sein.*',
                    '*Gemeinsam KE!N EINZELFALL.*',
                ],
            ],
        ];
    }
}
