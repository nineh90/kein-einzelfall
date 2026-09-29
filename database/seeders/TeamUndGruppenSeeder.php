<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Page;
use App\Models\TeamMember;
use App\Support\Bild;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Überführt Vorstand und Gruppen aus dem Fliesstext in eigene Datensätze.
 *
 * Grundlage ist docs/altseite-inhalt.json — der Abzug der Altseite. Auf
 * /ueber-uns-vorstand-und-team folgt dort je Person eine Abfolge aus Rolle,
 * Name, Kurzangaben und vielen Absätzen. Genau diese Reihenfolge wird hier
 * ausgewertet.
 *
 * Es wird nichts umformuliert und nichts ergänzt: Namen, Rollen und Texte
 * stammen wörtlich vom Verein. Nur die Zuordnung — welcher Absatz zu welcher
 * Person gehört — treffen wir.
 */
class TeamUndGruppenSeeder extends Seeder
{
    public function run(): void
    {
        $this->team();
        $this->gruppen();
        $this->seitenNeuAufbauen();
    }

    /**
     * Vorstand und Team.
     *
     * Erkennungsmuster: Ein Block ohne Absätze ist eine Rollenangabe
     * („1. Vorsitzende"), der folgende Block ohne Absätze der Name — an ihm
     * hängt das Porträt —, der nächste mit Absätzen die Vorstellung samt
     * Kurzangaben als Titel.
     *
     * Öffentlich, damit eine Migration den Bestand nachziehen kann: Der Abzug
     * vom Juli hatte durch einen Fehler im Importer vier von sieben Personen
     * verloren und deren Texte den übrigen zugeschlagen (siehe
     * AltseiteHolen::bloecke). `updateOrCreate` über den Namen bereinigt das
     * — und überschreibt damit auch, was im Panel an einer Person geändert
     * wurde. Für diese Bereinigung ist das richtig; danach gehört das Panel
     * wieder dem Verein.
     */
    public function team(): void
    {
        ['personen' => $personen] = self::teamAusAbzug();

        foreach ($personen as $i => $person) {
            // Nur Fotos, die tatsächlich geholt wurden (`bilder:holen`). Ein
            // Pfad ins Leere zeigte ein kaputtes Bild statt der Initialen.
            $foto = isset($person['bild']['src']) ? Bild::lokal($person['bild']['src']) : null;

            TeamMember::updateOrCreate(['name' => $person['name']], [
                'rolle' => $person['rolle'],
                'untertitel' => $person['untertitel'],
                'foto_pfad' => $foto,
                'foto_alt' => $foto ? ($person['bild']['alt'] ?: $person['name']) : null,
                // Der erste ganze Satz als Kurzfassung — nicht ein Stichpunkt
                // wie „Betroffene in verschiedenen Kontexten", mit dem
                // Franziska Künstlers Vorstellung auf der Altseite beginnt.
                // Stichpunkte enden ohne Satzzeichen.
                'kurzprofil' => Str::limit(
                    collect($person['absaetze'])->first(fn ($a) => preg_match('/[.!?…“"]$/u', $a)) ?? $person['absaetze'][0],
                    260
                ),
                'profil' => collect($person['absaetze'])
                    ->map(fn ($a) => '<p>'.e($a).'</p>')
                    ->implode("\n"),
                'bereich' => $person['bereich'],
                'position' => $i,
                'published_at' => now(),
            ]);
        }

        foreach (self::NEU_IM_TEAM as $j => $person) {
            TeamMember::updateOrCreate(['name' => $person['name']], $person + [
                'bereich' => self::BEREICH_TEAM,
                'position' => count($personen) + $j,
                'published_at' => now(),
            ]);
        }

        $this->command?->info(count($personen).' Personen übernommen.');
    }

    /**
     * Neu im Team, also nicht auf der Altseite. Stehen hinter den übernommenen
     * Personen. Bestehende Datenbanken bekommen sie per Migration.
     */
    public const NEU_IM_TEAM = [
        // KEV-67: Nils wollte den Eintrag als „IT und Webdesign“, Bild erst
        // mal von der Homepage (das Logo).
        [
            'name' => 'Nils-Digital',
            'rolle' => 'IT und Webdesign',
            'foto_pfad' => '/img/team/nils-digital.png',
            'foto_alt' => 'Logo von Nils-Digital',
            'untertitel' => 'Umsetzung und technische Betreuung dieser Website',
            'kurzprofil' => 'Wir sind Nils-Digital und haben die neue Website von KE!N EINZELFALL e.V. gebaut.',
            // Text von Nils und Kevin, abgestimmt am 29.09.2026
            'profil' => '<p>Wir sind Nils-Digital und haben die neue Website von KE!N EINZELFALL e.V. gebaut. '
                .'Auch danach kümmern wir uns um die Technik dahinter.</p>'."\n"
                .'<p>Eine Website für Betroffene muss mehr leisten als gut aussehen. Wer hier liest, soll sich '
                .'sicher fühlen. Genauso wichtig ist uns, dass alle die Seite nutzen können und daher haben wir '
                .'einen großen Wert auf Barrierefreiheit gesetzt.</p>'."\n"
                .'<p>Hinter Nils-Digital stehen Nils Nehring und Kevin Herrmann. Wir entwickeln Websites, Apps und '
                .'KI-Automatisierungen für Unternehmen, Selbstständige und Vereine. Jedes Projekt betreuen wir '
                .'persönlich, mit kurzen Wegen und klaren Absprachen.</p>'."\n"
                .'<p>Wir freuen uns, dass wir den Verein auf diesem Weg begleiten dürfen. Denn je leichter '
                .'Betroffene hier finden, was sie suchen, desto eher merken sie: Sie sind kein Einzelfall.</p>',
        ],
    ];

