<?php

namespace Tests\Feature;

use App\Models\Group;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Taddis neue Kurzbeschreibungen der Arbeitsgruppen (KEV-315 ff.): auf der
 * Karte und unter der Überschrift der Seite, auch per Migration.
 */
class KurzbeschreibungenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    private function pruefen(string $slug, string $alt, string $neu, string $migration): void
    {
        $this->assertSame($neu, Group::where('slug', $slug)->value('teaser'));

        $karte = $this->get('/arbeitsgruppen')->assertOk()->getContent();
        $karte = substr($karte, strpos($karte, '<article id="'.$slug.'"'));
        $this->assertStringContainsString(e($neu), substr($karte, 0, strpos($karte, '</article>')));
        $this->get('/arbeitsgruppen/'.$slug)->assertOk()->assertSee($neu);

        Group::where('slug', $slug)->update(['teaser' => $alt]);
        (require database_path("migrations/{$migration}.php"))->up();
        $this->assertSame($neu, Group::where('slug', $slug)->value('teaser'));

        Group::where('slug', $slug)->update(['teaser' => 'Eigene Zeile']);
        (require database_path("migrations/{$migration}.php"))->up();
        $this->assertSame('Eigene Zeile', Group::where('slug', $slug)->value('teaser'));
    }

    /** KEV-315 */
    public function test_ag_3(): void
    {
        $this->pruefen('ag-03-online-veranstaltungen',
            'Aus einer Idee wird ein Termin – und aus einem Termin ein Raum für Austausch, Wissen und Begegnung.',
            'Aus Ideen werden Begegnungen.', '2026_11_02_110000_ag3_kurzbeschreibung');
    }
}
