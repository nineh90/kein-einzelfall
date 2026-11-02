<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\GruppenUndVeranstaltungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Übersicht /gruppen-und-veranstaltungen nach Taddis Tickets.
 */
class GruppenUndVeranstaltungenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    /** KEV-290: „Deine Ideen sind gefragt!“ als Kasten unter dem Text, links neben dem Kalender. Auch per Migration. */
    public function test_ideen_kasten_unter_der_einleitung(): void
    {
        $html = $this->get('/gruppen-und-veranstaltungen')->assertOk()->getContent();
        $abschnitt = substr($html, strrpos(substr($html, 0, strpos($html, 'Wo Menschen zusammenkommen')), '<section'));
        $abschnitt = substr($abschnitt, 0, strpos($abschnitt, 'Das Team von KE!N EINZELFALL e.V.') + 40);

        // Unter dem Text, vor der rechten Spalte mit dem Kalender. Der Gruß
        // steht seit KEV-313 unter beiden Spalten.
        $this->assertMatchesRegularExpression(
            '#Entdecke unsere Gruppen.*?<h3[^>]*>\s*Deine Ideen sind gefragt!\s*</h3>.*?href="mailto:kontakt@kein-einzelfall\.de".*?Eine Idee genügt.*?data-mitlaufen.*?font-hand[^>]*>Das Team von KE!N EINZELFALL e\.V\.#s', $abschnitt);

        $einleitung = Page::where('slug', 'gruppen-und-veranstaltungen')->where('locale', 'de')->first()
            ->blocks()->where('typ', 'text')->orderBy('position')->first();
        $einleitung->update(['data' => array_diff_key($einleitung->data, ['kasten' => 1])]);

        $migration = require database_path('migrations/2026_11_01_210000_gruppen_und_veranstaltungen_ideen.php');
        $migration->up();
        $migration->up();
        $this->assertSame(GruppenUndVeranstaltungenSeeder::IDEEN, $einleitung->fresh()->data['kasten']);

        $woerterbuch = json_decode(file_get_contents(database_path('seeders/data/uebersetzungen.json')), true);
        foreach ([GruppenUndVeranstaltungenSeeder::IDEEN['titel'], ...GruppenUndVeranstaltungenSeeder::IDEEN['absaetze']] as $text) {
            $this->assertArrayHasKey($text, $woerterbuch);
        }
        $this->get('/en/gruppen-und-veranstaltungen')->assertOk()->assertSee('Your ideas are welcome!');

        // Ein im Panel geänderter Kasten bleibt.
        $einleitung->update(['data' => array_replace($einleitung->fresh()->data, ['kasten' => ['titel' => 'Eigener Kasten']])]);
        $migration->up();
        $this->assertSame(['titel' => 'Eigener Kasten'], $einleitung->fresh()->data['kasten']);
    }
}