    /**
     * Personen und Zwischentexte der Teamseite aus dem Abzug.
     *
     * Die Altseite gliedert in drei Gruppen, ohne sie zu überschreiben: den
     * Vorstand, dann — nach einer Überleitung — die weiteren
     * Gründungsmitglieder und Ehrenamtlichen, zuletzt das stellvertretende
     * Porträt für die Menschen im Hintergrund. Die Überleitungen stehen als
     * Absätze zwischen den Personen; der Importer kann sie nur der vorigen
     * Person zuschlagen. Hier werden sie wieder herausgelöst, als Text der
     * Seite, der nach dieser Person kommt — sonst spräche Petra Hildebrandt
     * in ihrem Profil über „viele Menschen, die uns unterstützen".
     *
     * @return array{personen: list<array<string, mixed>>, zwischentexte: array<string, list<string>>}
     */
    public static function teamAusAbzug(): array
    {
        $inhalt = json_decode((string) @file_get_contents(base_path('docs/altseite-inhalt.json')), true);
        $bloecke = $inhalt['/ueber-uns-vorstand-und-team/']['bloecke'] ?? [];

        $personen = [];
        $zwischentexte = [];
        $rolle = null;
        $name = null;
        $bild = null;

        foreach ($bloecke as $block) {
            $titel = trim((string) ($block['titel'] ?? ''));
            $absaetze = $block['absaetze'] ?? [];

            if ($titel === '') {
                continue;
            }

            // Überschrift ohne Text: entweder Rolle oder Name
            if ($absaetze === []) {
                if ($rolle === null) {
                    $rolle = $titel;
                } else {
                    $name = $titel;
                    $bild = $block['bild'] ?? null;
                }

                continue;
            }

            // Überschrift mit Text: die Vorstellung der zuletzt genannten Person
            if ($name !== null) {
                [$eigene, $seitentext] = self::seitentextAbtrennen($absaetze);

                $personen[] = [
                    'name' => $name,
                    'rolle' => $rolle,
                    'untertitel' => $titel,
                    'absaetze' => $eigene,
                    'bild' => $bild,
                    'bereich' => self::bereich($rolle),
                ];

                if ($seitentext !== []) {
                    $zwischentexte[$name] = $seitentext;
                }

                $rolle = null;
                $name = null;
                $bild = null;
            }
        }

        /*
         * Eine Stelle, an der die Zuordnung nicht aufgeht: „Herr und Frau
         * Unbekannt" ist kein Vorstandsmitglied, sondern ein stellvertretendes
         * Porträt für die Menschen, die im Hintergrund mitarbeiten. Die
         * darüberstehende Überschrift „Gemeinsam KE!N EINZELFALL" ist deshalb
         * keine Rollenbezeichnung. Ausdrücklich korrigiert statt die Heuristik
         * zu verbiegen — im Panel lässt sich beides jederzeit ändern.
         */
        foreach ($personen as &$person) {
            if ($person['name'] === 'Herr und Frau Unbekannt') {
                $person['rolle'] = null;
                $person['bereich'] = self::BEREICH_HINTERGRUND;
            }

            // André Bauers erster Satz steht auf der Altseite in zwei <p>
            // („Ich engagiere mich ehrenamtlich bei" / „KE!N EINZELFALL e.V.,
            // weil …") — ein Umbruch, kein Absatz. Zusammengefügt, damit die
            // Kurzfassung nicht mitten im Satz beginnt. Keine allgemeine
            // Regel: Die Altseite setzt auch Adressen und Listen als kurze <p>.
            if ($person['name'] === 'André Bauer'
                && str_ends_with($person['absaetze'][0] ?? '', ' bei')
                && count($person['absaetze']) > 1) {
                $person['absaetze'] = [
                    $person['absaetze'][0].' '.$person['absaetze'][1],
                    ...array_slice($person['absaetze'], 2),
                ];
            }
        }
        unset($person);

        return ['personen' => $personen, 'zwischentexte' => $zwischentexte];
    }

    public const BEREICH_VORSTAND = 'Vorstand';

    public const BEREICH_TEAM = 'Team';

    public const BEREICH_HINTERGRUND = 'Im Hintergrund';

