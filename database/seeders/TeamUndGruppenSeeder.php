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

    /**
     * Die Selbsthilfegruppen, wie Taddi sie am 29.09.2026 geschickt hat (KEV-73).
     *
     * Offen sind nur die ersten beiden. Die übrigen sind „grad inaktiv und
     * müssen erst später wieder freigeschaltet werden“: Status „geplant“,
     * damit sie ohne Termine im Kalender und ohne Anmeldung dastehen. Zum
     * Freischalten im Panel auf „offen“ stellen.
     *
     * Aus „Killing me Softly“ (Strukturpapier: „Killen me Softly“) wird
     * „Skillin me Softly“, so steht es in Taddis Überschrift und im Text.
     * Korrigiert: Kommas bei „egal ob physisch, … oder digital,“, „welche
     * Wege möglich sind, und“, „dabei zu sein, um zu malen“.
     */
    public const SELBSTHILFEGRUPPEN = [
        [
            'slug' => 'wir-sind-nicht-mehr-stumm', 'status' => 'offen',
            'name' => 'Wir sind nicht mehr stumm',
            'teaser' => 'Folgestörungen durch Missbrauch und andere schädigende Ereignisse',
            'absaetze' => [
                'Gewalt, Missbrauch, egal ob physisch, psychisch, sexuell oder digital, und andere schädigende Erfahrungen enden nicht immer mit dem eigentlichen Ereignis. Viele Betroffene leben noch lange danach mit seelischen oder körperlichen Folgen – oft begleitet von Scham, Schuldgefühlen und dem Gefühl, mit diesen Belastungen nicht ausreichend gesehen oder verstanden zu werden.',
                'In unserer Selbsthilfegruppe „Wir sind nicht mehr stumm“ geht es darum, genau darüber sprechen zu dürfen. Wir möchten Erfahrungen teilen, uns gegenseitig stärken und sichtbar machen, dass Folgestörungen ernst genommen und als Teil der erlebten Gewalt verstanden werden müssen.',
                'Die Gruppe ist ausschließlich für Betroffene gedacht und dient dem Erfahrungsaustausch auf Augenhöhe. Niemand muss mehr erzählen, als sich gerade richtig anfühlt – auch Zuhören ist völlig in Ordnung.',
            ],
            'schlusssatz' => 'Wir wollen das Schweigen gemeinsam brechen – denn wir sind nicht mehr stumm.',
            'rhythmus' => 'Jeden 2. Mittwoch im Monat', 'uhrzeit' => '19:00 Uhr',
            'wochentag' => 3, 'woche_im_monat' => 2, 'beginn_zeit' => '19:00',
        ],
        [
            'slug' => 'buerokratie-labyrinth', 'status' => 'offen',
            'name' => 'Bürokratie-Labyrinth',
            'teaser' => 'Gefangen im Dschungel von Behörden, Anträgen und Verfahren',
            'absaetze' => [
                'Anträge, Fristen, lange Bearbeitungszeiten und schwer verständliche Schreiben können schnell überfordern – besonders dann, wenn ohnehin schon viel Kraft für andere Dinge gebraucht wird.',
                'In unserer Selbsthilfegruppe „Bürokratie-Labyrinth – Gefangen im Dschungel der Behörden“ tauschen wir Erfahrungen rund um Behörden, Ämter, Anträge und Verfahren aus. Wir sprechen darüber, was geholfen hat, welche Wege möglich sind, und unterstützen uns gegenseitig beim Sortieren von Unterlagen, Ausfüllen von Formularen sowie beim Verfassen von Briefen und E-Mails.',
                'Die Gruppe richtet sich an Betroffene und Angehörige. Sie ist keine Rechtsberatung und keine Informationsveranstaltung, sondern lebt vom gemeinsamen Erfahrungsaustausch.',
            ],
            'schlusssatz' => 'Gemeinsam finden wir Wege durch das Bürokratie-Labyrinth – Schritt für Schritt.',
            'rhythmus' => 'Jeden 4. Mittwoch im Monat', 'uhrzeit' => '19:00 Uhr',
            'wochentag' => 3, 'woche_im_monat' => 4, 'beginn_zeit' => '19:00',
        ],
        [
            'slug' => 'seelenfarben', 'status' => 'geplant',
            'name' => 'Seelenfarben',
            'teaser' => 'Wenn Bilder mehr als 1.000 Worte sagen',
            'absaetze' => [
                'Manchmal lässt sich etwas leichter malen als aussprechen. In „Seelenfarben“ geht es darum, Gefühle, Gedanken und innere Prozesse kreativ sichtbar werden zu lassen – ganz ohne Leistungsdruck und ohne Bewertung.',
                'Gemeinsam malen wir zu freien oder vorgegebenen Themen. Im Anschluss kann, wer möchte, das eigene Bild zeigen und erzählen, was dabei entstanden ist. Niemand muss erklären oder sprechen – auch einfach dabei zu sein, um zu malen, ist völlig in Ordnung.',
                'Die Gruppe richtet sich an Menschen mit psychischen Belastungen, Trauma-Erfahrungen oder chronischen Erkrankungen sowie an Angehörige und Interessierte. Im Mittelpunkt stehen kreativer Ausdruck, Entlastung, Austausch und Selbststärkung.',
            ],
            'schlusssatz' => 'Was sich schwer in Worte fassen lässt, darf hier durch Farben sichtbar werden.',
            'rhythmus' => 'Jeden 1. Freitag im Monat', 'uhrzeit' => '10:00 Uhr',
            'wochentag' => 5, 'woche_im_monat' => 1, 'beginn_zeit' => '10:00',
        ],
        [
            'slug' => 'skillin-me-softly', 'status' => 'geplant',
            'name' => 'Skillin me Softly',
            'teaser' => 'Trigger und Skills',
            'absaetze' => [
                'Manchmal reicht ein Geruch, ein Satz, eine Situation oder eine Erinnerung – und plötzlich ist die Anspannung da. Was in solchen Momenten hilft, ist von Mensch zu Mensch verschieden.',
                'In „Skillin me Softly – Trigger und Skills“ tauschen wir uns darüber aus, was uns triggert und was bei hoher Anspannung hilft. Welche Skills funktionieren? Was haben andere ausprobiert? Und was könnte vielleicht auch für dich hilfreich sein?',
                'Es geht nicht um die eine richtige Lösung, sondern um Erfahrungen teilen, voneinander lernen und neue Möglichkeiten kennenlernen. Du kannst erzählen, Fragen stellen, zuhören oder einfach nur dabei sein.',
                'Die Gruppe ist ausschließlich für Betroffene gedacht.',
            ],
            'schlusssatz' => 'Manchmal kommt die Anspannung schneller, als Worte es erklären können – hier darfst du damit einfach sein.',
            'rhythmus' => 'Jeden 3. Dienstag im Monat', 'uhrzeit' => '11:00 Uhr',
            'wochentag' => 2, 'woche_im_monat' => 3, 'beginn_zeit' => '11:00',
        ],
        [
            'slug' => 'schreibwerkstatt', 'status' => 'geplant',
            'name' => 'Zwischen den Zeilen',
            'teaser' => 'Die Schreibwerkstatt',
            'absaetze' => [
                'Gedanken, Gefühle und Erlebtes lassen sich nicht immer leicht aussprechen – auf dem Papier finden sie oft einen anderen Weg. „Zwischen den Zeilen“ soll Raum dafür schaffen, das, was gerade da ist, in Worte zu fassen.',
                'Dabei geht es nicht darum, „gut“ schreiben zu können. Es geht darum, Gedanken zu sortieren, Gefühle in Worte zu fassen und dem Raum zu geben, was sonst vielleicht unausgesprochen bleibt.',
                'Texte können geteilt werden – müssen aber nicht. Du entscheidest selbst, was du schreiben, zeigen oder lieber für dich behalten möchtest.',
                'Die Schreibwerkstatt soll für alle offen sein und befindet sich derzeit noch in Vorbereitung.',
            ],
            'schlusssatz' => 'Das Wichtigste steht nicht immer in den großen Worten – sondern oft zwischen den Zeilen.',
        ],
    ];

    /**
     * Die Seite /selbsthilfegruppen (KEV-73). Taddis Text in fünf Absätzen:
     * Ab dem sechsten klappt der Baustein ein, und Regeln und Kontakt
     * gehören nicht hinter „Weiterlesen“. Reihenfolge wie bei ihr. Das
     * Titelbild bleibt, samt Unterzeile.
     */
    public const SELBSTHILFE_SEITE = [
        'titel' => 'Die Selbsthilfegruppen des KE!N EINZELFALL e.V.',
        'absaetze' => [
            'Manchmal tut es gut, mit Menschen zusammenzukommen, bei denen man sich nicht erst erklären muss, weil ähnliche Erfahrungen verbinden. Unsere Selbsthilfegruppen bieten Raum für Austausch, gegenseitiges Verständnis und neue Perspektiven.',
            'Du entscheidest selbst, wie viel du erzählen möchtest – auch einfach nur zuzuhören ist vollkommen in Ordnung, egal ob mit oder ohne Bild. Du kannst dich über den Chat oder dein Mikro beteiligen. Die Gruppen werden gemeinsam von den Teilnehmenden gestaltet und finden online über Microsoft Teams statt. Die Teilnahme ist kostenfrei und nicht an eine Vereinsmitgliedschaft gebunden.',
            'Wir sprechen offen über sensible Themen. Für die Teilnahme setzen wir Therapieerfahrung und eine gute Selbstfürsorge voraus – insbesondere, falls du getriggert wirst. Alles geschieht in eigener Verantwortung.',
            'Für einen sicheren und respektvollen Rahmen gibt es Teilnahmebedingungen und Gruppenregeln. Du findest beides weiter unten auf dieser Seite. Bitte lies sie dir vor deiner ersten Teilnahme durch – mit dem Beitritt zur Gruppe akzeptierst du diese. Unsere Selbsthilfegruppen ersetzen keine Therapie, medizinische oder rechtliche Beratung. Bei sensiblen Themen ist es wichtig, gut auf die eigenen Grenzen zu achten und selbst zu entscheiden, was gerade möglich ist.',
            'Du hast Fragen oder Interesse? Oder eine Idee für eine neue Selbsthilfegruppe? Schreib uns eine kurze E-Mail an selbsthilfe@kein-einzelfall.de. Wir melden uns umgehend bei dir.',
        ],
        'hand' => 'Das Team von KE!N EINZELFALL e.V.',
        // So heisst das PDF „Tu.V-SHG“ bei Taddi
        'dokument_umbenennen' => ['Teilnahmebedingungen' => 'Teilnahme- und Verschwiegenheitsvereinbarung'],
    ];

    /** Woran die Selbsthilfe-Seite der Altseite zu erkennen ist. */
    public const SELBSTHILFE_ALT = [
        'alt_erster_absatz' => 'Raum für deine Geschichte – ohne Druck oder Bewertung',
    ];

    /**
     * Die Selbsthilfe-Seite nach KEV-73: Einleitung neu, die übrigen
     * Fliesstext-Blöcke der Altseite fallen weg, dann die Gruppen, die
     * Dokumente ans Ende („weiter unten auf dieser Seite“). Nur, wo noch
     * die Einleitung der Altseite steht. Nur Deutsch, eine englische
     * Fassung gibt es nicht.
     */
    public static function selbsthilfeseiteAufbauen(): bool
    {
        $seite = Page::where('slug', 'selbsthilfegruppen')->where('locale', 'de')->first();
        $einleitung = $seite?->blocks()->where('typ', 'text')->orderBy('position')->first();

        if (! $einleitung || ($einleitung->data['absaetze'][0] ?? null) !== self::SELBSTHILFE_ALT['alt_erster_absatz']) {
            return false;
        }

        $neu = self::SELBSTHILFE_SEITE;
        $einleitung->update(['data' => array_replace($einleitung->data, [
            'titel' => $neu['titel'],
            'absaetze' => $neu['absaetze'],
            'hand' => $neu['hand'],
        ])]);

        // Taddis Text ersetzt den ganzen Fliesstext der Altseite. Welche
        // Reste daneben stehen, hängt davon ab, wie die Datenbank entstanden
        // ist („Voraussetzungen & Rahmen“, „Kontakt & Anmeldung“, …), also
        // alle Textblöcke ausser der Einleitung.
        $seite->blocks()->where('typ', 'text')->whereKeyNot($einleitung->getKey())->delete();

        foreach ($seite->blocks()->where('typ', 'download_list')->get() as $block) {
            $data = $block->data;
            foreach ($data['dokumente'] ?? [] as $i => $dok) {
                $data['dokumente'][$i]['titel'] = $neu['dokument_umbenennen'][$dok['titel']] ?? $dok['titel'];
            }
            $block->update(['data' => $data]);
        }

        // Einleitung, Gruppen, Dokumente
        $rang = ['text' => 0, 'group_list' => 1, 'download_list' => 2];
        $seite->blocks()->orderBy('position')->get()
            ->sortBy(fn ($b) => [$rang[$b->typ] ?? 1, $b->position])
            ->values()
            ->each(fn ($b, $i) => $b->update(['position' => $i]));

        return true;
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
            // Die Selbsthilfegruppen, Texte von Taddi (KEV-73).
            ...array_map(fn ($g) => [
                'slug' => $g['slug'], 'typ' => 'selbsthilfe', 'kuerzel' => null,
                'name' => $g['name'],
                'teaser' => $g['teaser'],
                'beschreibung' => self::alsHtml($g['absaetze']),
                'schlusssatz' => $g['schlusssatz'],
                'rhythmus' => $g['rhythmus'] ?? null, 'uhrzeit' => $g['uhrzeit'] ?? null,
                'ort' => 'online über Microsoft Teams', 'online' => true,
                'status' => $g['status'], 'anmeldung_hinweis' => null,
                // Strukturiert, damit „Nächster Termin“ und Kalender stimmen
                'wiederholung' => isset($g['wochentag']) ? 'monatlich_nter_wochentag' : 'keine',
                'wochentag' => $g['wochentag'] ?? null,
                'woche_im_monat' => $g['woche_im_monat'] ?? null,
                'beginn_zeit' => $g['beginn_zeit'] ?? null,
                'dauer_minuten' => isset($g['wochentag']) ? 120 : null,
            ], self::SELBSTHILFEGRUPPEN),

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
        self::selbsthilfeseiteAufbauen();
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
