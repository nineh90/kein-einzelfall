<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-143: Bereichsseite „Wissen“ mit Taddis neuem Text und Bild.
 */
class WissenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_taddis_text_mit_hervorhebungen(): void
    {
        $html = $this->get('/wissen')->assertOk()->getContent();

        $this->assertStringContainsString('Wissen bedeutet mehr, als Informationen zu haben.', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Wissen bedeutet verstehen\. Und verstehen bedeutet, selbst handeln zu können\.<\/strong>/u', $html);
        $this->assertStringContainsString('von Fachbegriffen und Abkürzungen über Fristen und Verfahren bis hin zu Attesten, Formularen und Vorlagen.', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Wissen schafft Klarheit\. Und Klarheit stärkt Selbstwirksamkeit\.<\/strong>/u', $html);
        $this->assertStringNotContainsString('*', strip_tags(preg_replace('#<(script|style|head)\b.*?</\1>#s', '', $html)));

        // Der alte Text und die zwei Links auf 2 von 12 Unterseiten sind weg,
        // „Mehr zu „Wissen““ nennt alle.
        $this->assertStringNotContainsString('Wir möchten informieren', $html);
        $this->assertStringNotContainsString('Zu den einzelnen Kategorien', $html);
        foreach (['/atteste', '/formulare', '/fristen', '/glossar', '/behoerden-und-bescheide'] as $pfad) {
            $this->assertStringContainsString('href="'.$pfad.'"', $html, $pfad);
        }
    }

    public function test_neues_bild(): void
    {
        $html = $this->get('/wissen')->getContent();

        $this->assertStringContainsString('src="/img/titelbilder/wissen.webp?v=', $html);
        $this->assertStringContainsString('object-position: 0% 60%', $html);
        $this->assertSame([2000, 776], array_slice(getimagesize(public_path('img/titelbilder/wissen.webp')), 0, 2));
    }

    /** Was der Verein im Panel geändert hat, bleibt. */
    public function test_migration_tauscht_nur_den_alten_wortlaut(): void
    {
        $seite = Page::where('slug', 'wissen')->where('locale', 'de')->firstOrFail();
        $seite->blocks()->first()->update(['data' => ['absaetze' => ['Vom Verein geändert.']]]);

        (require database_path('migrations/2026_10_23_230000_wissen_text_neu.php'))->up();

        $this->assertSame(['Vom Verein geändert.'], $seite->blocks()->first()->fresh()->data['absaetze']);
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/wissen')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<strong[^>]*>Knowledge means understanding\. And understanding means being able to act for yourself\.<\/strong>/u', $html);
        $this->assertStringContainsString('Knowledge creates clarity.', $html);
    }
}