    /**
     * Vorstand ist, wer ein Vorstandsamt trägt. Alle anderen — Landesstellen,
     * Beauftragte, Ehrenamtliche — sind „Team": Die Altseite trennt genau so,
     * mit der Überleitung „Darüber hinaus gibt es viele Menschen …".
     */
    private static function bereich(?string $rolle): string
    {
        return preg_match('/vorsitzende|kassenwart|schriftf|beisitz/iu', (string) $rolle)
            ? self::BEREICH_VORSTAND
            : self::BEREICH_TEAM;
    }

    /**
     * Absätze, die auf der Altseite zwischen den Personen stehen — und nicht
     * zur Person darüber gehören.
     *
     * @param  list<string>  $absaetze
     * @return array{list<string>, list<string>}  [eigene Absätze, Seitentext]
     */
    private static function seitentextAbtrennen(array $absaetze): array
    {
        $anfaenge = [
            'Darüber hinaus gibt es viele Menschen, die uns',
            'Ohne die Gründungsmitglieder, die zum Teil auch unsere Landesstellen',
            'Zusätzlich arbeiten im Hintergrund viele Ehrenamtliche',
        ];

        $istSeitentext = fn ($a) => collect($anfaenge)->contains(fn ($s) => str_starts_with($a, $s));

        return [
            array_values(array_filter($absaetze, fn ($a) => ! $istSeitentext($a))),
            array_values(array_filter($absaetze, $istSeitentext)),
        ];
    }

    /**
     * Die Teamseite so zusammensetzen, wie die Altseite gliedert: Einleitung,
     * Vorstand, Überleitung, Team, Hinweis auf die Ehrenamtlichen, das
     * stellvertretende Porträt. Ein einzelner Baustein mit allen Personen
     * hätte für die Überleitungen keinen Platz.
     *
     * Öffentlich für die Migration. Die Einleitung bleibt, wie sie in der
     * Datenbank steht — sie kann im Panel bearbeitet worden sein.
     */
    public function teamseiteAufbauen(): void
    {
        $seite = Page::where('slug', 'ueber-uns-vorstand-und-team')->where('locale', 'de')->first();

        if (! $seite) {
            return;
        }

        ['personen' => $personen, 'zwischentexte' => $zwischentexte] = self::teamAusAbzug();

        $einleitung = $seite->blocks()->where('typ', 'text')->orderBy('position')->first();

        $neu = [];
        if ($einleitung) {
            $neu[] = ['typ' => 'text', 'data' => $einleitung->data];
        }

        // Bereiche in der Reihenfolge ihres ersten Auftretens; die
        // Überleitung folgt auf die Gruppe, in der sie auf der Altseite steht.
        $gruppen = collect($personen)->groupBy('bereich');

        foreach ($gruppen as $bereich => $mitglieder) {
            // „Vorstandsebene“ über den Vorstandskarten (KEV-65). Das Team hat
            // seine Überschrift in der Überleitung davor (KEV-66).
            $neu[] = ['typ' => 'team_grid', 'data' => $bereich === self::BEREICH_VORSTAND
                ? ['titel' => 'Vorstandsebene', 'bereich' => $bereich]
                : ['bereich' => $bereich]];

            foreach ($mitglieder as $person) {
                // Ein Absatz, auch wenn die Altseite ihn in zwei zerlegt hatte:
                // So hat Taddi ihn für die Überleitung zum Team vorgegeben
                // (KEV-66). Zwei Absätze setzte der Baustein ungleich, den
                // ersten dunkler und grösser als Einstieg.
                //
                // Die Überleitung vor dem Team bekommt die Überschrift „Team“
                // (KEV-66), damit der Abschnitt nicht namenlos zwischen den
                // Karten steht. Erkannt am Anfang des Textes.
                if (isset($zwischentexte[$person['name']])) {
                    $absatz = implode(' ', $zwischentexte[$person['name']]);
                    $data = str_starts_with($absatz, 'Darüber hinaus gibt es viele Menschen')
                        ? ['titel' => 'Team', 'absaetze' => [$absatz]]
                        : ['absaetze' => [$absatz]];
                    $neu[] = ['typ' => 'text', 'data' => $data];
                }
            }
        }

        $seite->blocks()->delete();

        foreach ($neu as $position => $block) {
            $seite->blocks()->create($block + ['position' => $position]);
        }
    }

    /**
     * Gruppen.
     *
     * Namen, Termine und Beschreibungen stehen wörtlich so auf der Altseite.
     * Sie hier auszuschreiben ist ehrlicher als eine Heuristik: Die Seite
     * mischt Gruppen, Regeln und Spendenaufrufe in einer Überschriftenfolge,
     * die sich nicht zuverlässig maschinell trennen lässt.
     */
    private function gruppen(): void
    {
        foreach (self::gruppenliste() as $i => $gruppe) {
            Group::updateOrCreate(
                ['slug' => $gruppe['slug']],
                array_merge($gruppe, ['position' => $i, 'published_at' => now()])
            );
        }

        $this->command?->info(count(self::gruppenliste()).' Gruppen übernommen.');
    }

    /** Kontaktzeile am Ende jeder AG-Beschreibung. */
    public const AG_KONTAKT = 'Du hast Interesse? Schreibe uns einfach kurz an: arbeitsgruppe@kein-einzelfall.de';

