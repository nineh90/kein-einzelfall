<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use App\Models\Redirect;
use App\Support\Dokument;
use App\Support\Spenden;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Pflegt den Bestand der Altseite ein — aus docs/altseite-inhalt.json,
 * erzeugt von `php artisan altseite:holen`.
 *
 * Kein Text wird hier umformuliert. Vertraglich gilt: Inhalte stellt der Verein,
 * wir pflegen sie nur ein.
 */
class AltseiteSeeder extends Seeder
{
    /**
     * Slug-Zuordnung alt → neu.
     *
     * Grundsatz: URLs bleiben gleich, sonst verliert die Seite ihre Positionen
     * bei Google. Einzige Ausnahme ist das Impressum — "impressum-2" ist ein
     * WordPress-Unfall (der Slug "impressum" war belegt), den wir nicht mit
     * in die neue Seite schleppen.
     */
    private const SLUG_ANPASSUNG = [
        'impressum-2' => 'impressum',
    ];

    /**
     * Seiten, die dieser Seeder überspringt.
     *
     * Die Startseite ist inzwischen ebenfalls ein Datensatz, aber ihre
     * Bausteine sind Aufmacher, Einstiegskarten und Hinweisband — nicht der
     * Fliesstext, den `altseite-inhalt.json` für `/` hergibt. Sie kommt
     * deshalb aus dem `StartseiteSeeder`.
     */
    private const EIGENE_ROUTE = ['/'];

    /**
     * Titel für Seiten, auf denen die Altseite keine Überschrift ausweist.
     *
     * Aus dem Slug abgeleitet wäre der Titel „Ueber Uns Vorstand Und Team" —
     * Slugs kennen keine Umlaute. Auf einer deutschen Seite sieht das schlicht
     * falsch aus, deshalb hier ausgeschrieben. Die Schreibweise folgt der
     * Navigation, damit Menüpunkt und Überschrift zusammenpassen.
     */
    private const TITEL = [
        'ueber-uns-vorstand-und-team' => 'Über uns – Vorstand und Team',
        'buerokratie-labyrinth' => 'Das Bürokratie-Labyrinth',
        'traumafolgestoerungen-verstehen' => 'Traumafolgestörungen verstehen',
        'unterstuetzung' => 'Unterstützung',
        'fsm-erweitertes-hilfesystem' => 'FSM – Erweitertes Hilfesystem',
        'kein-einzelfall-im-dialog' => 'KE!N EINZELFALL im Dialog',
        'trauma-bindung-und-beziehung' => 'Trauma, Bindung und Beziehung',
        'das-hilfesystem' => 'Das Hilfesystem',
        'istanbul-konvention' => 'Istanbul-Konvention',
    ];

    /**
     * Titel, die der Verein nach dem Umzug anders haben wollte als auf der
     * Altseite. Sie schlagen deren Überschrift. Bestehende Datenbanken
     * erreicht die Änderung über eine eigene Migration.
     */
    /**
     * Blöcke der Altseite, die wir nicht übernehmen, je Seite nach Überschrift.
     *
     * „Mehr über:“ auf /verein war auf der Altseite eine Linkliste. Der Abzug
     * nimmt nur den Text mit, übrig blieben fünf nicht klickbare Zeilen —
     * direkt über den Karten „Mehr zu …“, die dieselben Seiten verlinken
     * (KEV-70). Bestehende Datenbanken räumt eine eigene Migration auf.
     */
    public const WEGLASSEN = [
        'verein' => ['Mehr über:'],
    ];

