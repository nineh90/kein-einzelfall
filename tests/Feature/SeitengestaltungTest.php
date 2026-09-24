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

    public function test_rechtstexte_haben_kein_titelbild(): void
    {
        $this->assertStringNotContainsString('/img/titelbilder/', $this->get('/impressum')->getContent());
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

        // „Vereinsarbeit“ und „Mitglieder“ in einem gemeinsamen Raster.
        $this->assertMatchesRegularExpression(
            '/md:grid-cols-2[^"]*">(?:(?!<section).)*Vereinsarbeit(?:(?!<section).)*Mitglieder/s', $html);
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
        // „Fragen zu diesem Thema?" unter einer Datenschutzerklärung wäre
        // deplatziert.
        $this->get('/datenschutz')->assertDontSee('Fragen zu diesem Thema');
        $this->get('/impressum')->assertDontSee('Fragen zu diesem Thema');

        // Inhaltsseiten dagegen schon
        $this->get('/erwerbsminderungsrente')->assertSee('Fragen zu diesem Thema');
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