    /** Woran die Arbeitsgruppen-Seite der Altseite zu erkennen ist. */
    public const ARBEITSGRUPPEN_ALT = [
        'alt_erster_absatz' => 'Mach mit – mit Fachwissen, Kreativität oder einfach dem Wunsch, etwas zu bewegen.',
        'alt_so_dabei' => 'So bist Du dabei:',
    ];

    /**
     * Die Arbeitsgruppen, wie Taddi sie am 29.09.2026 geschickt hat (KEV-74).
     *
     * Der erste Satz jeder AG ist die Kurzbeschreibung auf der Karte, der
     * letzte der handschriftliche Schlusssatz, dazwischen der aufklappbare
     * Text. Korrigiert: „dazukommen“ zusammen, Komma vor „etwas zu
     * verändern“, „IFG vs.“ mit Punkt.
     *
     * Die Slugs bleiben, wo es die AG schon gab. Die App war bis dahin
     * AG 07 („Traumabegleiter“), jetzt AG Nr. 8.
     */
    public const ARBEITSGRUPPEN = [
        [
            'slug' => 'ag-01-ser', 'kuerzel' => 'AG Nr. 1',
            'name' => 'SER (OEG/SGB XIV) vs. Best Practice und Worst Case',
            'teaser' => 'Wie erleben Betroffene das Soziale Entschädigungsrecht wirklich?',
            'absaetze' => [
                'In dieser Arbeitsgruppe schauen wir auf Erfahrungen aus Verfahren nach OEG und SGB XIV – auf das, was gut läuft, aber auch auf Hürden, Belastungen und wiederkehrende Probleme.',
                'Dafür werden anonymisierte Erfahrungen und Verfahrensakten aus verschiedenen Bundesländern ausgewertet. So möchten wir sichtbar machen, wo gute Praxis funktioniert und wo Betroffene immer wieder an ähnliche Grenzen stoßen.',
                'Uns geht es darum, aus einzelnen Erfahrungen ein größeres Bild entstehen zu lassen – damit Missstände nicht als Einzelfälle verschwinden und gute Lösungen dort sichtbar werden, wo sie bereits funktionieren.',
                'Du kannst jederzeit dazukommen, egal ob mit Wissen, Erfahrung, Kreativität oder dem Wunsch, etwas zu verändern.',
            ],
            'schlusssatz' => 'Wir machen sichtbar, was sonst in einzelnen Akten verschwindet.',
        ],
        [
            'slug' => 'ag-02-ifg', 'kuerzel' => 'AG Nr. 2',
            'name' => 'IFG vs. offene Fragen zum SER',
            'teaser' => 'Wir fragen nach, weil wir verstehen wollen.',
            'absaetze' => [
                'Im Sozialen Entschädigungsrecht bleiben für Betroffene viele Fragen offen. Abläufe sind schwer nachvollziehbar, Informationen fehlen oder unterscheiden sich – und oft ist unklar, warum Entscheidungen so getroffen werden, wie sie getroffen werden.',
                'In dieser Arbeitsgruppe sammeln wir genau diese offenen Fragen und bringen sie strukturiert zusammen. Über Anfragen nach dem Informationsfreiheitsgesetz wollen wir Antworten bekommen, Zusammenhänge besser verstehen und sichtbar machen, wie das SER in der Praxis umgesetzt wird.',
                'Aus den Antworten wollen wir herausarbeiten, wo gute Lösungen bereits funktionieren und wo Verfahren für Betroffene besonders belastend oder problematisch sind. So sollen nach und nach Best Practice und Worst Case sichtbar werden.',
                'Mitmachen kannst du mit eigenen Fragen, Erfahrungen oder bei Recherche, Strukturierung und Auswertung.',
                'Du kannst jederzeit dazukommen, egal ob mit Wissen, Erfahrung, Kreativität oder dem Wunsch, etwas zu verändern.',
            ],
            'schlusssatz' => 'Wir fragen nach, weil Verstehen der erste Schritt ist, um Unterschiede zu erkennen und Veränderung möglich zu machen.',
        ],
        [
            'slug' => 'ag-03-online-veranstaltungen', 'kuerzel' => 'AG Nr. 3',
            'name' => 'Veranstaltungsplanung',
            'teaser' => 'Aus einer Idee wird ein Termin – und aus einem Termin ein Raum für Austausch, Wissen und Begegnung.',
            'absaetze' => [
                'In dieser Arbeitsgruppe planen und entwickeln wir die Veranstaltungen von KE!N EINZELFALL. Gemeinsam sammeln wir Themen, überlegen passende Formate, suchen Referentinnen und Referenten und kümmern uns um die vielen kleinen Schritte, die aus einer Idee eine gute Veranstaltung machen.',
                'Ob Vortrag, Workshop, Gesprächsrunde, Infoabend oder neues Beteiligungsformat – hier darf mitgedacht, organisiert und ausprobiert werden. Dabei geht es nicht nur um den Ablauf, sondern auch darum, Veranstaltungen so zu gestalten, dass sie verständlich, zugänglich und möglichst angenehm für die Teilnehmenden sind.',
                'Du kannst dich mit Ideen, Organisationstalent, Recherche, Technik, Moderation oder einfach mit Interesse einbringen.',
                'Du kannst jederzeit dazukommen, egal ob mit Wissen, Erfahrung, Kreativität oder dem Wunsch, etwas zu verändern.',
            ],
            'schlusssatz' => 'Gute Veranstaltungen entstehen nicht einfach – sie wachsen aus vielen Ideen, Perspektiven und Menschen, die sie gemeinsam möglich machen.',
        ],
        [
            'slug' => 'ag-04-soziale-medien', 'kuerzel' => 'AG Nr. 4',
            'name' => 'Soziale Medien',
            'teaser' => 'Sichtbarkeit entsteht nicht von allein.',
            'absaetze' => [
                'In dieser Arbeitsgruppe entwickeln wir gemeinsam die Inhalte für unsere Social-Media-Kanäle. Wir überlegen, welche Themen wichtig sind, wie wir sie verständlich aufbereiten und wie wir Betroffenenperspektiven, Wissen, Vereinsarbeit und aktuelle Entwicklungen sichtbar machen können.',
                'Dabei entstehen Beiträge, Reels, Storys und neue Formate zu Themen wie Trauma, Selbsthilfe, Soziales Entschädigungsrecht, Pflege, GdB, Veranstaltungen, Vereinsnews und „Deine Stimme“.',
                'Du kannst dich mit Ideen, Texten, Gestaltung, Recherche, Video, Planung oder einfach mit deinem Blick auf ein Thema einbringen.',
                'Du kannst jederzeit dazukommen, egal ob mit Wissen, Erfahrung, Kreativität oder dem Wunsch, etwas zu verändern.',
            ],
            'schlusssatz' => 'Denn Sichtbarkeit beginnt dort, wo Erfahrungen, Wissen und Stimmen ihren Platz bekommen.',
        ],
        [
            'slug' => 'ag-05-oeffentlichkeitsarbeit', 'kuerzel' => 'AG Nr. 5',
            'name' => 'Öffentlichkeitsarbeit',
            'teaser' => 'Damit sichtbar wird, wofür wir stehen und was wir bewegen.',
            'absaetze' => [
                'In dieser Arbeitsgruppe beschäftigen wir uns damit, wie KE!N EINZELFALL nach außen auftritt und Menschen erreicht. Wir entwickeln Ideen für Informationsmaterialien, Messeauftritte, Aktionen und andere Formen der öffentlichen Präsenz.',
                'Dazu gehören zum Beispiel Flyer, Roll-ups, Visitenkarten, Infostände und die Vorbereitung auf Ehrenamtsmessen oder andere Veranstaltungen. Gemeinsam überlegen wir, wie wir unsere Themen verständlich, zugänglich und wiedererkennbar vermitteln können.',
                'Du kannst jederzeit dazukommen. Du kannst dich mit Gestaltung, Text, Organisation, Planung, Recherche oder eigenen Ideen einbringen.',
            ],
            'schlusssatz' => 'Öffentlichkeitsarbeit heißt für uns: sichtbar machen, was wichtig ist – und Menschen miteinander ins Gespräch bringen.',
        ],
        [
            'slug' => 'ag-06-datenbanken', 'kuerzel' => 'AG Nr. 6',
            'name' => 'Datenbanken',
            'teaser' => 'Wissen hilft nur dann weiter, wenn man es auch finden kann.',
            'absaetze' => [
                'In dieser Arbeitsgruppe sammeln, sortieren und strukturieren wir Informationen, die für Betroffene, Angehörige, Interessierte und Fachpersonen wichtig sein können. Unser Ziel ist es, Wissen nicht irgendwo verschwinden zu lassen, sondern so aufzubereiten, dass es später gezielt gefunden und genutzt werden kann.',
                'Dabei entstehen nach und nach Datenbanken zu unterschiedlichen Themen – zum Beispiel mit Volltexturteilen, Netzwerken, Fachliteratur oder weiteren hilfreichen Informationen. Gemeinsam überlegen wir, welche Inhalte wirklich nützlich sind, wie sie sinnvoll gegliedert werden können und wie daraus eine verlässliche Wissenssammlung entsteht.',
                'Du kannst jederzeit dazukommen. Du kannst dich mit Recherche, Sortierung, Strukturierung, Datenerfassung oder eigenen Ideen einbringen.',
            ],
            'schlusssatz' => 'Aus vielen einzelnen Informationen kann Orientierung entstehen.',
        ],
        [
            'slug' => 'ag-07-glaubhaftigkeitsgutachten', 'kuerzel' => 'AG Nr. 7',
            'name' => 'Glaubhaftigkeitsgutachten',
            'teaser' => 'Wenn Erinnerungen bewertet werden, braucht es Wissen, Sorgfalt und einen genauen Blick.',
            'absaetze' => [
                'In dieser Arbeitsgruppe beschäftigen wir uns mit Glaubhaftigkeitsgutachten in Verfahren nach OEG und SGB XIV. Wir schauen darauf, nach welchen Kriterien Aussagen bewertet werden, welche Rolle Themen wie Erinnerung, Suggestion, Dissoziation oder Traumafolgen spielen und wo es aus Betroffenensicht immer wieder zu Problemen kommt.',
                'Dabei wollen wir Gutachten, wissenschaftliche Grundlagen, gerichtliche Entscheidungen und Erfahrungen aus Verfahren zusammentragen und verständlich einordnen. Uns interessiert besonders, wie fachlich gearbeitet wird, wo Grenzen solcher Begutachtungen liegen und welche Aspekte bei komplexen Traumafolgen möglicherweise zu wenig berücksichtigt werden.',
                'Ziel ist es, Wissen zu bündeln, Unterschiede sichtbar zu machen und Betroffenen eine bessere Orientierung in einem Bereich zu geben, der häufig schwer verständlich und sehr belastend ist.',
                'Du kannst jederzeit dazukommen. Mitmachen kannst du mit eigener Erfahrung, Fachwissen, Recherche oder bei der Auswertung und Strukturierung von Materialien.',
            ],
            'schlusssatz' => 'Wo über Glaubhaftigkeit entschieden wird, darf Genauigkeit kein Nebenthema sein.',
        ],
        [
            'slug' => 'ag-08-app', 'kuerzel' => 'AG Nr. 8',
            'name' => 'Entwicklung einer App',
            'teaser' => 'Wenn Unterstützung gebraucht wird, sollte sie möglichst schnell erreichbar sein.',
            'absaetze' => [
                'In dieser Arbeitsgruppe entwickeln wir gemeinsam die Idee für eine App, die Menschen im Alltag Orientierung und Unterstützung geben soll. Dabei überlegen wir, welche Funktionen wirklich hilfreich sind, welche Informationen schnell erreichbar sein müssen und wie die Anwendung möglichst einfach, verständlich und niedrigschwellig aufgebaut werden kann.',
                'Im Mittelpunkt stehen die Erfahrungen der Menschen, die die App später nutzen sollen. Deshalb sammeln wir Ideen, prüfen Bedarfe, entwickeln Inhalte und denken gemeinsam darüber nach, wie aus vielen einzelnen Anforderungen eine Anwendung entstehen kann, die im richtigen Moment hilfreich ist.',
                'Du kannst jederzeit dazukommen. Mitmachen kannst du mit eigener Erfahrung, Ideen, Recherche, technischem Wissen, Gestaltung oder beim Testen neuer Funktionen.',
            ],
            'schlusssatz' => 'Eine gute Idee wird dann wertvoll, wenn sie Menschen im richtigen Moment Orientierung geben kann.',
        ],
    ];

