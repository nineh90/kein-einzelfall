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

        // Seit KEV-155 steht „Behörden & Bescheide“ als zweites Glossar dahinter.
        $this->assertSame(['/atteste', '/glossar', '/behoerden-und-bescheide'], array_slice($urls, -3));
        $this->assertStringContainsString('href="/atteste"', $this->get('/wissen')->getContent());
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
        // Dateien bekommen kein Sprachpräfix.
        $this->assertSame(27, preg_match_all('/<a href="\/dokumente\/2026\/07\/6\.5\.1\.-Info-Attest-/', $html));
        $this->assertStringNotContainsString('/en/dokumente/', $html);
    }
}
