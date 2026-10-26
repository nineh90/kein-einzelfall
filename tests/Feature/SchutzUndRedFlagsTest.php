<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Titelbilder;
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

    /**
     * KEV-206, KEV-209, KEV-210: Überschrift „Leitfaden für die Zusammenarbeit“,
     * darunter „Red Flags & Green Lights“, neuer Titel der Download-Liste. Menü,
     * Brotkrumen und Browser-Tab bleiben „Red Flags“. Auch per Migration.
     */
    public function test_red_flags_ueberschriften(): void
    {
        $seite = $this->seite('red-flags');
        $seite->update(['titelbild' => Titelbilder::datei('red-flags')]);

        $html = $this->get('/red-flags')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<h1[^>]*>\s*Leitfaden für die Zusammenarbeit\s*</h1>\s*<p[^>]*>\s*Red Flags &amp; Green Lights#', $html);
        $this->assertStringContainsString('<title>Red Flags - Kein Einzelfall e.V.</title>', $html);
        $this->assertStringContainsString('aria-current="page" class="text-ink">Red Flags<', $html);
        $this->assertStringContainsString('Leitfaden für die Zusammenarbeit herunterladen', $html);

        // Stand davor.
        $seite->update(['titel' => 'Red Flags', 'untertitel' => null]);
        $liste = $seite->blocks()->where('typ', 'download_list')->first();
        $liste->update(['data' => array_replace($liste->data, ['titel' => 'Leitfaden herunterladen'])]);

        $migration = require database_path('migrations/2026_10_26_300000_red_flags_ueberschrift.php');
        $migration->up();

        $seite = $seite->fresh();
        $this->assertSame('Leitfaden für die Zusammenarbeit', $seite->titel);
        $this->assertSame('Red Flags & Green Lights', $seite->untertitel);
        $this->assertSame('Leitfaden für die Zusammenarbeit herunterladen', $liste->fresh()->data['titel']);
    }

    /** KEV-210: Das verlängerte Bild hat seinen eigenen Ausschnitt. */
    public function test_red_flags_bild_ausschnitt(): void
    {
        $this->assertSame('40% 30%', Titelbilder::fokus(Titelbilder::datei('red-flags')));
        $this->assertSame('0% 100%', Titelbilder::fokus('/img/titelbilder/gibt-es-nicht.webp'));
    }
}