    /**
     * Die Seite /arbeitsgruppen oberhalb der Dokumente und Karten (KEV-74).
     * Der erste Satz steht zugleich als Unterzeile auf dem Titelbild; die
     * Seite blendet ihn dann im Text aus. „Betroffene“ statt „Betroffen“.
     */
    public const ARBEITSGRUPPEN_SEITE = [
        'untertitel' => 'Du möchtest nicht nur zuschauen, sondern etwas mitgestalten?',
        'meta_description' => 'Du möchtest nicht nur zuschauen, sondern etwas mitgestalten? In unseren Arbeitsgruppen bringen Menschen ihre Erfahrungen, ihr Wissen, ihre Ideen und ganz unterschiedliche Fähigkeiten zusammen.',
        'titel' => 'Die Arbeitsgruppen des KE!N EINZELFALL e.V.',
        'absaetze' => [
            'Du möchtest nicht nur zuschauen, sondern etwas mitgestalten?',
            'In unseren Arbeitsgruppen bringen Menschen ihre Erfahrungen, ihr Wissen, ihre Ideen und ganz unterschiedliche Fähigkeiten zusammen. Gemeinsam arbeiten wir an konkreten Themen, entwickeln Projekte weiter, sammeln Informationen, machen Missstände sichtbar und suchen nach Wegen, wie sich etwas verbessern lässt.',
            'Dabei musst du kein Profi sein. Du kannst dich mit Fachwissen, Kreativität, Organisation, Recherche oder einfach mit deinem Interesse einbringen. Wie viel du beitragen möchtest, entscheidest du selbst.',
            'Unsere Arbeitsgruppen arbeiten online, projektbezogen und auf Augenhöhe. Je nach Thema entstehen daraus zum Beispiel Auswertungen, Informationsmaterialien, Veranstaltungen, Social-Media-Inhalte, Datenbanken oder neue Projekte. Ein Einstieg ist jederzeit möglich.',
            'An den Arbeitsgruppen können Betroffene, Angehörige, Interessierte und Fachpersonen teilnehmen. Die Teilnahme ist kostenfrei und nicht an eine Vereinsmitgliedschaft gebunden.',
            'Du möchtest mitmachen oder hast selbst eine Idee für eine Arbeitsgruppe? Schreib uns an arbeitsgruppe@kein-einzelfall.de.',
        ],
        'hand' => 'Aus unterschiedlichen Perspektiven können gemeinsame Lösungen entstehen.',
    ];

