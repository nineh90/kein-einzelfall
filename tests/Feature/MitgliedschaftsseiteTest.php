<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\MitgliedschaftsseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Seite „Mitgliedschaft“ nach Taddis Tickets KEV-196 bis KEV-203.
 */
class MitgliedschaftsseiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    private function seite(): Page
    {
        return Page::where('slug', 'mitgliedschaft')->where('locale', 'de')->firstOrFail();
    }

    private function abschnitt(string $titel): ?\App\Models\PageBlock
    {
        return $this->seite()->blocks()->where('typ', 'text')->get()->first(fn ($b) => ($b->data['titel'] ?? null) === $titel);
    }

    /** KEV-199: „Mitglied werden“ mit Taddis Text zur anonymen Mitgliedschaft, ganz zu sehen, mit Knopf zum Antrag. */
    public function test_mitglied_werden_mit_anonymer_mitgliedschaft(): void
    {
        $html = $this->get('/mitgliedschaft')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h2[^>]*>\\s*Mitglied werden\\s*</h2>#', $html);
        $this->assertStringContainsString('Deshalb kannst du bei uns auch anonym Mitglied werden.', $html);
        $this->assertStringContainsString('href="mailto:verwaltung@kein-einzelfall.de"', $html);
        $this->assertStringContainsString('dass sie hinter unserer Arbeit stehen.', $html);
        $this->assertMatchesRegularExpression('#href="'.preg_quote(MitgliedschaftsseiteSeeder::ANTRAG_PDF, '#').'"[^>]*>\\s*Mitgliedsantrag \\(PDF\\)#', $html);

        // Stand der Demo davor: der Text von KEV-94.
        $block = $this->abschnitt(MitgliedschaftsseiteSeeder::ANTRAG_TITEL);
        $block->update(['data' => ['titel' => 'Antrag auf Mitgliedschaft', 'absaetze' => AltseiteSeeder::NEUE_TEXTE['mitgliedschaft']['Antrag auf Mitgliedschaft']]]);

        $migration = require database_path('migrations/2026_10_26_250000_mitgliedschaft_mitglied_werden.php');
        $migration->up();

        $data = $block->fresh()->data;
        $this->assertSame(MitgliedschaftsseiteSeeder::ANTRAG_TITEL, $data['titel']);
        $this->assertSame(MitgliedschaftsseiteSeeder::ANTRAG_ABSAETZE, $data['absaetze']);
        $this->assertTrue($data['alles_zeigen']);
    }
}
