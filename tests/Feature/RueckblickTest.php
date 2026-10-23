<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\RueckblickSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-142: Rückblick mit dem Abschnitt „KE!N EINZELFALL im Dialog – Trauma,
 * Bindung und Beziehung“ und Taddis Bild.
 */
class RueckblickTest extends TestCase
{
    use RefreshDatabase;

    public function test_abschnitt_mit_taddis_text_und_bild(): void
    {
        $html = $this->get('/rueckblick')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h2[^>]*>KE!N EINZELFALL im Dialog – Trauma, Bindung und Beziehung<\/h2>/u', $html);
        $this->assertStringContainsString('startete am 10. August 2026 unsere Veranstaltungsreihe', $html);
        $this->assertStringContainsString('Als Referenten durften wir Lutz-Ulrich Besser, Facharzt für Psychiatrie und Psychotherapie', $html);
        $this->assertStringContainsString('Fragen konnten auch ausschließlich über den Chat gestellt werden.', $html);
        // Komma ergänzt
        $this->assertStringContainsString('Mit den Fragen, die Menschen, die mit den Folgen von Trauma leben, beschäftigen.', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>KE!N EINZELFALL im Dialog – Wissen trifft Erfahrung\.<\/strong>/u', $html);

        // Stimmungsbild ohne Bildbeschreibung
        $this->assertStringContainsString('src="/img/rueckblick/trauma-bindung-und-beziehung.webp?v=', $html);
        $this->assertMatchesRegularExpression('/<img src="\/img\/rueckblick\/trauma-bindung-und-beziehung\.webp[^"]*" alt=""/', $html);
        $this->assertFileExists(public_path('img/rueckblick/trauma-bindung-und-beziehung.webp'));

        // Langer Text: Bild oben und mitlaufend
        $this->assertStringContainsString('lg:sticky', $html);
    }

    public function test_mit_inhalt_fuer_suchmaschinen_offen(): void
    {
        $seite = Page::where('slug', 'rueckblick')->where('locale', 'de')->firstOrFail();
        $this->assertFalse($seite->noindex);
        $this->assertStringContainsString('/rueckblick</loc>', $this->get('/sitemap.xml')->getContent());
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'rueckblick')->where('locale', 'de')->firstOrFail();
        $block = $seite->blocks()->firstOrFail();
        $block->update(['data' => array_merge($block->data, ['absaetze' => ['Vom Verein geändert.']])]);

        RueckblickSeeder::anlegen();
        (require database_path('migrations/2026_10_23_180000_rueckblick_trauma_bindung.php'))->up();

        $this->assertSame(1, $seite->blocks()->count());
        $this->assertSame(['Vom Verein geändert.'], $block->fresh()->data['absaetze']);
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/rueckblick')->assertOk()->getContent();

        $this->assertStringContainsString('KE!N EINZELFALL im Dialog – Trauma, attachment and relationships', $html);
        $this->assertStringContainsString('our event series “KE!N EINZELFALL im Dialog – Knowledge meets experience” began on 10 August 2026', $html);
        $this->assertStringContainsString('/img/rueckblick/trauma-bindung-und-beziehung.webp', $html);
    }
}
