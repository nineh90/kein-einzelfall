<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Taddis Unterzeilen unter dem Titel (Serie ab KEV-300): auf frischer
 * Datenbank, per Migration auf bestehender, Englisch aus dem Wörterbuch.
 */
class UnterzeilenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    private function pruefen(string $slug, string $text, string $englisch, string $migration): void
    {
        $this->assertSame($text, Page::where('slug', $slug)->where('locale', 'de')->value('untertitel'));
        $this->get('/'.$slug)->assertOk()->assertSee($text);

        // Bestehende Seite ohne Unterzeile: die Migration setzt sie, deutsch und englisch.
        Page::where('slug', $slug)->update(['untertitel' => null]);
        (require database_path("migrations/{$migration}.php"))->up();
        $this->assertSame($text, Page::where('slug', $slug)->where('locale', 'de')->value('untertitel'));

        $woerterbuch = json_decode(file_get_contents(database_path('seeders/data/uebersetzungen.json')), true);
        $this->assertSame($englisch, $woerterbuch[$text]['en']);

        // Eine eigene bleibt.
        Page::where('slug', $slug)->where('locale', 'de')->update(['untertitel' => 'Eigene Zeile']);
        (require database_path("migrations/{$migration}.php"))->up();
        $this->assertSame('Eigene Zeile', Page::where('slug', $slug)->where('locale', 'de')->value('untertitel'));
    }

    /** KEV-300 */
    public function test_kinderkodex(): void
    {
        $this->pruefen('kinderkodex', 'Schutz braucht Haltung', 'Protection needs a stance', '2026_11_01_170000_kinderkodex_unterzeile');
    }
}
