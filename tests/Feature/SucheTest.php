<?php

namespace Tests\Feature;

use App\Support\Suche;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\AntraegeUndFormulareSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Suche (KEV-23).
 *
 * Der Kern dieser Datei ist die Trefferliste unten. Eine Suche lässt sich nicht
 * daran beurteilen, ob sie „läuft" — sie läuft immer. Sie ist gut oder
 * schlecht, und das zeigt sich nur an echten Anfragen mit einer erwarteten
 * Antwort. Ohne diese Liste wäre jede spätere Änderung an der Bewertung ein
 * Blindflug.
 */
class SucheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
        $this->seed(AntraegeUndFormulareSeeder::class);
    }

    /**
     * Anfragen, wie diese Zielgruppe sie stellt → wo sie landen sollen.
     *
     * Bewusst in Laiensprache, denn genau darum geht es: Wer zum ersten Mal
     * mit dem Sozialrecht zu tun hat, tippt „ausweis beantragen", nicht
     * „Feststellungsverfahren nach § 152 SGB IX".
     *
     * Neue Fälle gehören hier dazu, sobald jemand eine Anfrage sieht, die ins
     * Leere lief — das ist die einzige Stelle, an der die Suche wirklich
     * besser wird.
     *
     * @return array<string, array{string, string}>
     */
    public static function anfragen(): array
    {
        return [
            'Ausweis, in Laienworten' => ['ausweis beantragen', '/grad-der-behinderung'],
            'Abkürzung aus dem Bescheid' => ['gdb', '/grad-der-behinderung'],
            'Fachwort, ganz' => ['schwerbehindertenausweis', '/grad-der-behinderung'],
            'Merkzeichen' => ['merkzeichen', '/grad-der-behinderung'],
            'Pflegegrad' => ['pflegegrad', '/pflegegrad'],
            'Pflege, in Laienworten' => ['pflege beantragen', '/pflegegrad'],
            'altes Gesetz, noch geläufig' => ['oeg', '/opferentschaedigungsgesetz'],
            'Entschädigung, umschrieben' => ['entschädigung nach gewalttat', '/soziales-entschaedigungsrecht'],
            'Traumaambulanz' => ['traumaambulanz', '/soziales-entschaedigungsrecht'],
            'Persönliches Budget' => ['persönliches budget', '/persoenliches-budget'],
            'Gruppe finden' => ['selbsthilfegruppe finden', '/selbsthilfegruppen'],
            'Gruppe, umschrieben' => ['mit anderen reden', '/selbsthilfegruppen'],
            'Mitgliedschaft' => ['mitglied werden', '/mitgliedschaft'],
            'Spenden' => ['spenden', '/spenden'],
            'Rente, umschrieben' => ['rente weil ich nicht arbeiten kann', '/erwerbsminderungsrente'],
            'FSM ausgeschrieben' => ['fonds sexueller missbrauch', '/fsm-erweitertes-hilfesystem'],
            'Kontakt, in Laienworten' => ['kontakt aufnehmen', '/anfragen'],
            'Istanbul-Konvention' => ['istanbul konvention', '/istanbul-konvention'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('anfragen')]
    public function test_anfragen_landen_auf_der_richtigen_seite(string $anfrage, string $ziel): void
    {
        $ergebnis = app(Suche::class)->suchen($anfrage);
        $urls = array_column($ergebnis['treffer'], 'url');

        $this->assertContains(
            $ziel,
            array_slice($urls, 0, 3),
            "„{$anfrage}\" sollte {$ziel} unter den ersten drei Treffern haben. ".
            'Gefunden: '.(implode(', ', array_slice($urls, 0, 3)) ?: 'nichts')
        );
    }

    public function test_die_seite_ist_ohne_javascript_bedienbar(): void
    {
        /*
         * Ein GET-Formular, mehr nicht. Wer mit Screenreader, altem Browser
         * oder abgeschaltetem JavaScript kommt, muss suchen können — bei dieser
         * Zielgruppe ist das kein Randfall.
         */
        $html = $this->get('/suche')->assertOk()->getContent();

        $this->assertStringContainsString('role="search"', $html);
        $this->assertStringContainsString('method="get"', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('<label for="suchfeld"', $html);
    }

    public function test_treffer_stehen_in_einer_nummerierten_liste(): void
    {
        // <ol> und nicht <div>: Die Reihenfolge trägt Bedeutung, und
        // Vorlesehilfen sagen „3 von 8" nur bei einer echten Liste.
        $html = $this->get('/suche?q=pflegegrad')->assertOk()->getContent();

        // Auf `data-treffer` und nicht auf `<ol` allgemein: Die Brotkrumen
        // sind ebenfalls eine geordnete Liste, ein Test darauf ginge auch bei
        // leerer Trefferliste durch.
        $this->assertStringContainsString('<ol data-treffer', $html);
        $this->assertGreaterThanOrEqual(1, substr_count($html, '<li'));
    }

    public function test_suchergebnisse_bleiben_aus_dem_suchmaschinen_index(): void
    {
        // Für jede Anfrage eine andere Seite ohne eigenen Inhalt — das gehört
        // nicht in einen Index, und die Anfrage selbst erst recht nicht.
        $this->get('/suche?q=pflegegrad')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_ohne_anfrage_kommt_kein_leeres_ergebnis_sondern_eine_einladung(): void
    {
        $this->get('/suche')
            ->assertOk()
            ->assertSee('Gib oben ein, was du suchst', false)
            ->assertDontSee('Dazu haben wir nichts gefunden');
    }

    public function test_ohne_treffer_fuehrt_die_seite_weiter_statt_in_die_sackgasse(): void
    {
        /*
         * Wer hier landet, hat es schon mit eigenen Worten versucht. Ihn mit
         * „nichts gefunden" stehen zu lassen wäre das Gegenteil von dem, was
         * diese Website sein will.
         */
        $antwort = $this->get('/suche?q=xylofonbaukasten')->assertOk();

        $antwort->assertSee('Dazu haben wir nichts gefunden');
        $antwort->assertSee('/anfragen', false);
        $antwort->assertSee('/glossar', false);
    }

    public function test_krisenwendungen_bringen_hilfe_vor_die_trefferliste(): void
    {
        /*
         * Auch in ein Suchfeld schreiben Menschen, was sie sonst niemandem
         * sagen. Wer das tut, braucht keine Antragsseite.
         */
        $antwort = $this->get('/suche?q='.urlencode('ich kann nicht mehr'))->assertOk();

        $antwort->assertSee('Wenn es dir gerade sehr schlecht geht');
        $antwort->assertSee('Zu den Hilfe-Nummern');
    }

    public function test_eine_gewoehnliche_anfrage_loest_keinen_krisenhinweis_aus(): void
    {
        // Ein Kasten, der ständig erscheint, wird bald übersehen.
        $this->get('/suche?q=pflegegrad')
            ->assertOk()
            ->assertDontSee('Wenn es dir gerade sehr schlecht geht');
    }

    public function test_umlaute_sind_egal(): void
    {
        // Auf dem Handy und mit motorischen Einschränkungen fallen sie ständig
        // weg. Beide Schreibweisen müssen dasselbe finden.
        $mit = array_column(app(Suche::class)->suchen('persönliches budget')['treffer'], 'url');
        $ohne = array_column(app(Suche::class)->suchen('personliches budget')['treffer'], 'url');

        $this->assertSame(array_slice($mit, 0, 3), array_slice($ohne, 0, 3));
    }

    public function test_entwuerfe_bleiben_aus_der_suche(): void
    {
        /*
         * `noindex` schliesst nicht aus — das heisst „nicht bei Google", nicht
         * „unauffindbar". Wirklich verborgen ist nur, was nicht veröffentlicht
         * ist: Schutzkonzept und Beschwerdemanagement haben noch keinen Text.
         */
        $urls = array_column(app(Suche::class)->suchen('schutzkonzept')['treffer'], 'url');

        $this->assertNotContains('/schutzkonzept', $urls);
    }

    public function test_die_anfrage_wird_nirgends_gespeichert(): void
    {
        /*
         * Was jemand hier eintippt, verrät mehr über ihn als jede andere Zeile
         * dieser Website. Es gibt keine Tabelle dafür, und es soll keine geben,
         * solange niemand sie ausdrücklich beauftragt und in die
         * Datenschutzerklärung schreibt.
         */
        $this->get('/suche?q='.urlencode('entschädigung nach gewalttat'))->assertOk();

        $tabellen = \Illuminate\Support\Facades\Schema::getTableListing();

        foreach ($tabellen as $tabelle) {
            $this->assertStringNotContainsString(
                'such',
                strtolower($tabelle),
                "Die Tabelle {$tabelle} sieht nach einem Protokoll der Suchanfragen aus."
            );
        }
    }
}
