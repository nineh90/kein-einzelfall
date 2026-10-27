<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-143: Bereichsseite „Wissen“ mit Taddis neuem Text und Bild. Seit
 * KEV-239 nur noch Taddis Kategorien, seit KEV-242 als Knöpfe neben dem Text.
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
        // seit KEV-242 stehen alle Kategorien rechts als Knöpfe.
        $this->assertStringNotContainsString('Wir möchten informieren', $html);
        $this->assertStringNotContainsString('Zu den einzelnen Kategorien', $html);
        // Die Glossare seit KEV-254 gesammelt unter „Klartext statt Fachsprache“.
        foreach (['/atteste', '/formulare', '/fristen', '/klartext-statt-fachsprache'] as $pfad) {
            $this->assertStringContainsString('href="'.$pfad.'"', $html, $pfad);
        }
    }

    /**
     * KEV-239: Menü in Taddis Reihenfolge. Die alten Seiten bleiben
     * erreichbar, vier davon verlinkt „KE!N EINZELFALL im Dialog“.
     */
    public function test_nur_noch_taddis_kategorien(): void
    {
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $this->assertSame(
            [['Atteste', '/atteste'], ['Formulare und Download', '/formulare'],
                ['Fristen in Anträgen und Verfahren', '/fristen'], ['Klartext statt Fachsprache', '/klartext-statt-fachsprache']],
            array_map(fn ($k) => [$k['label'], $k['url']], $wissen['children']),
        );

        $this->get('/kein-einzelfall-im-dialog')->assertSee('href="/das-hilfesystem"', false);
        $this->get('/das-hilfesystem')->assertOk();
        $this->get('/erwerbsminderungsrente')->assertOk();

        $menue = $this->get('/wissen')->getContent();
        $this->assertStringNotContainsString('href="/erwerbsminderungsrente"', $menue);
        $this->assertStringNotContainsString('href="/das-hilfesystem"', $menue);
    }

    /** KEV-242: Taddis Kategorien als Knöpfe rechts neben dem Text, unten kein „Mehr zu“. */
    public function test_kategorien_als_knoepfe_neben_dem_text(): void
    {
        $html = $this->get('/wissen')->getContent();

        $this->assertMatchesRegularExpression(
            '#lg:grid-cols-\[minmax\(0,65ch\)_minmax\(0,1fr\)\].*?Selbstwirksamkeit.*?href="/atteste".*?Atteste.*?href="/formulare".*?Formulare und Download'
            .'.*?href="/fristen".*?Fristen in Anträgen und Verfahren.*?href="/klartext-statt-fachsprache"#su',
            $html,
        );
        $this->assertStringNotContainsString('id="weiterlesen-titel"', $html);

        // Auf den Unterseiten bleibt „Mehr zu „Wissen““.
        $this->assertStringContainsString('id="weiterlesen-titel"', $this->get('/fristen')->getContent());
    }

    /** KEV-241: Der REHADAT-Kasten der Migration vom 22.09.2026 kommt weg. */
    public function test_migration_nimmt_den_rehadat_kasten_weg_und_setzt_die_knoepfe_einmal(): void
    {
        $seite = Page::where('slug', 'wissen')->where('locale', 'de')->sole();
        $seite->blocks()->where('typ', 'seitenkarten')->delete();
        (require database_path('migrations/2026_09_22_140000_rehadat_verweis_auf_wissen.php'))->up();
        $this->assertStringContainsString('Urteile zum Schwerbehindertenrecht', $this->get('/wissen')->getContent());

        $migration = require database_path('migrations/2026_10_27_120000_wissen_nur_taddis_kategorien.php');
        $migration->up();
        $migration->up();

        $html = $this->get('/wissen')->getContent();
        $this->assertStringNotContainsString('REHADAT', $html);
        $this->assertSame(['text', 'seitenkarten'], $seite->blocks()->orderBy('position')->pluck('typ')->all());
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
