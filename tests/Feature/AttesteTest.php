<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\AttesteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-144: Seite „Atteste“ unter Wissen, mit Taddis Text, ihrem Bild und den
 * 27 Attestvorlagen der Altseite (Reihe 6.5.1).
 */
class AttesteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_seite_mit_taddis_text_und_hervorgehobenem_satz(): void
    {
        $html = $this->get('/atteste')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Atteste - Kein Einzelfall e.V.</title>', $html);
        $this->assertStringContainsString('Manchmal fehlen die Worte, um zu erklären, warum etwas nicht geht', $html);
        $this->assertStringContainsString('die individuell mit den behandelnden Fachpersonen abgestimmt werden können.', $html);
        // Der Satz in Gänsefüßchen: fett, die inneren Anführungszeichen bleiben.
        $this->assertMatchesRegularExpression('/<strong[^>]*>Damit Du nicht jedes „Warum“ selbst erklären musst\.<\/strong>/u', $html);
        $this->assertStringNotContainsString('*Damit', $html);
    }

    /** KEV-243, KEV-244: Unterzeile, grüne Überschrift, Absatz mit Adresse. */
    public function test_unterzeile_ueberschrift_und_kontaktadresse(): void
    {
        $html = $this->get('/atteste')->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Atteste\s*<\/h1>\s*(?:\{\{--.*?--\}\}\s*)?<p[^>]*>Wenn Worte allein nicht reichen<\/p>/su', $html);
        // Vereinsname am Stück, wie bei den Formularen (KEV-247).
        $this->assertMatchesRegularExpression('/<h2[^>]*text-green[^>]*>\s*Attestmöglichkeiten von<br><span class="inline-block">KE!N EINZELFALL e\.V\.<\/span>\s*<\/h2>/u', $html);
        $this->assertStringContainsString('Wenn Dir hier ein Attest fehlt oder Du Fragen hast, schreib uns gerne an', $html);
        $this->assertStringContainsString('href="mailto:kontakt@kein-einzelfall.de"', $html);

        // Der neue Absatz steht vor dem fetten Schlusssatz.
        $this->assertLessThan(strpos($html, 'Damit Du nicht jedes'), strpos($html, 'Wenn Dir hier ein Attest fehlt'));
    }

    /** Bestehende Seiten bekommen KEV-244 per Migration, Eigenes bleibt. */
    public function test_nachtragen_nur_auf_taddis_altem_text(): void
    {
        $einleitung = Page::where('slug', 'atteste')->where('locale', 'de')->sole()->blocks()->orderBy('position')->first();

        $einleitung->update(['data' => ['absaetze' => AttesteSeeder::ABSAETZE_VOR_KEV_244]]);
        AttesteSeeder::nachtragen();
        AttesteSeeder::nachtragen();
        $this->assertSame(
            ['titel' => AttesteSeeder::EINLEITUNG_TITEL, 'absaetze' => AttesteSeeder::ABSAETZE],
            $einleitung->fresh()->data,
        );

        $einleitung->update(['data' => ['titel' => 'Eigene', 'absaetze' => ['Vom Verein.']]]);
        AttesteSeeder::nachtragen();
        $this->assertSame(['titel' => 'Eigene', 'absaetze' => ['Vom Verein.']], $einleitung->fresh()->data);
    }

    public function test_alle_27_vorlagen_zum_herunterladen(): void
    {
        $html = $this->get('/atteste')->getContent();

        $this->assertStringContainsString('Attestvorlagen und Formulierungshilfen', $html);
        $this->assertCount(27, AttesteSeeder::VORLAGEN);
        $this->assertSame(27, preg_match_all('/<a href="\/dokumente\/2026\/07\/6\.5\.1\.-Info-Attest-[^"]+\.pdf"\s+download/', $html));

        foreach (AttesteSeeder::dokumente()['dokumente'] as $vorlage) {
            $datei = public_path($vorlage['url']);
            $this->assertFileExists($datei);
            $this->assertSame('%PDF-', file_get_contents($datei, false, null, 0, 5), $vorlage['url']);
            $this->assertStringContainsString('>'.e($vorlage['titel']).'</span>', $html);
        }

        // Lesbare Titel, keine Dateinamen und keine Tippfehler der Altseite.
        $this->assertStringNotContainsString('>6.5.1.', $html);
        $this->assertStringNotContainsString('Begeitperson', strip_tags($html));
        $this->assertStringNotContainsString('Alltaghs', strip_tags($html));
        $this->assertMatchesRegularExpression('/PDF-Datei, \d+ KB/', $html);
    }

    public function test_im_menue_wissen_vor_dem_glossar(): void
    {
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $urls = array_column($wissen['children'], 'url');

        // Seit KEV-150/152 Formulare und Fristen zwischen Atteste und den
        // Glossaren, die seit KEV-254 unter „Klartext statt Fachsprache“ stehen.
        $this->assertSame(
            ['/atteste', '/formulare', '/fristen', '/klartext-statt-fachsprache'],
            array_slice($urls, -4),
        );
        $this->assertStringContainsString('href="/atteste"', $this->get('/wissen')->getContent());
    }

    /**
     * KEV-245: Die 27 Vorlagen ab „md“ in zwei Spalten. Jede öffnet im Browser
     * (Titel ohne download) und lässt sich daneben herunterladen.
     */
    public function test_zwei_spalten_ansehen_und_herunterladen(): void
    {
        $html = $this->get('/atteste')->getContent();

        $this->assertStringContainsString('md:grid-cols-2', $html);
        $url = '/dokumente/2026/07/6.5.1.-Info-Attest-Fahrdienst.pdf';
        $this->assertMatchesRegularExpression('/<a href="'.preg_quote($url, '/').'"\s+class="group[^"]*">/', $html);
        $this->assertMatchesRegularExpression('/<a href="'.preg_quote($url, '/').'" download[^>]*>.*?Herunterladen.*?<span class="sr-only">: Fahrdienst<\/span>/s', $html);
        $this->assertSame(27, substr_count($html, 'öffnet im Browser'));

        // Kurze Listen bleiben einspaltig.
        $this->assertStringNotContainsString('md:grid-cols-2', $this->get('/satzung')->getContent());
    }

    /**
     * KEV-246 zeigte Taddis Kategorien vor den Seiten der alten Website. Seit
     * KEV-239 gibt es unter „Wissen“ nur noch ihre Kategorien.
     */
    public function test_mehr_zu_wissen_zeigt_die_anderen_kategorien(): void
    {
        $html = $this->get('/atteste')->getContent();
        preg_match('/id="weiterlesen-titel".*?<\/ul>/s', $html, $teil);
        preg_match_all('/href="([^"]+)"/', $teil[0] ?? '', $treffer);

        $this->assertSame(['/formulare', '/fristen', '/klartext-statt-fachsprache'], $treffer[1]);
    }

    /**
     * Taddis Bild (Papierstapel mit Stempel), rechts um Wand verlängert wie
     * die Jahresberichte, damit der Stapel nicht unter dem Titel liegt.
     */
    public function test_eigenes_titelbild(): void
    {
        $html = $this->get('/atteste')->getContent();

        $this->assertStringContainsString('src="/img/titelbilder/atteste.webp?v=', $html);
        $this->assertStringContainsString('/img/titelbilder/atteste-1000.webp?v=', $html);
        $this->assertStringNotContainsString('platzhalter.webp', $html);
        $this->assertStringContainsString('object-position: 0% 85%', $html);
        $this->assertNotContains('atteste', Titelbilder::PLATZHALTER_SEITEN);
        $this->assertSame([2000, 776], array_slice(getimagesize(public_path('img/titelbilder/atteste.webp')), 0, 2));
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'atteste')->where('locale', 'de')->firstOrFail();
        $text = $seite->blocks()->where('typ', 'text')->firstOrFail();
        $text->update(['data' => ['absaetze' => ['Vom Verein geändert.']]]);

        AttesteSeeder::anlegen();
        (require database_path('migrations/2026_10_22_235000_atteste_anlegen.php'))->up();

        $this->assertSame(1, Page::where('slug', 'atteste')->where('locale', 'de')->count());
        $this->assertSame(2, $seite->blocks()->count());
        $this->assertSame(['Vom Verein geändert.'], $text->fresh()->data['absaetze']);
    }

    public function test_englische_fassung_verlinkt_dieselben_dateien(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/atteste')->assertOk()->getContent();

        $this->assertStringContainsString('Medical certificates', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>So that you don(\'|&#039;)t have to explain every “why” yourself\.<\/strong>/u', $html);
        $this->assertStringContainsString('Certificate templates and wording aids', $html);
        $this->assertStringContainsString('Cancellation fee for short-notice cancellations', $html);
        // Dateien bekommen kein Sprachpräfix. Je Datei zwei Links (KEV-245):
        // ansehen und herunterladen.
        $this->assertSame(54, preg_match_all('/<a href="\/dokumente\/2026\/07\/6\.5\.1\.-Info-Attest-/', $html));
        $this->assertSame(27, preg_match_all('/\.pdf" download/', $html));
        $this->assertStringNotContainsString('/en/dokumente/', $html);
    }
}
