<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\SchutzkonzeptSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Schutz- und Wertekonzept (KEV-204) und Red Flags (KEV-206 bis KEV-210).
 */
class SchutzUndRedFlagsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    private function seite(string $slug): Page
    {
        return Page::where('slug', $slug)->where('locale', 'de')->firstOrFail();
    }

    /** KEV-204: Das Konzept als PDF, mit Knopf und zum Herunterladen. Die Datei liegt bei. */
    public function test_schutzkonzept_ist_verlinkt(): void
    {
        $this->assertFileExists(public_path(ltrim(SchutzkonzeptSeeder::PDF, '/')));

        $html = $this->get('/schutz-und-wertekonzept')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#href="'.preg_quote(SchutzkonzeptSeeder::PDF, '#').'"[^>]*>\\s*Schutz- und Wertekonzept lesen \\(PDF\\)#', $html);
        $this->assertStringContainsString('Unser Schutz- und Wertekonzept zum Downloaden', $html);

        // Auf einer bestehenden Seite ohne beides, und nur einmal.
        $seite = $this->seite('schutz-und-wertekonzept');
        $seite->blocks()->where('typ', 'download_list')->delete();
        $text = $seite->blocks()->where('typ', 'text')->first();
        $text->update(['data' => array_diff_key($text->data, ['cta' => 1])]);

        $migration = require database_path('migrations/2026_10_26_290000_schutzkonzept_pdf.php');
        $migration->up();
        $migration->up();

        $this->assertSame(SchutzkonzeptSeeder::KNOPF, $text->fresh()->data['cta']);
        $this->assertSame(1, $seite->blocks()->where('typ', 'download_list')->count());
    }
}