    /**
     * Neue Texte des Vereins für Blöcke der Altseite, je Seite nach
     * Überschrift. Sie ersetzen die Absätze des Abzugs. Bestehende
     * Datenbanken erreicht die Änderung über eine eigene Migration.
     */
    public const NEUE_TEXTE = [
        'ueber-uns-vorstand-und-team' => [
            // KEV-64, Text von Taddi. Ohne Komma vor „und ein Raum“,
            // „persönlichem“ statt „persönlichen“.
            'Unser Team – mit Herz, Haltung und Vision' => [
                'Hinter unserem Verein stehen Menschen mit unterschiedlichen Erfahrungen und Kompetenzen, '
                .'die sich mit Überzeugung, fachlichem Know-how und großem persönlichem Engagement für die '
                .'Opferhilfe und die Belange von Betroffenen einsetzen. Unsere Vorstandsebene gestaltet die '
                .'Arbeit des Vereins, trifft verantwortungsvolle Entscheidungen und sorgt dafür, dass '
                .'Unterstützung ankommt, Wissen vermittelt wird und ein Raum für Austausch entsteht.',
            ],
        ],
        'istanbul-konvention' => [
            // KEV-101, Text von Taddi, Absätze von uns gesetzt. „Istanbul-
            // Konvention“ mit Bindestrich wie im Seitentitel, Taddi schrieb
            // es getrennt.
            '' => [
                'Gewalt gegen Frauen, Mädchen und alle von geschlechtsspezifischer Gewalt betroffenen Menschen ist '
                .'kein Einzelfall – sie ist ein strukturelles Problem. Die Istanbul-Konvention ist das stärkste '
                .'internationale Schutzinstrument, das Betroffene davor bewahren soll, übersehen, nicht gehört oder '
                .'allein gelassen zu werden.',
                'Als KE!N EINZELFALL e.V. erkennen wir die Istanbul-Konvention ausdrücklich an und verstehen ihre '
                .'Grundsätze als zentrale Orientierung für unsere Arbeit. Sie stärkt die Rechte der Betroffenen, '
                .'verpflichtet zu Schutz, Prävention, Unterstützung und Sensibilisierung – Werte, die tief in unserer '
                .'Vereinsarbeit verankert sind.',
                'Mit unserer öffentlichen Unterstützung der Istanbul-Konvention machen wir deutlich: Betroffene '
                .'verdienen Schutz, Respekt, Sicherheit und verlässliche Hilfsstrukturen. Wir setzen uns dafür ein, '
                .'Lücken im Hilfesystem sichtbar zu machen und überall dort zu füllen, wo staatliche Angebote noch '
                .'fehlen oder unzureichend sind.',
                'Hier findest du unsere Haltung zur Istanbul-Konvention und warum sie für unsere Arbeit so wichtig '
                .'ist, sowie die Istanbul-Konvention.',
            ],
        ],
        'kinderkodex' => [
            // KEV-99, Text von Taddi. Der Abschnitt hat keine Überschrift,
            // der Schlüssel ist deshalb leer. Absätze von uns gesetzt.
            '' => [
                'Kinder und Jugendliche brauchen Schutz, Verlässlichkeit und Menschen, die Verantwortung '
                .'übernehmen. Bei KE!N EINZELFALL e.V. möchten wir überall dort, wo wir wirken, sichere und '
                .'respektvolle Räume schaffen – online wie offline. Kinder und Jugendliche sollen sich '
                .'gesehen, ernst genommen und geschützt fühlen.',
                'Unser Kinderkodex beschreibt verbindlich, wie wir diesem Anspruch gerecht werden. Er zeigt, '
                .'wie wir Risiken vorbeugen, verantwortungsvoll handeln und sicherstellen, dass die Rechte, '
                .'die Würde und die Sicherheit von Kindern und Jugendlichen jederzeit im Mittelpunkt stehen.',
                'Hier kannst du unseren Kinderkodex vollständig einsehen – als verbindliche Grundlage für '
                .'einen achtsamen und verantwortungsvollen Umgang.',
            ],
        ],
    ];

    /**
     * Überschrift über der Dokumentenliste, wo „Dokumente zum Herunterladen“
     * nicht passt, weil dort nur ein Dokument liegt.
     */
    /**
     * Einträge, die vor den Dokumenten der Altseite in die Liste kommen.
     */
    public const DOKUMENTE_DAVOR = [
        // KEV-101, Wunsch von Taddi: die Konvention selbst vor „Unsere
        // Haltung“. Verlinkt statt kopiert, Link von ihr.
        'istanbul-konvention' => [[
            'titel' => 'Die Istanbul-Konvention',
            'url' => 'https://www.institut-fuer-menschenrechte.de/menschenrechtsschutz/europarat/'
                .'menschenrechtsabkommen-des-europarats/istanbul-konvention',
            'quelle' => 'Deutsches Institut für Menschenrechte',
        ]],
    ];

    public const DOKUMENTE_TITEL = [
        // KEV-100, Wunsch von Taddi
        'kinderkodex' => 'Kinderkodex herunterladen',
        // KEV-101: Die Konvention ist ein Link auf eine fremde Seite, kein
        // Download (Kevin, 07.10.2026).
        'istanbul-konvention' => 'Zum Nachlesen',
    ];