    /** Absätze als HTML, wie der Editor im Panel sie speichert; Adressen als Link. */
    public static function alsHtml(array $absaetze): string
    {
        return collect($absaetze)
            ->map(fn ($a) => '<p>'.preg_replace(
                '/[\w.+-]+@[\w-]+(?:\.[\w-]+)+/u', '<a href="mailto:$0">$0</a>', e($a)
            ).'</p>')
            ->implode("\n");
    }

    /**
     * Die Arbeitsgruppen-Seite nach KEV-74: Einleitung neu, der Block „So
     * bist Du dabei:“ fällt weg. Er war Fliesstext der Altseite, in dem
     * Downloads, Überschriften und die alte AG 01 zusammengelaufen waren.
     * Dokumente und Karten bleiben.
     *
     * Öffentlich für die Migration. Gibt die geänderten Seiten zurück.
     *
     * @param  array<string, array<string, mixed>>  $fassungen  je Sprache: alte Einleitung → neue Seite
     */
    public static function arbeitsgruppenseiteAufbauen(array $fassungen): int
    {
        $geaendert = 0;

        foreach (Page::where('slug', 'arbeitsgruppen')->get() as $seite) {
            $f = $fassungen[$seite->locale] ?? null;
            $einleitung = $seite->blocks()->where('typ', 'text')->orderBy('position')->first();

            // Nur, wo noch die Einleitung der Altseite steht.
            if (! $f || ! $einleitung || ($einleitung->data['absaetze'][0] ?? null) !== $f['alt_erster_absatz']) {
                continue;
            }

            $einleitung->update(['data' => array_replace($einleitung->data, [
                'titel' => $f['seite']['titel'],
                'absaetze' => $f['seite']['absaetze'],
                'hand' => $f['seite']['hand'],
            ])]);

            $seite->blocks()->where('typ', 'text')->get()
                ->filter(fn ($b) => ($b->data['titel'] ?? null) === $f['alt_so_dabei'])
                ->each->delete();

            $seite->update([
                'untertitel' => $f['seite']['untertitel'],
                'meta_description' => $f['seite']['meta_description'],
            ]);

            // Lücken in den Positionen schliessen
            foreach ($seite->blocks()->orderBy('position')->get()->values() as $i => $block) {
                $block->update(['position' => $i]);
            }

            $geaendert++;
        }

        return $geaendert;
    }

