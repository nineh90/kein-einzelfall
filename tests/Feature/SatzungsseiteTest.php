<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\SatzungsseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Seite „Satzung“ nach Taddis Tickets KEV-193 bis KEV-195.
 */
class SatzungsseiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    private function verzeichnis(): string
    {
        $html = $this->get('/satzung')->assertOk()->getContent();
        $nav = substr($html, strpos($html, 'data-verzeichnis'));

        return substr($nav, 0, strpos($nav, '</nav>'));
    }

    /** KEV-193: Der Abschnitt zum Herunterladen steht im Verzeichnis und springt dorthin. */
    public function test_download_steht_im_verzeichnis(): void
    {
        $html = $this->get('/satzung')->getContent();

        preg_match('#href="\#(dl-[^"]+)"#', $this->verzeichnis(), $treffer);
        $this->assertNotEmpty($treffer);
        $this->assertStringContainsString('<h2 id="'.$treffer[1].'"', $html);
    }

    /** KEV-194: „Die Satzung lesen“ mit Taddis Text, der Knopf zum PDF bleibt. Auch per Migration. */
    public function test_die_satzung_lesen_mit_neuem_text(): void
    {
        $html = $this->get('/satzung')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h2[^>]*>\s*Die Satzung lesen\s*</h2>#', $html);
        $this->assertStringContainsString('<p>Unsere Satzung – die Grundlage für unser gemeinsames Handeln', $html);
        $this->assertStringContainsString('href="mailto:kontakt@kein-einzelfall.de"', $html);
        $this->assertStringNotContainsString('gerechtere Zukunft', $html);
        $this->assertStringContainsString('Satzung lesen (PDF)', $html);

        // Stand auf der Demo davor.
        $block = Page::where('slug', 'satzung')->where('locale', 'de')->first()->blocks()->where('typ', 'text')->get()
            ->first(fn ($b) => ($b->data['titel'] ?? null) === SatzungsseiteSeeder::LESEN_TITEL);
        $block->update(['data' => array_replace($block->data, ['titel' => 'Satzung lesen', 'absaetze' => [
            'Hier kannst du unsere Satzung vollständig einsehen.',
            'Solltest du Fragen zur Satzung haben oder eine barrierefreie Version benötigen, melde dich gerne bei uns.',
            'Satzung Gemeinsam für eine gerechtere Zukunft.',
        ]])]);

        $migration = require database_path('migrations/2026_10_26_230000_satzung_lesen_neuer_text.php');
        $migration->up();

        $data = $block->fresh()->data;
        $this->assertSame(SatzungsseiteSeeder::LESEN_TITEL, $data['titel']);
        $this->assertSame(SatzungsseiteSeeder::LESEN_ABSAETZE, $data['absaetze']);
        $this->assertSame('Satzung lesen (PDF)', $data['cta']['label']);
    }
}