    /**
     * Beschreibung für Suchmaschinen, wo die der Altseite nicht mehr passt.
     */
    public const NEUE_BESCHREIBUNGEN = [
        // KEV-101: Die alte passte nicht zur Seite und war schief („Wir setzen
        // uns … ein und fordert …“).
        'istanbul-konvention' => 'Warum KE!N EINZELFALL e.V. die Istanbul-Konvention anerkennt: unsere Haltung '
            .'zum Schutz vor geschlechtsspezifischer Gewalt, dazu die Konvention selbst.',
        // KEV-100: Die alte war grammatisch schief („Die Mitglieder und seine
        // Arbeitsgruppen“) und passte nicht mehr zu Taddis Text aus KEV-99.
        'kinderkodex' => 'Der Kinderkodex von KE!N EINZELFALL e.V.: wie wir Kinder und Jugendliche '
            .'schützen, Risiken vorbeugen und verantwortungsvoll handeln. Zum Herunterladen.',
    ];

    public const VORGABE_TITEL = [
        // KEV-59, Wunsch von Taddi
        'verein' => 'Gemeinnütziger Verein',
    ];

    /**
     * Zieht die ausgeschriebenen Titel in einer bestehenden Datenbank nach.
     *
     * Die Liste oben kam erst nach dem ersten Import dazu. Der Seeder läuft
     * bei uns nur bei leerer Datenbank und auf dem Server gar nicht — die
     * Seiten trugen deshalb weiter den Notbehelf aus dem Slug, sichtbar als
     * Überschrift („Ueber Uns Vorstand Und Team"), im Brotkrumenpfad und im
     * Reitertitel des Browsers.
     *
     * Angefasst wird nur, was noch exakt der Notbehelf ist. Hat der Verein
     * einen Titel im Panel gepflegt, bleibt er stehen — ein „besser gemeinter"
     * Titel aus dem Code darf keine redaktionelle Entscheidung überschreiben.
     *
     * Nur die Standardsprache: Übersetzungen haben eigene Titel, und ein
     * deutscher Titel auf einer englischen Seite wäre schlimmer als ein
     * holpriger.
     *
     * @return list<string> die Slugs, die geändert wurden
     */
    public static function titelNachziehen(): array
    {
        $standard = Language::standardCode();
        $geaendert = [];

        foreach (self::TITEL as $slug => $titel) {
            $seite = Page::where('slug', $slug)->where('locale', $standard)->first();

            if (! $seite || $seite->titel !== Str::headline($slug)) {
                continue;
            }

            $seite->update(['titel' => $titel]);
            $geaendert[] = $slug;
        }

        return $geaendert;
    }

