<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageBlock;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Unterseiten bestanden anfangs nur aus einer Überschrift und gleich
 * aussehenden Textblöcken — im Vergleich zur Startseite wirkten sie unfertig.
 * Diese Tests halten fest, was dagegen eingebaut wurde.
 */
class SeitengestaltungTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_unterseiten_haben_einen_seitenkopf_mit_brotkrumen(): void
    {
        $html = $this->get('/satzung')->getContent();

        $this->assertStringContainsString('aria-label="Sie sind hier"', $html);
        // Bereichsangabe über der Überschrift, abgeleitet aus der Navigation
        $this->assertStringContainsString('Verein', $html);
    }

    public function test_brotkrumen_markieren_die_aktuelle_seite_und_verlinken_sie_nicht(): void
    {
        $html = $this->get('/satzung')->getContent();

        preg_match('/aria-label="Sie sind hier".*?<\/nav>/s', $html, $treffer);
        $krumen = $treffer[0];

        $this->assertStringContainsString('aria-current="page"', $krumen);
        $this->assertStringContainsString('href="/"', $krumen);
    }

    public function test_titelbild_steht_im_seitenkopf_als_schmuck(): void
    {
        $html = $this->get('/verein')->getContent();
        preg_match('/<header data-anschliessend.*?<\/header>/s', $html, $kopf);

        $this->assertStringContainsString('src="/img/titelbilder/verein.webp"', $kopf[0]);
        // Stimmungsbild ohne Aussage: leeres alt, Vorlesehilfen überspringen es.
        $this->assertStringContainsString('alt=""', $kopf[0]);
        $this->assertFileExists(public_path('img/titelbilder/verein.webp'));
        // Kleine Fassung für schmale Bildschirme
        $this->assertStringContainsString('/img/titelbilder/verein-1000.webp 1000w', $kopf[0]);
    }

    public function test_titelbild_kopf_ist_immer_gleich_gebaut(): void
    {
        foreach (['/spenden', '/kontakt', '/wissen', '/selbsthilfegruppen'] as $pfad) {
            preg_match('/<header data-anschliessend.*?<\/header>/s', $this->get($pfad)->getContent(), $kopf);
            $kopf = $kopf[0];

            // Grüne Zeile, Titel, Unterzeile — in dieser Reihenfolge und auf jeder Seite
            $this->assertMatchesRegularExpression(
                '/<img .*?<p class="[^"]*uppercase[^"]*">\s*\S.*?<h1.*?<\/h1>\s*<p[^>]*>\s*\S/s',
                $kopf,
                "{$pfad}: grüne Zeile, Titel oder Unterzeile fehlt",
            );

            // Die Brotkrumen stehen unter dem Bild, nicht darauf
            $this->assertGreaterThan(strpos($kopf, '</h1>'), strpos($kopf, 'aria-label="Sie sind hier"'), $pfad);
        }
    }

    public function test_unterzeile_steht_nicht_gleich_darunter_noch_einmal(): void
    {
        $html = $this->get('/selbsthilfegruppen')->getContent();

        $text = preg_replace('/\s+/', ' ', $html);

        $this->assertSame(1, substr_count($text, '> Raum für deine Geschichte – ohne Druck oder Bewertung </p>')
            + substr_count($text, '>Raum für deine Geschichte – ohne Druck oder Bewertung</p>'));
    }

    public function test_terminuebersicht_nimmt_das_titelbild_der_gleichnamigen_seite(): void
    {
        $this->assertStringContainsString(
            '/img/titelbilder/veranstaltungen.webp',
            $this->get('/veranstaltungen')->getContent(),
        );
    }

    /**
     * Seit KEV-36 hat jede Seite ein Titelbild. Wo es noch kein eigenes gibt,
     * vorerst den Platzhalter; nur Startseite und Hinweisfenster bleiben ohne.
     */
    public function test_jede_seite_hat_ein_titelbild(): void
    {
        $this->assertStringContainsString('/img/titelbilder/platzhalter.webp', $this->get('/impressum')->getContent());

        $ohne = Page::whereNull('titelbild')->whereNotNull('published_at')->pluck('slug')->unique()->sort()->values()->all();
        $this->assertSame(['startseite', 'trigger-warnung'], $ohne);
    }

    /** KEV-61: Das Antragsbild reicht bis oben und wird dort ausgerichtet. */
    public function test_bilder_mit_motiv_oben_werden_oben_ausgerichtet(): void
    {
        $this->assertStringContainsString('object-position: 0% 0%', $this->get('/mitgliedschaft')->getContent());
        $this->assertStringContainsString('object-position: 0% 100%', $this->get('/spenden')->getContent());
    }

    /** KEV-83: Taddis Arbeitsgruppen-Bild hat das Motiv auf halber Höhe. */
    public function test_bilder_mit_motiv_in_der_mitte_werden_mittig_ausgerichtet(): void
    {
        $html = $this->get('/arbeitsgruppen')->getContent();

        $this->assertStringContainsString('src="/img/titelbilder/arbeitsgruppen.webp"', $html);
        $this->assertStringContainsString('object-position: 0% 50%', $html);
    }

    /** KEV-80: Bild von Taddi. Seit KEV-98 mit Text und veröffentlicht. */
    public function test_beschwerdemanagement_hat_titelbild(): void
    {
        $seite = Page::where('slug', 'beschwerdemanagement')->where('locale', 'de')->firstOrFail();

        $this->assertSame('/img/titelbilder/beschwerdemanagement.webp', $seite->titelbild);
        $this->get('/beschwerdemanagement')->assertOk()
            ->assertSee('src="/img/titelbilder/beschwerdemanagement.webp"', false)
            ->assertSee('object-position: 0% 35%', false);
    }

    /**
     * KEV-79: Ein Bild für beide Seiten, das Logo sitzt oben. Ausgerichtet auf
     * seine Höhe, auf dem Handy steht der Text darunter statt darauf.
     */
    public function test_istanbul_konvention_und_kinderkodex_mit_logo_bild(): void
    {
        foreach (['/istanbul-konvention', '/kinderkodex'] as $pfad) {
            $html = $this->get($pfad)->assertOk()->getContent();

            $this->assertStringContainsString('src="/img/titelbilder/ordner-mit-logo.webp"', $html, $pfad);
            $this->assertStringNotContainsString('platzhalter.webp', $html, $pfad);
            $this->assertStringContainsString('object-position: 0% 42%', $html, $pfad);
            $this->assertStringContainsString('min-h-[60svh] md:min-h-[50svh]', $html, $pfad);
            $this->assertStringContainsString('bg-linear-to-t', $html, $pfad);
        }

        // Andere Seiten behalten den Text oben
        $this->assertStringNotContainsString('min-h-[60svh]', $this->get('/spenden')->getContent());
    }

    /** KEV-99: Text von Taddi statt des Altseiten-Absatzes, in drei Absätzen. */
    public function test_kinderkodex_hat_den_neuen_text(): void
    {
        $html = $this->get('/kinderkodex')->assertOk()->getContent();

        $this->assertStringContainsString('Kinder und Jugendliche brauchen Schutz, Verlässlichkeit und Menschen', $html);
        $this->assertStringContainsString('<p>Unser Kinderkodex beschreibt verbindlich', $html);
        $this->assertStringContainsString('<p>Hier kannst du unseren Kinderkodex vollständig einsehen', $html);
        $this->assertStringNotContainsString('Erwachsene, die hinsehen', $html);
        $this->assertStringNotContainsString('ein Versprechen, das wir jeden Tag einlösen', $html);

        // Der Kodex zum Herunterladen bleibt direkt darunter, seit KEV-100
        // unter eigener Überschrift: Es ist nur ein Dokument.
        $this->assertStringContainsString('Kinderkodex-HP-29.03.26.pdf', $html);
        $this->assertStringContainsString('Kinderkodex herunterladen', $html);
        $this->assertStringNotContainsString('Dokumente zum Herunterladen', $html);

        $this->assertStringContainsString('content="Der Kinderkodex von KE!N EINZELFALL e.V.: wie wir Kinder', $html);
        $this->assertStringNotContainsString('und seine Arbeitsgruppen', $html);

        // Andere Seiten behalten die allgemeine Überschrift.
        $this->assertStringContainsString('Dokumente zum Herunterladen', $this->get('/selbsthilfegruppen')->getContent());
    }

    /** KEV-101: Text von Taddi, die Konvention als Link vor „Unsere Haltung“. */
    public function test_istanbul_konvention_nach_kev_101(): void
    {
        $html = $this->get('/istanbul-konvention')->assertOk()->getContent();

        $this->assertStringContainsString('<p>Als KE!N EINZELFALL e.V. erkennen wir die Istanbul-Konvention', $html);
        $this->assertStringContainsString('<p>Hier findest du unsere Haltung zur Istanbul-Konvention', $html);
        $this->assertStringNotContainsString('unseren vollständigen Text zur Istanbul Konvention', $html);
        $this->assertStringNotContainsString('gelassen zu werden.Als', $html);

        // Die Konvention steht vor der Haltung.
        $konvention = strpos($html, 'href="https://www.institut-fuer-menschenrechte.de/');
        $haltung = strpos($html, 'Unsere Haltung zur Istanbul-Konvention');
        $this->assertNotFalse($konvention);
        $this->assertNotFalse($haltung);
        $this->assertLessThan($haltung, $konvention);

        // Ein Link ist kein Download.
        $this->assertStringContainsString('Zum Nachlesen', $html);
        $this->assertStringNotContainsString('Dokumente zum Herunterladen', $html);

        $this->assertStringContainsString('content="Warum KE!N EINZELFALL e.V. die Istanbul-Konvention anerkennt', $html);
    }

    /** KEV-95: Abschnitt zur Beitrags- und Mitgliederordnung, direkt über den Dokumenten. */
    public function test_mitgliedschaft_erklaert_die_beitragsordnung(): void
    {
        $html = $this->get('/mitgliedschaft')->assertOk()->getContent();

        $antrag = strpos($html, 'Antrag auf Mitgliedschaft');
        $ordnung = strpos($html, 'id="abschnitt-beitrags-und-mitgliederordnung"');
        $dokumente = strpos($html, 'Dokumente zum Herunterladen');

        $this->assertNotFalse($ordnung);
        $this->assertTrue($antrag < $ordnung && $ordnung < $dokumente, 'Reihenfolge stimmt nicht');
        $this->assertStringContainsString('Unsere Beitrags- und Mitgliederordnung ergänzt die Satzung', $html);
        $this->assertStringContainsString('worauf sich eine Mitgliedschaft bei KE!N EINZELFALL e.V. stützt.', $html);

        $this->assertStringContainsString('Hilfe zum Ausfüllen', $html);
        $this->assertStringNotContainsString('Hlfe', $html);
    }

    /** KEV-88: neuer Text von Taddi im ersten Abschnitt der Satzung. */
    public function test_satzung_hat_den_neuen_text(): void
    {
        $html = $this->get('/satzung')->assertOk()->getContent();

        $this->assertStringContainsString('Was uns trägt, wie wir zusammenarbeiten und wofür wir Verantwortung übernehmen.', $html);
        $this->assertStringContainsString('Unsere Satzung bildet die verbindliche Grundlage unserer Vereinsarbeit.', $html);
        $this->assertStringContainsString('offen, nachvollziehbar und für alle einsehbar.', $html);
        $this->assertStringNotContainsString('Wir freuen uns, euch auf dieser Seite', $html);
        $this->assertStringNotContainsString('Unser Fundament', $html);
    }

    /** KEV-89: Unter „Satzung lesen“ steht ein Knopf zum PDF, nicht nur die Überschrift. */
    public function test_satzung_lesen_hat_einen_knopf_zum_pdf(): void
    {
        $html = $this->get('/satzung')->assertOk()->getContent();

        $abschnitt = strpos($html, 'id="abschnitt-satzung-lesen"');
        $knopf = strpos($html, 'Satzung lesen (PDF)');

        $this->assertNotFalse($abschnitt);
        $this->assertNotFalse($knopf);
        $this->assertGreaterThan($abschnitt, $knopf);
        $this->assertMatchesRegularExpression('#href="/dokumente/2026/05/26\.04\.02\.-Satzung-II\.pdf"[^>]*>\s*Satzung lesen \(PDF\)#', $html);
    }

    /** KEV-93: neuer Text von Taddi unter „Wir brauchen dich!“. */
    public function test_mitgliedschaft_wir_brauchen_dich_hat_den_neuen_text(): void
    {
        $html = $this->get('/mitgliedschaft')->assertOk()->getContent();

        $this->assertStringContainsString('Eigene Erfahrungen: Vielleicht bist du selbst betroffen', $html);
        $this->assertStringContainsString('das aus Erfahrung Wissen und Veränderung entstehen lässt.', $html);
        $this->assertStringNotContainsString('Einige Motivationen sind die folgenden.', $html);
        $this->assertStringNotContainsString('Hilfe und Unterstützung leisten', $html);
    }

    /** KEV-94: neuer Text von Taddi unter „Antrag auf Mitgliedschaft“. */
    public function test_mitgliedschaft_antrag_hat_den_neuen_text(): void
    {
        $html = $this->get('/mitgliedschaft')->assertOk()->getContent();

        $this->assertStringContainsString('Dann beginnt dein Weg genau hier mit dem Mitgliedsantrag.', $html);
        $this->assertStringContainsString('Weiter unten haben wir dir auch eine Ausfüllhilfe zur Verfügung gestellt.', $html);
        $this->assertStringContainsString('Wir freuen uns, wenn du Teil von KE!N EINZELFALL wirst.', $html);
        $this->assertStringNotContainsString('Für alle zukünftigen Mitglieder und Interessierten', $html);
    }

    /** KEV-96: Nachsatz zur Ordnung, Dokumente in Taddis Reihenfolge, Überschrift bleibt. */
    public function test_mitgliedschaft_dokumente_in_taddis_reihenfolge(): void
    {
        $html = $this->get('/mitgliedschaft')->assertOk()->getContent();

        $this->assertStringContainsString('Hier kannst du die Beitrags- und Mitgliederordnung vollständig einsehen.', $html);
        $this->assertStringContainsString('Dokumente zum Herunterladen', $html);

        $antrag = strpos($html, '1.5.1.-MA-0126.pdf');
        $hilfe = strpos($html, '1.5.1.1.-MA-AH-0126.pdf');
        $ordnung = strpos($html, '1.3.1.-B-M-O-0126.pdf');

        $this->assertNotFalse($antrag);
        $this->assertTrue($antrag < $hilfe && $hilfe < $ordnung, 'Reihenfolge der Dokumente stimmt nicht');
    }

    /** Prüfung der Firma (08.10.2026): Beim Import verlorene Knöpfe, Links und Beschriftungen. */
    public function test_verlorene_inhalte_der_altseite_sind_wieder_da(): void
    {
        $this->get('/das-hilfesystem')
            ->assertSee('href="https://events.teams.microsoft.com/event/e54c4ba0-16e3-495a-97af-6a56d0c521ee@f30279d7-3481-4b91-a979-f9430f7afde1"', false)
            ->assertSee('Jetzt anmelden')
            ->assertDontSee('Diese findest du unter: Veranstaltungen');

        // Vorbei: kein Anmeldeknopf, kein Doppelpunkt ins Leere.
        $this->get('/trauma-bindung-und-beziehung')
            ->assertSee('hat am 10. August 2026 bei Teams stattgefunden')
            ->assertDontSee('Jetzt anmelden');

        $buero = $this->get('/buerokratie-labyrinth')->getContent();
        $this->assertSame(1, substr_count($buero, 'Veranstaltung an: <a href="mailto:veranstaltung@kein-einzelfall.de"'));

        $this->get('/kontakt')
            ->assertSee('1. Vorsitzende: Tatjana Belmar')
            ->assertSee('Opferbeauftragter:')
            ->assertSee('href="/landesstellen"', false)
            // Kurze Zeilen werden nicht eingeklappt.
            ->assertDontSee('weitere Absätze');

        $this->get('/wissen')->assertSee('href="/fsm-erweitertes-hilfesystem"', false);
        $this->get('/kein-einzelfall-im-dialog')->assertSee('href="/das-hilfesystem"', false);
        $this->get('/veranstaltungen')
            ->assertDontSee('TEILNAHMEVEREINBARUNG')
            ->assertSee('Teilnahmevereinbarung');
    }

    /** Prüfung der Firma (08.10.2026): Impressum mit geltenden Gesetzen und lesbarer Adresse. */
    public function test_impressum_nennt_die_geltenden_gesetze(): void
    {
        $html = $this->get('/impressum')->assertOk()->getContent();

        $this->assertStringContainsString('Angaben gemäß § 5 DDG', $html);
        $this->assertStringContainsString('§ 18 Abs. 2 MStV', $html);
        $this->assertStringNotContainsString('TMG', $html);
        $this->assertStringNotContainsString('RStV', $html);
        $this->assertStringContainsString('<p>Schiffbeker Höhe 30</p>', $html);
        $this->assertStringNotContainsString('3022119', $html);

        $this->assertStringNotContainsString('TTDSG', $this->get('/datenschutz')->getContent());
    }

    /** KEV-104: Landesstellen mit Text, je Bundesland ein Abschnitt, im Menü unter Kontakt. */
    public function test_landesstellen(): void
    {
        $html = $this->get('/landesstellen')->assertOk()->getContent();

        foreach (['Bayern', 'Berlin', 'Hamburg', 'Sachsen-Anhalt', 'Schleswig-Holstein'] as $land) {
            $this->assertStringContainsString('id="abschnitt-landesstelle-'.\Illuminate\Support\Str::slug($land).'"', $html, $land);
            $this->assertStringContainsString('href="mailto:LS-'.$land.'@kein-einzelfall.de"', $html, $land);
        }

        // Der erste Satz steht als Unterzeile auf dem Bild, im Text nicht noch einmal.
        $this->assertSame(1, substr_count($html, 'ist nicht nur an einem Ort zuhause.'));
        $this->assertStringContainsString('src="/img/titelbilder/landesstellen.webp"', $html);

        $this->assertStringContainsString('Elke Redeker', $html);
        $this->assertStringNotContainsString('Reedeker', $html);

        $kontakt = collect(config('navigation.main'))->firstWhere('url', '/kontakt');
        $this->assertContains('/landesstellen', array_column($kontakt['children'], 'url'));
    }

    /** KEV-105: Gremium UKFB mit Text, veröffentlicht, Kontakt auf der echten Domain. */
    public function test_gremium_ukfb(): void
    {
        $html = $this->get('/gremium-ukfb')->assertOk()->getContent();

        $this->assertStringContainsString('Assoziiertes Fachgremium – UKFB', $html);
        $this->assertStringContainsString('Unabhängiges Kuratorium für Betroffenenexpertise', $html);
        $this->assertStringContainsString('nimmt keinen Einfluss auf die inhaltliche Arbeit', $html);
        $this->assertStringContainsString('src="/img/titelbilder/gremium-ukfb.webp"', $html);

        // Taddi schrieb ufb.org und ukf.org; ufb.org steht zum Verkauf.
        $this->assertStringContainsString('href="mailto:kontakt@ukfb.org"', $html);
        $this->assertStringContainsString('href="https://ukfb.org"', $html);
        $this->assertStringNotContainsString('ufb.org"', $html);
        $this->assertStringNotContainsString('ukf.org', $html);

        $verein = collect(config('navigation.main'))->firstWhere('url', '/verein');
        $this->assertSame('/gremium-ukfb', end($verein['children'])['url']);
    }

    /** KEV-97: Schutz- und Wertekonzept und Red Flags, im Menü „Verein“ an Position 4 und 5. */
    public function test_schutz_und_wertekonzept_und_red_flags(): void
    {
        $html = $this->get('/schutz-und-wertekonzept')->assertOk()->getContent();
        $this->assertStringContainsString('Schutz, Würde und Selbstbestimmung sind Grundlagen unserer Arbeit.', $html);
        $this->assertStringContainsString('Der Mensch steht vor dem Verfahren. Immer.', $html);
        $this->assertStringContainsString('src="/img/titelbilder/platzhalter.webp"', $html);

        $html = $this->get('/red-flags')->assertOk()->getContent();
        $this->assertStringContainsString('Gemeinsam gelingt Zusammenarbeit am besten', $html);
        $this->assertStringContainsString('Leitfaden herunterladen', $html);
        $this->assertStringContainsString('6.1.2.-Must-haves-u.-Red-Flaggs-fuer-Betroffene.pdf', $html);
        $this->assertStringContainsString('src="/img/titelbilder/platzhalter.webp"', $html);

        // Der leere Entwurf ist gefüllt, nicht verdoppelt.
        $this->assertFalse(Page::where('slug', 'schutzkonzept')->exists());

        $verein = collect(config('navigation.main'))->firstWhere('url', '/verein');
        $this->assertSame('/schutz-und-wertekonzept', $verein['children'][3]['url']);
        $this->assertSame('/red-flags', $verein['children'][4]['url']);
    }

    /** KEV-103: neue Seite unter Verein, hinter dem Kinderkodex, mit Platzhalterbild. */
    public function test_taetigkeits_und_jahresberichte(): void
    {
        $html = $this->get('/taetigkeits-und-jahresberichte')->assertOk()->getContent();

        $this->assertStringContainsString('Tätigkeits- und Jahresberichte', $html);
        $this->assertStringContainsString('Hinter jedem Jahr stehen Menschen, Begegnungen, Ideen', $html);
        $this->assertStringContainsString('Beginnend mit dem Jahr 2025 findest du hier unsere Berichte', $html);
        $this->assertStringContainsString('src="/img/titelbilder/platzhalter.webp"', $html);

        // Hinter dem Kinderkodex (seit KEV-105 steht das Gremium UKFB dahinter).
        $verein = collect(config('navigation.main'))->firstWhere('url', '/verein');
        $urls = array_column($verein['children'], 'url');
        $this->assertSame(array_search('/kinderkodex', $urls) + 1, array_search('/taetigkeits-und-jahresberichte', $urls));
    }

    /**
     * KEV-75: Das Bild ist nur noch Hintergrund, das echte Logo liegt als
     * eigenes Element darauf. Auf dem Handy über dem Text, nicht darunter.
     */
    public function test_verein_traegt_das_echte_logo_auf_dem_titelbild(): void
    {
        $html = $this->get('/verein')->assertOk()->getContent();

        $this->assertStringContainsString('src="/img/titelbilder/verein.webp"', $html);
        $this->assertMatchesRegularExpression('#<img src="/img/logo-gross\.webp" alt=""[^>]*data-kopf-logo#', $html);

        // Auf dem Handy so hoch wie der Inhalt, eine Mindesthöhe machte nur
        // die Lücke zwischen Logo und Text größer. Erst ab md halbe Höhe.
        $this->assertStringContainsString('relative isolate flex overflow-hidden md:min-h-[50svh]', $html);
        $this->assertStringNotContainsString('min-h-[60svh]', $html);

        // Nur dort: Andere Seiten haben ihr Motiv im Bild.
        $this->assertStringNotContainsString('data-kopf-logo', $this->get('/spenden')->getContent());
    }

    /** KEV-77: Taddis Bild statt Platzhalter, Motiv unten wie üblich. */
    public function test_satzung_hat_eigenes_titelbild(): void
    {
        $html = $this->get('/satzung')->assertOk()->getContent();

        $this->assertStringContainsString('src="/img/titelbilder/satzung.webp"', $html);
        $this->assertStringNotContainsString('platzhalter.webp', $html);
        $this->assertStringContainsString('object-position: 0% 100%', $html);
    }

    public function test_jedes_gesetzte_titelbild_gibt_es_als_datei(): void
    {
        Page::whereNotNull('titelbild')->pluck('titelbild')->each(
            fn ($pfad) => $this->assertFileExists(public_path(ltrim($pfad, '/')))
        );

        $this->assertGreaterThan(10, Page::whereNotNull('titelbild')->count());
    }

    /**
     * Die Flächen der Abschnitte einer Seite in Dokumentreihenfolge — vom
     * Seitenkopf bis zum Kontakt-Abschluss, so wie sie untereinander stehen.
     *
     * @return list<string>
     */
    private function flaechen(string $pfad): array
    {
        $html = $this->get($pfad)->getContent();
        $inhalt = preg_match('/<main[^>]*>(.*?)<\/main>/s', $html, $m) ? $m[1] : '';

        // Nur die obersten Kinder von <main>: Kästen innerhalb eines
        // Abschnitts tragen selbst bg-card und zählen nicht als Abschnitt.
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8"?><main>'.$inhalt.'</main>');
        $main = $dom->getElementsByTagName('main')->item(0);

        $flaechen = [];
        foreach ($main->childNodes as $kind) {
            if (! $kind instanceof \DOMElement || ! in_array($kind->tagName, ['header', 'section', 'aside', 'div'], true)) {
                continue;
            }

            // Ein Abschnitt hat oben und unten Luft (py-…). Was nur oben Luft
            // hat — die Sprungmarken-Leiste — gehört zum Abschnitt darunter.
            $klassen = $kind->getAttribute('class');
            if ($kind->tagName === 'div' && ! preg_match('/\bpy-/', $klassen)) {
                continue;
            }

            /*
             * Bausteine, die als `anschliessend` eingestuft sind, bleiben
             * absichtlich auf der Fläche des Abschnitts davor, weil sie zu ihm
             * gehören — der Hinweis-Kasten deckt auf der Karte sogar deren
             * untere Linie ab, damit keine Naht entsteht. Sie sind kein
             * eigener Abschnitt und dürfen hier nicht als einer zählen.
             *
             * Aufgefallen am 22.09.2026: Bis dahin gab es keinen einzigen
             * Hinweis-Baustein im Bestand, der Fall kam also nie vor. Der
             * erste — der REHADAT-Verweis auf /wissen — hätte den Test
             * eigentlich sofort brechen müssen.
             */
            if ($kind->hasAttribute('data-anschliessend')) {
                continue;
            }

            // Hinweisleisten über dem Kopf (Entwurf, Sprachrückfall, Leichte
            // Sprache) sind kein Inhaltsabschnitt: schmale Streifen mit
            // eigener Linie. Seit der Seitenkopf kein eigenes Band mehr ist
            // (23.09.2026), steht direkt darunter der erste Abschnitt.
            if ($kind->hasAttribute('data-hinweisleiste')) {
                continue;
            }

            $flaechen[] = str_contains($klassen, 'bg-card') ? 'card' : 'cream';
        }

        return $flaechen;
    }

    /**
     * Textbausteine hintereinander bilden einen Artikel: eine Fläche, keine
     * Bänder dazwischen (Abnahme 23.09.2026). Bis dahin stand hier der
     * gegenteilige Test — jeder Textbaustein wechselte die Fläche, und genau
     * das liess zwei Abschnitte wie zwei leere Kästen aussehen.
     */
    public function test_aufeinanderfolgende_textbausteine_bilden_einen_artikel(): void
    {
        $block = fn (string $typ) => new PageBlock(['typ' => $typ, 'data' => ['titel' => 'T']]);

        $abschnitte = PageBlock::abschnitte([
            $block('text'), $block('text'), $block('text'),
            $block('cta_band'),
            $block('text'),
            $block('donation_options'),
            $block('text'), $block('text'),
        ]);

        $this->assertSame([3, 1, 1, 1, 2], array_map(fn ($a) => count($a['bloecke']), $abschnitte));
    }

    public function test_lange_seiten_bekommen_ein_mitlaufendes_verzeichnis(): void
    {
        $html = $this->get('/kontakt')->assertOk()->getContent();

        // Seitenleiste im Artikel, der Kasten oben nur noch unterhalb von „lg“.
        $this->assertStringContainsString('data-verzeichnis', $html);
        $this->assertMatchesRegularExpression('/lg:hidden[^>]*>\s*<div class="mx-auto max-w-6xl">\s*<div class="max-w-prose">\s*<nav/', $html);

        // Alle fünf Abschnitte stehen in einem einzigen Artikel.
        $this->assertSame(1, substr_count($html, 'data-verzeichnis'));
    }

    public function test_kurze_abschnitte_der_startseite_stehen_nebeneinander(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // „Verein“ (bis KEV-54 „Vereinsarbeit“) und „Mitglieder“ in einem
        // gemeinsamen Raster. „Verein“ steht auch im Menü, deshalb die Überschrift.
        $this->assertMatchesRegularExpression(
            '/md:grid-cols-2[^"]*">(?:(?!<section).)*>\s*Verein\s*<\/h2>(?:(?!<section).)*Mitglieder/s', $html);
    }

    /**
     * Keine zwei benachbarten Abschnitte auf derselben Fläche — auf keiner Seite.
     *
     * Bis September 2026 wurde stur nach Position gewechselt, und nur der
     * Textbaustein hat mitgemacht: Auf /selbsthilfegruppen standen drei helle
     * Abschnitte hintereinander, auf /verein eine Karte direkt über der Karte
     * „Weiterlesen". Bausteine, die nichts miteinander zu tun haben, sahen
     * aus, als gehörten sie zusammen.
     */
    public function test_benachbarte_abschnitte_stehen_nie_auf_derselben_flaeche(): void
    {
        $pfade = Page::veroeffentlicht()
            ->where('locale', 'de')
            ->pluck('slug')
            ->map(fn ($slug) => $slug === 'startseite' ? '/' : '/'.$slug)
            ->push('/veranstaltungen')
            ->unique();

        foreach ($pfade as $pfad) {
            $flaechen = $this->flaechen($pfad);

            foreach ($flaechen as $i => $flaeche) {
                if ($i === 0) {
                    continue;
                }

                $this->assertNotSame(
                    $flaechen[$i - 1], $flaeche,
                    "{$pfad}: Abschnitt {$i} steht auf derselben Fläche wie sein Vorgänger (".implode(' > ', $flaechen).')'
                );
            }
        }
    }

    public function test_seiten_fuehren_zu_verwandten_seiten_statt_in_eine_sackgasse(): void
    {
        $this->get('/satzung')
            ->assertSee('weiterlesen-titel', false)
            ->assertSee('Mitgliedschaft');
    }

    public function test_bereichsuebersichten_fuehren_zu_ihren_unterseiten(): void
    {
        // /verein ist selbst ein Bereich — dort sollen die Unterseiten stehen,
        // nicht die Geschwister.
        $this->get('/verein')
            ->assertSee('Satzung')
            ->assertSee('Mitgliedschaft');
    }

    public function test_rechtstexte_bekommen_keinen_kontakt_aufruf(): void
    {
        // „Du möchtest uns etwas mitteilen?" unter einer Datenschutzerklärung
        // wäre deplatziert.
        $this->get('/datenschutz')->assertDontSee('Du möchtest uns etwas mitteilen?');
        $this->get('/impressum')->assertDontSee('Du möchtest uns etwas mitteilen?');

        // Inhaltsseiten dagegen schon, mit Taddis Text (KEV-71)
        $this->get('/erwerbsminderungsrente')
            ->assertSee('Du möchtest uns etwas mitteilen?')
            ->assertSee('oder hast Ideen für Projekte, oder möchtest uns etwas mitteilen?')
            ->assertDontSee('Fragen zu diesem Thema');
    }

    public function test_erster_absatz_wird_zum_vorspann_ohne_verloren_zu_gehen(): void
    {
        $seite = Page::where('slug', 'verein')->first();

        // Der Text des ersten Absatzes muss weiterhin auf der Seite stehen —
        // er wandert nur in den Kopf, er verschwindet nicht.
        $ersterAbsatz = $seite->blocks->first()->data['absaetze'][0] ?? null;
        $this->assertNotNull($ersterAbsatz);

        $this->get('/verein')->assertSee(mb_substr($ersterAbsatz, 0, 60), false);
    }

    public function test_uebersichtsseiten_verwenden_einheitliche_breiten(): void
    {
        // Auf /veranstaltungen lag ein eigener Container mit px-4 um die Seite,
        // während die eingebetteten Bausteine denselben Rand nochmal mitbrachten.
        // Das Padding addierte sich, die Breite wechselte zwischen max-w-4xl und
        // max-w-6xl — die Seite wirkte dadurch unruhig.
        foreach (['/veranstaltungen', '/aktuelles', '/verein'] as $pfad) {
            $html = $this->get($pfad)->getContent();
            $inhalt = preg_match('/<main[^>]*>(.*?)<\/main>/s', $html, $m) ? $m[1] : '';

            preg_match_all('/mx-auto max-w-(\w+)/', $inhalt, $treffer);
            $breiten = array_unique($treffer[1]);

            $this->assertContains('6xl', $breiten, "Hauptcontainer fehlt auf {$pfad}");
            $this->assertNotContains('4xl', $breiten, "Abweichende Breite auf {$pfad}");
        }
    }

    public function test_seitenrand_wird_nicht_doppelt_gesetzt(): void
    {
        // Ein Baustein mit px-4 innerhalb eines Containers mit px-4 ergibt den
        // doppelten Abstand — genau das sprang auf /veranstaltungen ins Auge.
        $html = $this->get('/veranstaltungen')->getContent();
        $inhalt = preg_match('/<main[^>]*>(.*?)<\/main>/s', $html, $m) ? $m[1] : '';

        $this->assertSame(
            0,
            preg_match_all('/<div class="px-4[^"]*lg:px-10[^"]*">\s*<div class="mx-auto max-w-4xl"/', $inhalt),
            'Verschachtelter Seitenrand gefunden'
        );
    }

    public function test_jede_seite_hat_weiterhin_genau_eine_h1(): void
    {
        // Der Seitenkopf bringt eine eigene Überschrift mit — es darf keine
        // zweite dazukommen.
        foreach (['verein', 'satzung', 'datenschutz', 'wissen'] as $slug) {
            $html = $this->get("/{$slug}")->getContent();

            $this->assertSame(1, preg_match_all('/<h1[^>]*>/', $html), "Seite /{$slug}");
        }
    }
}
