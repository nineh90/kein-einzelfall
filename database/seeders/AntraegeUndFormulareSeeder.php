<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Die fünf Bereiche aus Abschnitt 6.2 des Strukturpapiers („Anträge und
 * Formulare"): OEG, SER, GdB, Pflegegrad, Persönliches Budget.
 *
 * ── Warum hier Text steht und beim NeueBereicheSeeder nicht ────────────────
 *
 * Der Unterschied ist die Art des Inhalts. Ein Schutzkonzept ist eine
 * Selbstverpflichtung: Was darin steht, muss der Verein auch einhalten können
 * — das kann niemand für ihn erfinden. Diese fünf Seiten beschreiben dagegen
 * geltendes Recht. Das steht in amtlichen Quellen und lässt sich
 * zusammentragen, ohne dem Verein etwas in den Mund zu legen.
 *
 * Trotzdem ist es Text von uns und nicht vom Verein. Deshalb:
 *
 * 1. Jede Seite trägt oben einen unübersehbaren Entwurfsvermerk.
 * 2. Jede Seite ist auf `noindex` — ungeprüfte Rechtsauskunft gehört nicht in
 *    eine Suchmaschine. Wer sie über Google fände, käme ohne den Kontext
 *    „Entwurf" an und läse sie als verbindliche Auskunft.
 * 3. Keine Seite hängt im Menü (config/navigation.php). Sie sind über ihre
 *    Adresse erreichbar, damit der Verein sie im fertigen Layout gegenlesen
 *    kann — mehr nicht.
 *
 * Nach der Freigabe: Vermerk löschen, `noindex` abschalten, ins Menü nehmen.
 *
 * ── Warum das Risiko ernst zu nehmen ist ───────────────────────────────────
 *
 * Wer diese Seiten liest, steckt in der Regel in einem laufenden Verfahren.
 * Eine falsche Frist kostet hier keinen Komfort, sondern einen Anspruch. Jede
 * Angabe unten ist am 22.09.2026 an einer amtlichen oder fachlich
 * einschlägigen Quelle geprüft; die Quelle steht jeweils auf der Seite. Was
 * sich nicht belegen liess, steht nicht drin.
 *
 * Bewusst nirgends: Beträge, Tabellenwerte, Einzelfallbewertungen. Sie ändern
 * sich jährlich und wären der schnellste Weg zu einer veralteten Seite.
 */
class AntraegeUndFormulareSeeder extends Seeder
{
    /** Widerspruchsfrist — im Sozialrecht durchgehend ein Monat, § 84 SGG. */
    private const WIDERSPRUCH = [
        'typ' => 'hinweis',
        'data' => [
            'titel' => 'Ein Monat für den Widerspruch',
            'text' => 'Gegen einen Bescheid kannst du innerhalb eines Monats '
                .'nach seiner Bekanntgabe Widerspruch einlegen. Die Frist '
                .'läuft auch dann, wenn du noch auf Unterlagen wartest — ein '
                .'kurzer, fristwahrender Widerspruch reicht zunächst, die '
                .'Begründung kannst du nachreichen.',
            'art' => 'frist',
        ],
    ];

    public function run(): void
    {
        foreach (self::seiten() as $seite) {
            $datensatz = Page::updateOrCreate(
                ['slug' => $seite['slug'], 'locale' => 'de'],
                [
                    'titel' => $seite['titel'],
                    'meta_title' => $seite['titel'].' - Kein Einzelfall e.V.',
                    'meta_description' => $seite['beschreibung'],
                    // Sichtbar, damit der Verein im fertigen Layout gegenlesen
                    // kann — aber nicht auffindbar, solange ungeprüft.
                    'noindex' => true,
                    // Der Vermerk oben auf der Seite, siehe
                    // components/layout/entwurfsvermerk.blade.php.
                    'ungeprueft' => true,
                    'published_at' => now(),
                ],
            );

            $datensatz->blocks()->delete();

            foreach ($seite['bausteine'] as $position => $baustein) {
                $datensatz->blocks()->create([
                    'typ' => $baustein['typ'],
                    'position' => $position,
                    'data' => $baustein['data'],
                ]);
            }
        }

        $this->command?->info(count(self::seiten()).' Seiten aus Abschnitt 6.2 als Entwurf angelegt.');
    }

    /**
     * @return array<int, array{slug: string, titel: string, beschreibung: string, bausteine: array<int, array{typ: string, data: array<string, mixed>}>}>
     */
    public static function seiten(): array
    {
        return [
            self::opferentschaedigungsgesetz(),
            self::sozialesEntschaedigungsrecht(),
            self::gradDerBehinderung(),
            self::pflegegrad(),
            self::persoenlichesBudget(),
        ];
    }

    /**
     * 6.2.1 · OEG.
     *
     * Bewusst kurz und als Wegweiser gebaut: Das OEG ist seit dem 01.01.2024
     * im SGB XIV aufgegangen. Eine eigene, gleichwertige Seite daneben würde
     * zwei Fassungen desselben Rechts nebeneinanderstellen — und Betroffene
     * landen dann auf der falschen. Der Begriff „OEG" ist aber so eingeführt,
     * dass die Seite bleiben muss: Danach wird gesucht.
     */
    private static function opferentschaedigungsgesetz(): array
    {
        return [
            'slug' => 'opferentschaedigungsgesetz',
            'titel' => 'Opferentschädigungsgesetz (OEG)',
            'beschreibung' => 'Das OEG ist seit dem 1. Januar 2024 im SGB XIV '
                .'aufgegangen. Was das für laufende Verfahren und alte Bescheide bedeutet.',
            'bausteine' => [
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Das OEG gibt es so nicht mehr',
                        'absaetze' => [
                            'Das Opferentschädigungsgesetz wurde zum 1. Januar 2024 durch das '
                            .'Vierzehnte Buch Sozialgesetzbuch (SGB XIV) abgelöst. Seither heisst '
                            .'der Bereich Soziales Entschädigungsrecht, und alle neuen Anträge '
                            .'laufen darüber.',
                            'Der Begriff „OEG" ist trotzdem geblieben — in Bescheiden, in '
                            .'Beratungsstellen und in den Köpfen. Wer ihn kennt, sucht auch '
                            .'danach. Deshalb steht hier, was er heute noch bedeutet.',
                        ],
                        'cta' => [
                            'label' => 'Zum heutigen Recht: SGB XIV',
                            'url' => '/soziales-entschaedigungsrecht',
                            'variant' => 'primary',
                        ],
                    ],
                ],
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Wann das alte Recht noch gilt',
                        'absaetze' => [
                            'Für Anträge, die vor dem 1. Januar 2024 gestellt wurden, wird '
                            .'weiterhin nach dem OEG geprüft. Ein laufendes Verfahren wechselt '
                            .'also nicht mitten im Lauf die Grundlage.',
                            'Wer vor 2024 bereits Leistungen bezogen hat, wurde automatisch in '
                            .'das neue Recht überführt. Die Bescheide gelten weiter, die '
                            .'Zahlungen liefen ohne Unterbrechung weiter, und ein neuer Antrag '
                            .'war dafür nicht nötig.',
                        ],
                    ],
                ],
                [
                    'typ' => 'hinweis',
                    'data' => [
                        'titel' => 'Ein Blick auf den alten Bescheid kann sich lohnen',
                        'text' => 'Das SGB XIV kennt Leistungen, die es im OEG nicht gab — '
                            .'allen voran die Traumaambulanz. Wer einen alten Bescheid hat, '
                            .'sollte prüfen lassen, ob die neuen Angebote infrage kommen.',
                        'art' => 'hinweis',
                        'link' => [
                            'label' => 'Was das SGB XIV heute bietet',
                            'url' => '/soziales-entschaedigungsrecht',
                        ],
                    ],
                ],
                self::quelle('https://www.bmas.de/DE/Soziales/Soziale-Entschaedigung/soziale-entschaedigung.html', 'Soziale Entschädigung beim BMAS'),
            ],
        ];
    }

    /** 6.2.2 · SER. Die eigentliche Hauptseite des Themas. */
    private static function sozialesEntschaedigungsrecht(): array
    {
        return [
            'slug' => 'soziales-entschaedigungsrecht',
            'titel' => 'Soziales Entschädigungsrecht (SGB XIV)',
            'beschreibung' => 'Entschädigung nach einer Gewalttat: Wer Anspruch hat, '
                .'was die Traumaambulanz leistet und wie der Antrag läuft.',
            'bausteine' => [
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Worum es geht',
                        'absaetze' => [
                            'Wer durch eine Gewalttat gesundheitliche Schäden erlitten hat, kann '
                            .'Leistungen der Sozialen Entschädigung erhalten. Geregelt ist das '
                            .'seit dem 1. Januar 2024 im SGB XIV, das das '
                            .'Opferentschädigungsgesetz abgelöst hat.',
                            'Der Gedanke dahinter: Der Staat konnte die Tat nicht verhindern und '
                            .'tritt deshalb für die Folgen ein. Es geht nicht um Schadenersatz '
                            .'von der Täterin oder dem Täter — dieser Weg bleibt davon unberührt.',
                        ],
                    ],
                ],
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Auch psychische Gewalt zählt',
                        'absaetze' => [
                            'Das ist die wichtigste Neuerung gegenüber dem OEG. Das alte Recht '
                            .'verlangte einen tätlichen Angriff — also körperliche Gewalt. Das '
                            .'SGB XIV erfasst darüber hinaus auch schwerwiegende psychische '
                            .'Gewalt, etwa erhebliche Drohungen oder Nachstellungen mit '
                            .'gesundheitlichen Folgen.',
                            'Wer früher deshalb abgelehnt wurde oder gar nicht erst einen Antrag '
                            .'gestellt hat, sollte die Frage neu stellen.',
                        ],
                    ],
                ],
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Die Traumaambulanz — Hilfe, bevor irgendetwas entschieden ist',
                        'absaetze' => [
                            'Die Traumaambulanz ist psychische Erste Hilfe und das Angebot, das '
                            .'am schnellsten greift. Sie setzt keinen bewilligten Antrag voraus: '
                            .'Es genügt eine überschlägige Prüfung, dass jemand betroffen sein '
                            .'könnte.',
                            'Möglich sind bis zu 15 Sitzungen. Die erste soll innerhalb von zwölf '
                            .'Monaten nach der Tat stattfinden; liegt die Tat länger zurück, '
                            .'zählen zwölf Monate ab dem Auftreten der akuten Belastung. '
                            .'Fahrtkosten und Begleitpersonen werden übernommen.',
                            'Wird der Antrag später abgelehnt, musst du die Kosten der '
                            .'Traumaambulanz nicht zurückzahlen.',
                        ],
                    ],
                ],
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Der Antrag',
                        'absaetze' => [
                            'Zuständig sind die Versorgungsbehörden der Bundesländer. Eine '
                            .'Antragsfrist gibt es nicht — die Tat darf also auch lange '
                            .'zurückliegen.',
                            'Wichtig ist trotzdem der Zeitpunkt: Leistungen werden grundsätzlich '
                            .'erst ab dem Antrag erbracht, nicht rückwirkend. Wer überlegt, ob er '
                            .'einen Antrag stellt, verliert mit jedem Monat Wartezeit Leistungen, '
                            .'die er sonst bekommen hätte.',
                        ],
                    ],
                ],
                self::WIDERSPRUCH,
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Du musst das nicht allein durchstehen',
                        'absaetze' => [
                            'Ein Antragsverfahren im Entschädigungsrecht dauert oft Jahre und '
                            .'verlangt, die Tat immer wieder zu schildern. Genau dafür gibt es '
                            .'uns: für den Austausch mit Menschen, die dasselbe hinter sich '
                            .'haben, und für Orientierung, wenn der nächste Schritt unklar ist.',
                        ],
                        'cta' => [
                            'label' => 'Anfragen & Austausch',
                            'url' => '/anfragen',
                            'variant' => 'primary',
                        ],
                    ],
                ],
                self::quelle('https://www.bmas.de/DE/Soziales/Soziale-Entschaedigung/Fragen-und-Antworten/faq-ser.html', 'Fragen und Antworten beim BMAS'),
            ],
        ];
    }

    /** 6.2.3 · GdB. Hier gehört der REHADAT-Verweis hin, siehe docs/KEV-4-REHADAT.md. */
    private static function gradDerBehinderung(): array
    {
        return [
            'slug' => 'grad-der-behinderung',
            'titel' => 'Grad der Behinderung (GdB) und Schwerbehindertenausweis',
            'beschreibung' => 'Wie der GdB festgestellt wird, wann es einen '
                .'Schwerbehindertenausweis gibt und wie lange das dauert.',
            'bausteine' => [
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Was der GdB ist',
                        'absaetze' => [
                            'Der Grad der Behinderung beschreibt, wie stark sich '
                            .'gesundheitliche Beeinträchtigungen auf die Teilhabe am Leben in '
                            .'der Gesellschaft auswirken. Er wird in Zehnerschritten von 20 bis '
                            .'100 festgestellt.',
                            'Ab einem GdB von 50 gilt man als schwerbehindert und bekommt einen '
                            .'Schwerbehindertenausweis. Daran hängen Nachteilsausgleiche — vom '
                            .'Kündigungsschutz bis zu steuerlichen Freibeträgen.',
                            'Der GdB ist kein Prozentwert und keine Aussage über den Wert eines '
                            .'Menschen. Er ist eine Verwaltungsgrösse, an die Rechte geknüpft '
                            .'sind.',
                        ],
                    ],
                ],
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Beantragt wird die Feststellung, nicht der Ausweis',
                        'absaetze' => [
                            'Das klingt nach Wortklauberei, ist aber der Kern des Verfahrens: '
                            .'Beim Versorgungsamt beantragt man die Feststellung einer '
                            .'Behinderung nach § 152 SGB IX. Der Ausweis ist nur die Folge, wenn '
                            .'mindestens ein GdB von 50 herauskommt.',
                            'Das Amt fordert die Befunde bei den behandelnden Ärztinnen und '
                            .'Ärzten an und bewertet sie nach den Versorgungsmedizinischen '
                            .'Grundsätzen. Eine persönliche Untersuchung findet meist nicht '
                            .'statt.',
                            'Die Bearbeitung dauert je nach Bundesland und Fall in der Regel zwei '
                            .'bis sechs Monate. Die Feststellung wirkt auf den Monat der '
                            .'Antragstellung zurück — ein früher Antrag ist deshalb nie verkehrt.',
                        ],
                    ],
                ],
                self::WIDERSPRUCH,
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Wie andere Verfahren ausgegangen sind',
                        'absaetze' => [
                            'Ob sich ein Widerspruch lohnt, lässt sich leichter einschätzen, '
                            .'wenn man vergleichbare Fälle kennt. Zu GdB, Merkzeichen und '
                            .'Feststellungsverfahren sammelt REHADAT-Recht die Rechtsprechung — '
                            .'ein vom Bundesministerium für Arbeit und Soziales gefördertes '
                            .'Angebot mit über 16.000 Urteilen.',
                            'Wichtige Entscheidungen sind dort zusätzlich in Einfacher Sprache '
                            .'erklärt. Ein Urteil zu einem ähnlichen Fall ist keine Zusage für '
                            .'den eigenen — aber es zeigt, worauf Gerichte geachtet haben.',
                        ],
                        'cta' => [
                            'label' => 'Zu REHADAT-Recht',
                            'url' => 'https://www.rehadat-recht.de/rechtsprechung/',
                            'variant' => 'ghost',
                        ],
                    ],
                ],
                self::quelle('https://www.rehadat-recht.de/rechtsprechung/feststellungsverfahren/', 'Feststellungsverfahren bei REHADAT-Recht'),
            ],
        ];
    }

    /** 6.2.4 · Pflegegrad. */
    private static function pflegegrad(): array
    {
        return [
            'slug' => 'pflegegrad',
            'titel' => 'Pflegegrad',
            'beschreibung' => 'Antrag bei der Pflegekasse, Begutachtung durch den '
                .'Medizinischen Dienst und die Frist von 25 Arbeitstagen.',
            'bausteine' => [
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Fünf Pflegegrade',
                        'absaetze' => [
                            'Die Pflegeversicherung kennt die Pflegegrade 1 bis 5. Massstab ist '
                            .'nicht eine Diagnose, sondern die Selbstständigkeit: Was kann '
                            .'jemand noch allein, wobei braucht er Hilfe.',
                            'Begutachtet werden dabei ausdrücklich auch psychische '
                            .'Beeinträchtigungen und Verhaltensweisen — nicht nur körperliche '
                            .'Einschränkungen. Das wird oft unterschätzt.',
                        ],
                    ],
                ],
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Der Weg zum Bescheid',
                        'absaetze' => [
                            'Der Antrag geht an die Pflegekasse, die bei der eigenen Krankenkasse '
                            .'sitzt. Er ist formlos möglich — schriftlich, online, telefonisch '
                            .'oder persönlich. Ein Satz genügt für den Anfang.',
                            'Die Pflegekasse beauftragt dann den Medizinischen Dienst mit der '
                            .'Begutachtung. Sie findet in der Regel zu Hause statt und schaut '
                            .'sich den Alltag an.',
                            'Von der Antragstellung bis zum Bescheid hat die Pflegekasse '
                            .'grundsätzlich 25 Arbeitstage Zeit. Zusammen mit dem Bescheid '
                            .'kannst du das Gutachten anfordern — wer widersprechen will, '
                            .'braucht es.',
                        ],
                    ],
                ],
                [
                    'typ' => 'hinweis',
                    'data' => [
                        'titel' => 'Ein Pflegetagebuch hilft bei der Begutachtung',
                        'text' => 'Der Begutachtungstermin ist eine Momentaufnahme, und gerade '
                            .'an schlechten Tagen fällt es schwer, den eigenen Alltag zu '
                            .'schildern. Wer vorher ein bis zwei Wochen notiert, wobei er Hilfe '
                            .'brauchte und wie lange sie dauerte, hat etwas in der Hand.',
                        'art' => 'hinweis',
                    ],
                ],
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Wenn der Pflegegrad zu niedrig ausfällt',
                        'absaetze' => [
                            'Das kommt vor, und es liegt nicht immer an der Begutachtung: Ein '
                            .'guter Tag, ein aufgeräumter Eindruck oder die Gewohnheit, sich '
                            .'nichts anmerken zu lassen, können das Bild verschieben.',
                            'Fordere zuerst das Gutachten an und lies nach, was dort bewertet '
                            .'wurde. Oft findet sich eine Stelle, an der etwas fehlt oder zu '
                            .'gut eingeschätzt wurde — das ist der Ansatzpunkt für den '
                            .'Widerspruch.',
                        ],
                    ],
                ],
                self::WIDERSPRUCH,
                self::quelle('https://www.medizinischerdienst.de/versicherte/pflegebegutachtung', 'Pflegebegutachtung beim Medizinischen Dienst'),
            ],
        ];
    }

    /** 6.2.5 · Persönliches Budget. */
    private static function persoenlichesBudget(): array
    {
        return [
            'slug' => 'persoenliches-budget',
            'titel' => 'Persönliches Budget',
            'beschreibung' => 'Teilhabeleistungen als Geldbetrag statt als Sachleistung — '
                .'Antrag, Zielvereinbarung und zuständiger Träger.',
            'bausteine' => [
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Selbst entscheiden statt zugeteilt bekommen',
                        'absaetze' => [
                            'Leistungen zur Teilhabe bekommt man normalerweise als Sachleistung: '
                            .'Ein Träger sucht den Dienst aus und rechnet mit ihm ab. Beim '
                            .'Persönlichen Budget bekommt man stattdessen einen Geldbetrag und '
                            .'organisiert die Hilfe selbst.',
                            'Geregelt ist das in § 29 SGB IX. Der Zweck steht im Gesetz '
                            .'ausdrücklich: ein möglichst selbstbestimmtes Leben in eigener '
                            .'Verantwortung.',
                        ],
                    ],
                ],
                [
                    'typ' => 'text',
                    'data' => [
                        'titel' => 'Antrag und Zielvereinbarung',
                        'absaetze' => [
                            'Das Budget gibt es nur auf Antrag. Beteiligt sind je nach Bedarf die '
                            .'Rehabilitationsträger, die Pflegekassen und die Integrationsämter — '
                            .'zuständig für das Verfahren ist der leistende Rehabilitationsträger '
                            .'nach § 14 SGB IX.',
                            'Vor der Bewilligung wird eine Zielvereinbarung geschlossen: Sie hält '
                            .'fest, wofür das Budget da ist und wie die Verwendung nachgewiesen '
                            .'wird. Sie gilt für den Bewilligungszeitraum.',
                            'Das Budget bedeutet Freiheit und Verantwortung zugleich — wer damit '
                            .'Assistenz einkauft, wird unter Umständen zur Arbeitgeberin. Eine '
                            .'Beratung vorher ist keine Formalie.',
                        ],
                    ],
                ],
                self::WIDERSPRUCH,
                self::quelle('https://www.gesetze-im-internet.de/sgb_9_2018/__29.html', '§ 29 SGB IX im Wortlaut'),
            ],
        ];
    }

    /**
     * Quellenangabe am Fuss jeder Seite.
     *
     * Nicht Zierde, sondern Pflicht: Wer eine Rechtsauskunft liest, muss
     * nachsehen können, worauf sie beruht — und die amtliche Quelle ist
     * aktuell, wenn diese Seite es längst nicht mehr ist.
     *
     * Ein Textbaustein und kein Hinweis-Kasten: Ein Hinweis bleibt auf der
     * Fläche des Abschnitts davor, weil er zu ihm gehört. Zwei Kästen
     * hintereinander ergäben deshalb zweimal dieselbe Fläche — und sähen aus
     * wie ein Kasten, der nicht aufhören will.
     *
     * @return array{typ: string, data: array<string, mixed>}
     */
    private static function quelle(string $url, string $name): array
    {
        return [
            'typ' => 'text',
            'data' => [
                'titel' => 'Woher diese Angaben stammen',
                'absaetze' => [
                    'Zusammengetragen am 22. September 2026 aus der amtlichen Quelle. '
                    .'Rechtsstand und Zuständigkeiten ändern sich — im Zweifel gilt die '
                    .'Quelle und nicht diese Seite.',
                ],
                'cta' => ['label' => $name, 'url' => $url, 'variant' => 'ghost'],
            ],
        ];
    }
}