    /**
     * Der Gruppenbestand als Daten.
     *
     * Öffentlich und statisch, damit eine Migration einzelne fehlende Gruppen
     * nachtragen kann, ohne den ganzen Seeder laufen zu lassen: Der schreibt
     * mit `updateOrCreate` und überschriebe damit jede Änderung, die der
     * Verein im Panel an einer bestehenden Gruppe gemacht hat.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function gruppenliste(): array
    {
        return [
            [
                'slug' => 'buerokratie-labyrinth', 'typ' => 'selbsthilfe',
                'name' => 'Das Bürokratie-Labyrinth',
                'teaser' => 'Erfahrungsaustausch zu Anträgen, Fristen & Verfahren',
                'rhythmus' => 'Jeden 4. Mittwoch im Monat', 'uhrzeit' => '19:00 Uhr',
                'ort' => 'online via Teams', 'online' => true, 'status' => 'offen',
                // Strukturiert, damit die Termine im Kalender erscheinen
                'wiederholung' => 'monatlich_nter_wochentag', 'wochentag' => 3,
                'woche_im_monat' => 4, 'beginn_zeit' => '19:00', 'dauer_minuten' => 120,
            ],
            [
                'slug' => 'seelenfarben', 'typ' => 'selbsthilfe',
                'name' => 'Seelenfarben',
                'teaser' => 'Wenn Farben mehr als 1.000 Worte sagen',
                'rhythmus' => 'Jeden 1. Freitag im Monat', 'uhrzeit' => '10:00 Uhr',
                'ort' => 'online via Teams', 'online' => true, 'status' => 'offen',
                'wiederholung' => 'monatlich_nter_wochentag', 'wochentag' => 5,
                'woche_im_monat' => 1, 'beginn_zeit' => '10:00', 'dauer_minuten' => 120,
            ],
            [
                'slug' => 'wir-sind-nicht-mehr-stumm', 'typ' => 'selbsthilfe',
                'name' => 'Wir sind nicht mehr stumm!',
                'teaser' => 'Folgestörungen nach schädigenden Ereignissen',
                'online' => true, 'status' => 'offen',
            ],
            [
                'slug' => 'schreibwerkstatt', 'typ' => 'selbsthilfe',
                'name' => 'Schreibwerkstatt',
                'status' => 'geplant',
                'anmeldung_hinweis' => 'In Planung – aktuell noch keine Anmeldung möglich',
            ],
            /*
             * Aus dem Strukturpapier des Vereins, auf der Altseite noch nicht
             * vorhanden. Status „geplant“, weil uns kein Termin genannt wurde —
             * eine Gruppe als offen auszuweisen, zu der niemand kommen kann,
             * wäre bei dieser Zielgruppe die schlechtere Auskunft.
             *
             * ⚠️ Schreibweise: Im Strukturpapier steht „Killen me Softly“. Wir
             * gehen von „Killing me Softly“ aus — steht als Rückfrage auf der
             * Übergabe-Checkliste.
             */
            [
                'slug' => 'killing-me-softly', 'typ' => 'selbsthilfe',
                'name' => 'Killing me Softly',
                'teaser' => 'Umgang mit Trigger und Skills',
                'status' => 'geplant',
                'anmeldung_hinweis' => 'In Planung – aktuell noch keine Anmeldung möglich',
            ],

