<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use App\Support\Suche;
use App\Support\Titelbilder;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Database\Seeders\WallOfShameSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * KEV-157: Wall of Shame unter „Raum für Ungesagtes“, mit Taddis Einleitung,
 * 145 Sätzen und der Wand mit Zählstrichen.
 */
class WallOfShameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_einleitung_von_taddi(): void
    {
        $html = $this->get('/wall-of-shame')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Wall of Shame - Kein Einzelfall e.V.</title>', $html);
        $this->assertStringContainsString('Manche Sätze vergisst man nicht.', $html);
        $this->assertStringContainsString('beschämen oder einfach sprachlos machen …', $html);
        // Im Dokument fett, und „Gutachter“ korrigiert zu „Gutachtern“.
        $this->assertMatchesRegularExpression('/Auf unserer <strong[^>]*>Wall of Shame<\/strong> sammeln wir/u', $html);
        $this->assertStringContainsString('Gerichten, Gutachtern, Anwälten', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Nicht die Betroffenen sollten sich für diese Aussagen schämen müssen!<\/strong>/u', $html);
    }

    public function test_alle_saetze_als_liste_und_die_wand_erst_mit_javascript(): void
    {
        $html = $this->get('/wall-of-shame')->getContent();
        $saetze = WallOfShameSeeder::saetze();

        $this->assertCount(145, $saetze);
        $this->assertSame(145, substr_count($html, '<li data-satz="'));
        $this->assertStringContainsString('„So schlimm kann es ja nicht gewesen sein, wenn Sie später noch zwei Kinder bekommen haben.“', $html);
        $this->assertStringContainsString('„Ich verstehe ja, dass Sie das belastet, aber …“', $html);
        $this->assertStringContainsString('Alle 145 Sätze als Liste', $html);

        // Die Wand ist ohne Skript verborgen, der Knopf wäre sonst eine Attrappe.
        $this->assertMatchesRegularExpression('/<div data-wand hidden/', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
        $this->assertStringContainsString('data-wand-schluss', $html);
        // Erst ein Klick zeigt einen Satz: Auf dem Zettel steht anfangs nur die Anleitung.
        $this->assertMatchesRegularExpression('/data-wand-satz[^>]*>\s*Ein Klick auf den Papierstapel/u', $html);
    }

    public function test_bilder_von_taddi(): void
    {
        $html = $this->get('/wall-of-shame')->getContent();

        $this->assertStringContainsString('src="/img/titelbilder/wall-of-shame.webp?v=', $html);
        $this->assertStringContainsString('object-position: 0% 45%', $html);
        $this->assertNotContains('wall-of-shame', Titelbilder::PLATZHALTER_SEITEN);
        $this->assertStringContainsString('/img/wall-of-shame/wand.webp?v=', $html);
        $this->assertStringContainsString('/img/wall-of-shame/wand-1000.webp?v=', $html);
    }

    public function test_im_panel_ein_satz_pro_zeile(): void
    {
        $seite = Page::where('slug', 'wall-of-shame')->where('locale', 'de')->firstOrFail();
        $block = $seite->blocks()->where('typ', 'wall_of_shame')->firstOrFail();

        $test = Livewire::actingAs(User::factory()->redaktion()->create())
            ->test(EditPage::class, ['record' => $seite->getKey()]);

        // Im Formular steht die Liste als Text, eine Zeile je Satz.
        $daten = collect($test->get('data.blocks'))->firstWhere('typ', 'wall_of_shame');
        $this->assertStringStartsWith("So schlimm kann es ja nicht gewesen sein, wenn Sie später noch zwei Kinder bekommen haben.\nWarum", $daten['data']['saetze']);

        $schluessel = collect($test->get('data.blocks'))->search(fn ($b) => $b['typ'] === 'wall_of_shame');
        $test->set("data.blocks.{$schluessel}.data.saetze", "Erster neuer Satz.\n\n  Zweiter neuer Satz.  \nDritter.")
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['Erster neuer Satz.', 'Zweiter neuer Satz.', 'Dritter.'], $block->fresh()->data['saetze']);
        $this->assertStringContainsString('Alle 3 Sätze als Liste', $this->get('/wall-of-shame')->getContent());
    }

    public function test_suche_findet_die_saetze(): void
    {
        $treffer = array_column(app(Suche::class)->suchen('Aussage gegen Aussage')['treffer'], 'url');

        $this->assertContains('/wall-of-shame', $treffer);
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'wall-of-shame')->where('locale', 'de')->firstOrFail();
        $seite->blocks()->where('typ', 'wall_of_shame')->first()->update(['data' => ['saetze' => ['Vom Verein.']]]);

        WallOfShameSeeder::anlegen();
        (require database_path('migrations/2026_10_22_235990_wall_of_shame_anlegen.php'))->up();

        $this->assertSame(1, Page::where('slug', 'wall-of-shame')->where('locale', 'de')->count());
        $this->assertSame(2, $seite->blocks()->count());
        $this->assertSame(['Vom Verein.'], $seite->blocks()->where('typ', 'wall_of_shame')->first()->data['saetze']);
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/wall-of-shame')->assertOk()->getContent();

        $this->assertStringContainsString('Some sentences you never forget.', $html);
        $this->assertStringContainsString('„It can&#039;t have been that bad if you went on to have two children.“', $html);
        $this->assertStringContainsString('„You need to pull yourself together a bit now.“', $html);
        $this->assertStringContainsString('All 145 sentences as a list', $html);
        $this->assertStringContainsString('Read the next sentence', $html);
        // Kein Satz bleibt deutsch.
        $this->assertStringNotContainsString('„So schlimm kann es', $html);
    }
}
