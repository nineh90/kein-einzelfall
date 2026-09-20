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

        $this->command?->info(count($personen).' Personen übernommen.');
    }

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
            $neu[] = ['typ' => 'team_grid', 'data' => ['bereich' => $bereich]];

            foreach ($mitglieder as $person) {
                if (isset($zwischentexte[$person['name']])) {
                    $neu[] = ['typ' => 'text', 'data' => ['absaetze' => $zwischentexte[$person['name']]]];
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

            [
                'slug' => 'ag-01-ser', 'typ' => 'arbeits', 'kuerzel' => 'AG 01',
                'name' => 'SER (OEG/SGB XIV) vs. Missstände & Best Practices',
                'status' => 'offen',
            ],
            [
                'slug' => 'ag-02-ifg', 'typ' => 'arbeits', 'kuerzel' => 'AG 02',
                'name' => 'Informationsfreiheitsgesetz (IFG) vs. offene Fragen',
                'teaser' => 'Entwicklung eines Fragenkatalogs für die Landesämter zur Verwaltungspraxis',
                'status' => 'offen',
            ],
            [
                'slug' => 'ag-03-online-veranstaltungen', 'typ' => 'arbeits', 'kuerzel' => 'AG 03',
                'name' => 'Online-Veranstaltungen',
                'teaser' => 'Planung & Organisation digitaler Veranstaltungen',
                'status' => 'offen',
            ],
            [
                'slug' => 'ag-04-soziale-medien', 'typ' => 'arbeits', 'kuerzel' => 'AG 04',
                'name' => 'Soziale Medien',
                'teaser' => 'Kreative Inhalte für unsere Kanäle',
                'status' => 'offen',
            ],
            [
                'slug' => 'ag-05-oeffentlichkeitsarbeit', 'typ' => 'arbeits', 'kuerzel' => 'AG 05',
                'name' => 'Öffentlichkeitsarbeit',
                'teaser' => 'Aktuell: Gestaltung & Entwurf eines Flyers (inkl. Signs to Help).',
                'status' => 'offen',
            ],
            [
                'slug' => 'ag-06-datenbanken', 'typ' => 'arbeits', 'kuerzel' => 'AG 06',
                'name' => 'Aufbau von Datenbanken',
                'teaser' => 'Aktuell: Strukturierte Sammlung von Rechtsprechung und Wissen',
                'status' => 'offen',
            ],
            /*
             * AG 07 aus dem Strukturpapier. Laut Besprechung vom 02.08.2026
             * steht das Projekt hinten an: Erst muss ein Konzept stehen und
             * eine Förderung beantragt sein.
             */
            [
                'slug' => 'ag-07-traumabegleiter', 'typ' => 'arbeits', 'kuerzel' => 'AG 07',
                'name' => 'Erstellung einer App „Traumabegleiter“',
                'teaser' => 'Konzept und Förderantrag für eine App zur Begleitung im Alltag',
                'status' => 'geplant',
                'anmeldung_hinweis' => 'In Planung – aktuell noch keine Anmeldung möglich',
            ],
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