            // Die Arbeitsgruppen, Texte von Taddi (KEV-74).
            ...array_map(fn ($ag) => [
                'slug' => $ag['slug'], 'typ' => 'arbeits', 'kuerzel' => $ag['kuerzel'],
                'name' => $ag['name'],
                'teaser' => $ag['teaser'],
                'beschreibung' => self::alsHtml([...$ag['absaetze'], self::AG_KONTAKT]),
                'schlusssatz' => $ag['schlusssatz'],
                // „Unsere Arbeitsgruppen arbeiten online“, jederzeit offen
                'online' => true, 'status' => 'offen', 'anmeldung_hinweis' => null,
            ], self::ARBEITSGRUPPEN),
        ];
    }

    /**
     * Die betroffenen Seiten neu zusammensetzen.
     *
     * Der Fliesstext, aus dem die Datensätze stammen, wird durch den passenden
     * Baustein ersetzt — sonst stünde alles doppelt auf der Seite.
     */
    private function seitenNeuAufbauen(): void
    {
        // Erst die Personenabschnitte durch einen Baustein ersetzen, dann die
        // Seite nach der Gliederung der Altseite zusammensetzen.
        $this->seiteUmbauen('ueber-uns-vorstand-und-team', 'team_grid', []);
        $this->teamseiteAufbauen();

        $this->seiteUmbauen('selbsthilfegruppen', 'group_list', [
            'titel' => 'Unsere Selbsthilfegruppen',
            'typ' => 'selbsthilfe',
        ]);

        $this->seiteUmbauen('arbeitsgruppen', 'group_list', [
            'titel' => 'Aktuelle Arbeitsgruppen',
            'typ' => 'arbeits',
        ]);
        self::arbeitsgruppenseiteAufbauen(['de' => self::ARBEITSGRUPPEN_ALT + ['seite' => self::ARBEITSGRUPPEN_SEITE]]);
    }

    private function seiteUmbauen(string $slug, string $typ, array $daten): void
    {
        $seite = Page::where('slug', $slug)->first();

        if (! $seite || $seite->blocks()->where('typ', $typ)->exists()) {
            return;
        }

        $bloecke = $seite->blocks()->orderBy('position')->get();

        /*
         * Welche Abschnitte ersetzt der neue Baustein?
         *
         * Abgeglichen wird über die Namen der angelegten Datensätze: Ein
         * Textabschnitt, dessen Überschrift eine Gruppe oder eine Person
         * benennt, steht ab jetzt doppelt und fliegt raus. Einleitung,
         * Teilnahmeregeln und Dokumente bleiben — sie gehören nicht in die
         * Aufzählung, sondern drumherum.
         */
        $bezeichnungen = collect()
            ->merge(Group::pluck('name'))
            ->merge(TeamMember::pluck('name'))
            ->merge(TeamMember::pluck('rolle')->filter())
            ->merge(TeamMember::pluck('untertitel')->filter())
            ->map(fn ($n) => mb_strtolower(trim($n)))
            ->filter()
            ->values();

        $behalten = $bloecke->reject(function ($block) use ($bezeichnungen) {
            $titel = mb_strtolower(trim((string) ($block['data']['titel'] ?? '')));

            if ($titel === '') {
                return false;
            }

            return $bezeichnungen->contains(function ($name) use ($titel) {
                // Auf der Seite steht „AG 02 – Informationsfreiheitsgesetz …",
                // in der Datenbank nur der Name ohne Kürzel. Deshalb genügt es,
                // wenn der eine Text im anderen vorkommt.
                //
                // Die Mindestlänge verhindert Fehltreffer: Ein kurzer Name wie
                // „Team" käme sonst in halben Überschriften vor.
                if (mb_strlen($name) < 12) {
                    return str_starts_with($titel, $name);
                }

                return str_contains($titel, $name) || str_contains($name, $titel);
            });
        });

        $seite->blocks()->delete();

        $position = 0;
        foreach ($behalten as $block) {
            $seite->blocks()->create([
                'typ' => $block->typ,
                'position' => $position++,
                'data' => $block->data,
            ]);
        }

        $seite->blocks()->create([
            'typ' => $typ,
            'position' => $position,
            'data' => $daten,
        ]);
    }
}
