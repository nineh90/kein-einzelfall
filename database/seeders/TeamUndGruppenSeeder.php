<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Page;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

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
        $inhalt = json_decode(file_get_contents(base_path('docs/altseite-inhalt.json')), true);

        $this->team($inhalt['/ueber-uns-vorstand-und-team/'] ?? null);
        $this->gruppen();
        $this->seitenNeuAufbauen();
    }

    /**
     * Vorstand und Team.
     *
     * Erkennungsmuster: Ein Block ohne Absätze ist eine Rollenangabe
     * („1. Vorsitzende"), der folgende Block ohne Absätze der Name, der
     * nächste mit Absätzen die Vorstellung samt Kurzangaben als Titel.
     */
    private function team(?array $seite): void
    {
        if (! $seite) {
            return;
        }

        $bloecke = $seite['bloecke'];
        $personen = [];
        $rolle = null;
        $name = null;

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
                }

                continue;
            }

            // Überschrift mit Text: die Vorstellung der zuletzt genannten Person
            if ($name !== null) {
                $personen[] = [
                    'name' => $name,
                    'rolle' => $rolle,
                    'untertitel' => $titel,
                    'absaetze' => $absaetze,
                ];
                $rolle = null;
                $name = null;
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
        $sonderfaelle = [
            'Herr und Frau Unbekannt' => ['rolle' => null, 'bereich' => 'Team'],
        ];

        foreach ($personen as $i => $person) {
            $sonder = $sonderfaelle[$person['name']] ?? [];

            TeamMember::updateOrCreate(['name' => $person['name']], [
                'rolle' => array_key_exists('rolle', $sonder) ? $sonder['rolle'] : $person['rolle'],
                'untertitel' => $person['untertitel'],
                // Erster Absatz als Kurzfassung, der Rest als aufklappbarer Text
                'kurzprofil' => \Illuminate\Support\Str::limit($person['absaetze'][0], 260),
                'profil' => collect($person['absaetze'])
                    ->map(fn ($a) => '<p>'.e($a).'</p>')
                    ->implode("\n"),
                'bereich' => $sonder['bereich']
                    ?? (str_contains(mb_strtolower($person['rolle'] ?? ''), 'landesstelle')
                        ? 'Landesstellen'
                        : 'Vorstand'),
                'position' => $i,
                'published_at' => now(),
            ]);
        }

        $this->command?->info(count($personen).' Personen übernommen.');
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
        $gruppen = [
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
        ];

        foreach ($gruppen as $i => $gruppe) {
            Group::updateOrCreate(
                ['slug' => $gruppe['slug']],
                array_merge($gruppe, ['position' => $i, 'published_at' => now()])
            );
        }

        $this->command?->info(count($gruppen).' Gruppen übernommen.');
    }

    /**
     * Die betroffenen Seiten neu zusammensetzen.
     *
     * Der Fliesstext, aus dem die Datensätze stammen, wird durch den passenden
     * Baustein ersetzt — sonst stünde alles doppelt auf der Seite.
     */
    private function seitenNeuAufbauen(): void
    {
        // Ohne eigenen Titel: Der Einleitungsblock der Seite trägt ihn bereits.
        $this->seiteUmbauen('ueber-uns-vorstand-und-team', 'team_grid', []);

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