    public function run(): void
    {
        // Muss vor den Seiten laufen: jede Seite braucht eine Sprache, und
        // ohne Standardsprache stünde nicht fest, welche das ist.
        $this->call(SprachenSeeder::class);
        $standard = Language::standardCode();

        $datei = base_path('docs/altseite-inhalt.json');

        if (! file_exists($datei)) {
            $this->command->error('docs/altseite-inhalt.json fehlt — erst `php artisan altseite:holen` ausführen.');

            return;
        }

        $inhalt = json_decode(file_get_contents($datei), true);
        $dokumente = collect(json_decode(file_get_contents(base_path('docs/dokumente-manifest.json')), true));

        $angelegt = 0;

        foreach ($inhalt as $altPfad => $daten) {
            if (in_array($altPfad, self::EIGENE_ROUTE, true)) {
                continue;
            }

            $altSlug = trim($altPfad, '/');
            $slug = self::SLUG_ANPASSUNG[$altSlug] ?? $altSlug;

            // Die Sprache gehört in den Suchschlüssel: Slugs sind nur noch
            // innerhalb einer Sprache eindeutig, und der Altbestand ist
            // ausnahmslos die deutsche Fassung.
            $page = Page::updateOrCreate(['slug' => $slug, 'locale' => $standard], [
                // Reihenfolge: Vorgabe des Vereins, dann Überschrift der
                // Altseite, sonst unsere Liste, erst zuletzt der aus dem Slug
                // abgeleitete Notbehelf.
                'titel' => self::VORGABE_TITEL[$slug]
                    ?? ($daten['titel'] ?: (self::TITEL[$slug] ?? Str::headline($slug))),
                'meta_title' => $daten['meta_title'],
                'meta_description' => self::NEUE_BESCHREIBUNGEN[$slug] ?? $daten['meta_description'],
                'published_at' => now(),
            ]);

            $page->blocks()->delete();
            $position = 0;

            foreach ($daten['bloecke'] as $block) {
                // Blöcke ohne Fließtext sind Layout-Reste aus Elementor
                if (empty($block['absaetze'])) {
                    continue;
                }

                if (in_array($block['titel'], self::WEGLASSEN[$slug] ?? [], true)) {
                    continue;
                }

                $page->blocks()->create([
                    'typ' => 'text',
                    'position' => $position++,
                    'data' => [
                        'titel' => $block['titel'],
                        // Abschnitt ohne Überschrift: Schlüssel '' (KEV-99).
                        'absaetze' => self::NEUE_TEXTE[$slug][$block['titel'] ?? ''] ?? $block['absaetze'],
                    ],
                ]);
            }

            // Die Spendenseite: Konto und PayPal standen auf der Altseite als
            // Fliesstext, betterplace als iframes, die der Abzug gar nicht
            // erst mitnimmt. Daraus wird der Spenden-Baustein — dieselbe
            // Umstellung, die die Migration auf bestehenden Datenbanken macht.
            if ($slug === 'spenden') {
                Spenden::spendenseiteUmstellen($page);
                $position = (int) $page->blocks()->max('position') + 1;
            }

            // Verlinkte Dokumente als eigener Block ans Seitenende.
            // Titel und Größe kommen aus dem Manifest — der Linktext der Altseite
            // ist als Bezeichnung deutlich brauchbarer als der Dateiname.
            if ($daten['pdfs']) {
                $liste = collect($daten['pdfs'])->map(function ($pdf) use ($dokumente) {
                    $bekannt = $dokumente->firstWhere('alt_url', $pdf['url']);

                    return [
                        'titel' => $pdf['titel'] ?: ($bekannt['titel'] ?? basename($pdf['url'])),
                        // Auf unsere eigene Adresse zeigen, nicht mehr auf die
                        // Altseite. Solange die Datei noch nicht geholt ist,
                        // blendet der Baustein den Eintrag aus — ein toter
                        // Download-Knopf ist schlechter als keiner.
                        'url' => Dokument::pfad($pdf['url']),
                        // Die tatsaechliche Groesse schlaegt das Manifest: Wenn
                        // die Datei bei uns liegt, gilt was auf der Platte steht.
                        'bytes' => Dokument::groesse($pdf['url']) ?? $bekannt['bytes'] ?? null,
                    ];
                })->unique('url')->values()->all();

                $liste = [...self::DOKUMENTE_DAVOR[$slug] ?? [], ...$liste];

                $page->blocks()->create([
                    'typ' => 'download_list',
                    'position' => $position++,
                    'data' => ['titel' => self::DOKUMENTE_TITEL[$slug] ?? 'Dokumente zum Herunterladen', 'dokumente' => $liste],
                ]);
            }

            // WordPress liefert jede Seite mit Schrägstrich am Ende aus.
            // Ohne diese Weiterleitung wäre jede indexierte URL ein 404.
            Redirect::updateOrCreate(
                ['von' => $altSlug.'/'],
                ['nach' => '/'.$slug, 'status' => 301, 'notiz' => 'WordPress-Slash']
            );

            if ($slug !== $altSlug) {
                Redirect::updateOrCreate(
                    ['von' => $altSlug],
                    ['nach' => '/'.$slug, 'status' => 301, 'notiz' => 'Slug bereinigt']
                );
            }

            $angelegt++;
        }

        // Die beiden auf der Altseite kaputten Links (liefern dort 404).
        // Extern verlinkt oder gebookmarkt können sie trotzdem sein.
        foreach ([
            'selbsthilfegruppen-2' => '/selbsthilfegruppen',
            'kontaktformular' => '/anfragen',
        ] as $von => $nach) {
            Redirect::updateOrCreate(
                ['von' => $von],
                ['nach' => $nach, 'status' => 301, 'notiz' => 'War auf der Altseite ein 404']
            );
        }

        // Die Startseite. Eigener Seeder, weil sie aus Bausteinen besteht und
        // nicht aus dem Fliesstext der Altseite.
        $this->call(StartseiteSeeder::class);

        // Nicht Teil des Altbestands, aber im Footer und in der Toolbar
        // verlinkt — ohne sie liefe der Verweis ins Leere.
        $this->call(BarrierefreiheitSeeder::class);

        // Vorstand und Gruppen aus dem Fliesstext in eigene Datensaetze
        // ueberfuehren und die betroffenen Seiten neu zusammensetzen.
        $this->call(TeamUndGruppenSeeder::class);

        // Titelbilder im Seitenkopf. Die Migration dafür lief schon, als die
        // Datenbank noch leer war, und hat nichts gefunden.
        Titelbilder::setzen();
        Titelbilder::platzhalterSetzen();

        $this->command->info("{$angelegt} Seiten und ".Redirect::count().' Weiterleitungen eingepflegt.');
    }
}
