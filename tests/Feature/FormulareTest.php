<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\FormulareSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-150: „Formulare“ unter „Wissen“, Taddis Text, die Formulare folgen.
 */
class FormulareTest extends TestCase
{
    use RefreshDatabase;

    public function test_seite_unter_wissen_zwischen_atteste_und_fristen(): void
    {
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $urls = array_column($wissen['children'], 'url');
        $hier = array_search('/formulare', $urls);

        $this->assertSame('/atteste', $urls[$hier - 1]);
        $this->assertSame('/fristen', $urls[$hier + 1]);

        $html = $this->get('/formulare')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Formulare\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Manchmal braucht es einfach den nächsten Schritt – und dafür das richtige Formular.', $html);
        $this->assertStringContainsString('zum Beispiel Schweigepflichtentbindungen, Vollmachten, Mitgliedsanträge und weitere Unterlagen.', $html);
        $this->assertStringContainsString('Damit du nicht lange suchen musst', $html);
    }

    /** Die Formulare folgen: Die Liste steht bereit, zeigt sich aber erst mit Inhalt. */
    public function test_liste_erscheint_erst_mit_formularen(): void
    {
        $html = $this->get('/formulare')->getContent();
        $this->assertStringNotContainsString('Formulare zum Herunterladen', $html);
        // Verspricht noch Formulare, die es nicht gibt: nicht in Suchmaschinen.
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);

        $liste = Page::where('slug', 'formulare')->where('locale', 'de')->firstOrFail()
            ->blocks()->where('typ', 'download_list')->firstOrFail();
        $liste->update(['data' => ['titel' => 'Formulare zum Herunterladen', 'dokumente' => [
            ['titel' => 'Mitgliedsantrag', 'url' => 'https://example.org/mitgliedsantrag.pdf'],
        ]]]);

        $html = $this->get('/formulare')->getContent();
        $this->assertStringContainsString('Formulare zum Herunterladen', $html);
        $this->assertStringContainsString('Mitgliedsantrag', $html);
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'formulare')->where('locale', 'de')->firstOrFail();
        $seite->blocks()->first()->update(['data' => ['absaetze' => ['Vom Verein geändert.']]]);

        FormulareSeeder::anlegen();
        (require database_path('migrations/2026_10_23_210000_formulare_anlegen.php'))->up();

        $this->assertSame(1, Page::where('slug', 'formulare')->where('locale', 'de')->count());
        $this->assertSame(['Vom Verein geändert.'], $seite->blocks()->first()->fresh()->data['absaetze']);
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/formulare')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Forms\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Sometimes all it takes is the next step', $html);
    }
}
