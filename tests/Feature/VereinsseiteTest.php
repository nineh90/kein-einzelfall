<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\AltseiteSeeder;
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
}
