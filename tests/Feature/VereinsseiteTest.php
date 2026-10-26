<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\VereinsseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Seite „Gemeinnütziger Verein“ nach Taddis Tickets KEV-172 bis KEV-178.
 */
class VereinsseiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);

        // Wie auf der Demo: mit Titelbild. Erst damit hat der Kopf die grüne
        // Zeile über dem Titel.
        foreach (['verein', 'satzung'] as $slug) {
            Page::where('slug', $slug)->update(['titelbild' => Titelbilder::datei($slug)]);
        }
        Page::where('slug', 'verein')->update(['titel' => 'Gemeinnütziger Verein', 'untertitel' => 'Opferhilfe für soziale Gerechtigkeit!']);
    }

    /** KEV-172: oben klein „Gemeinnütziger Verein“, darunter groß der Vereinsname. */
    public function test_kopf_zeigt_den_vereinsnamen_gross(): void
    {
        $this->assertMatchesRegularExpression(
            '#<p class="[^"]*uppercase[^"]*">\s*Gemeinnütziger Verein\s*</p>\s*<h1[^>]*>\s*KE!N EINZELFALL e\.V\.\s*</h1>\s*'
            .'<p[^>]*>\s*Opferhilfe für soziale Gerechtigkeit!#',
            $this->get('/verein')->assertOk()->getContent());

        // Die Unterseiten behalten ihren Titel als Überschrift.
        $this->get('/satzung')->assertOk()->assertSeeInOrder(['<h1', 'Satzung', '</h1>'], false);
    }

    /** KEV-173: Taddis neuer Text, ganz zu sehen, mit Leitsatz und fettem Vereinsnamen. */
    public function test_vereinsseite_zeigt_taddis_neuen_text(): void
    {
        $html = $this->get('/verein')->assertOk()->getContent();

        $this->assertStringContainsString(
            '<strong class="font-semibold text-ink">KE!N EINZELFALL e.V.</strong> wurde 2024 aus persönlicher Betroffenheit', $html);
        $this->assertStringContainsString('während der Blick auf vorhandene Ressourcen und Stärken verloren geht.', $html);
        $this->assertStringNotContainsString('Unsere Aufgabe: Wir schaffen', $html);
        // Alle sechs Absätze offen, nichts hinter „Weiterlesen“.
        $this->assertStringNotContainsString('weitere Absätze', $html);
        $this->assertMatchesRegularExpression(
            '#<p class="[^"]*font-hand[^"]*">Für Sichtbarkeit\. Für eine Stimme\. Für Unterstützung\. Gemeinsam KE!N EINZELFALL\.</p>#', $html);
    }

    /** KEV-173: Bestehende Seiten bekommen den Text per Migration, Englisch mit. */
    public function test_migration_setzt_den_neuen_vereinstext(): void
    {
        $block = Page::where('slug', 'verein')->where('locale', 'de')->first()->blocks()->where('typ', 'text')->first();
        $block->update(['data' => ['absaetze' => VereinsseiteSeeder::ALTE_ABSAETZE]]);

        $migration = require database_path('migrations/2026_10_26_130000_verein_neuer_text.php');
        $migration->up();
        $migration->up();

        $data = $block->fresh()->data;
        $this->assertSame(VereinsseiteSeeder::ABSAETZE, $data['absaetze']);
        $this->assertSame(VereinsseiteSeeder::HAND, $data['hand']);
        $this->assertTrue($data['alles_zeigen']);

        $woerterbuch = json_decode(file_get_contents(database_path('seeders/data/uebersetzungen.json')), true);
        foreach ([...VereinsseiteSeeder::ABSAETZE, VereinsseiteSeeder::HAND] as $text) {
            $this->assertArrayHasKey($text, $woerterbuch);
        }

        // Ein im Panel geänderter Text bleibt.
        $block->update(['data' => ['absaetze' => ['Eigener Text.']]]);
        $migration->up();
        $this->assertSame(['Eigener Text.'], $block->fresh()->data['absaetze']);
    }
}
