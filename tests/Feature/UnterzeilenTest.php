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

    /** KEV-301 */
    public function test_istanbul_konvention(): void
    {
        $this->pruefen('istanbul-konvention', 'Schutz vor Gewalt ist kein Privileg', 'Protection from violence is not a privilege', '2026_11_01_180000_istanbul_konvention_unterzeile');
    }

    /** KEV-303 */
    public function test_gruppen_und_veranstaltungen(): void
    {
        $this->pruefen('gruppen-und-veranstaltungen', 'Austausch verbindet. Wissen stärkt.', 'Connection through exchange. Strength through knowledge.', '2026_11_01_190000_gruppen_und_veranstaltungen_unterzeile');
    }

    /** KEV-317: ersetzt die längere Unterzeile, nur wo die noch steht. */
    public function test_veranstaltungen(): void
    {
        $neu = 'Wissen teilen. Perspektiven erweitern.';
        $alt = 'Wissen teilen. Erfahrungen verbinden. Gemeinsam Perspektiven schaffen.';
        $this->assertSame($neu, Page::where('slug', 'veranstaltungen')->where('locale', 'de')->value('untertitel'));

        Page::where('slug', 'veranstaltungen')->where('locale', 'de')->update(['untertitel' => $alt]);
        (require database_path('migrations/2026_11_02_130000_veranstaltungen_unterzeile.php'))->up();
        $this->assertSame($neu, Page::where('slug', 'veranstaltungen')->where('locale', 'de')->value('untertitel'));

        $woerterbuch = json_decode(file_get_contents(database_path('seeders/data/uebersetzungen.json')), true);
        $this->assertSame('Sharing knowledge. Broadening perspectives.', $woerterbuch[$neu]['en']);

        // Die Übersicht kommt vom EventController und zeigt sie im Kopf.
        $this->get('/veranstaltungen')->assertOk()->assertSee($neu)
            ->assertDontSee('Termine unserer Selbsthilfe- und Arbeitsgruppen sowie einzelne Veranstaltungen.');
    }

    /** KEV-318 */
    public function test_ehrenamtsmessen(): void
    {
        $this->pruefen('ehrenamtsmessen', 'Engagement zeigen. Mitstreiter gewinnen.', 'Showing commitment. Winning people over.', '2026_11_02_140000_ehrenamtsmessen_unterzeile');
    }

    /** KEV-319 */
    public function test_petitionen(): void
    {
        $this->pruefen('petitionen', 'Gemeinsam Forderungen Gehör verschaffen.', 'Making demands heard, together.', '2026_11_02_150000_petitionen_unterzeile');
    }

    /** KEV-321 */
    public function test_rueckblick(): void
    {
        $this->pruefen('rueckblick', 'Was war, wirkt weiter.', 'What has been lives on.', '2026_11_02_180000_rueckblick_unterzeile');
    }
}
