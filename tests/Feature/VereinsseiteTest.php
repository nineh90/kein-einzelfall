<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UnterstuetzungsbandSeeder;
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

    /** KEV-174: Selbsthilfe- und Arbeitsgruppen mit Bild rechts neben dem Text. */
    public function test_gruppen_stehen_als_karten_neben_dem_text(): void
    {
        foreach (['selbsthilfegruppen', 'arbeitsgruppen'] as $slug) {
            Page::where('slug', $slug)->update(['titelbild' => Titelbilder::datei($slug), 'published_at' => now()]);
        }

        $html = $this->get('/verein')->assertOk()->getContent();

        // Im selben Abschnitt wie der Text, nach ihm: Text links, Karten rechts.
        $abschnitt = substr($html, strpos($html, 'wurde 2024 aus persönlicher Betroffenheit'));
        $abschnitt = substr($abschnitt, 0, strpos($abschnitt, '</section>'));
        $this->assertMatchesRegularExpression('#<a href="/selbsthilfegruppen"[^>]*>.*?selbsthilfegruppen[^"]*\.webp.*?Selbsthilfegruppen#s', $abschnitt);
        $this->assertMatchesRegularExpression('#<a href="/arbeitsgruppen"[^>]*>.*?arbeitsgruppen[^"]*\.webp.*?Arbeitsgruppen#s', $abschnitt);
    }

    /** KEV-174: Die Migration hängt die Karten genau einmal hinter den Text. */
    public function test_migration_haengt_die_karten_einmal_an(): void
    {
        $seite = Page::where('slug', 'verein')->where('locale', 'de')->first();
        $seite->blocks()->where('typ', 'seitenkarten')->delete();

        $migration = require database_path('migrations/2026_10_26_140000_verein_gruppen_daneben.php');
        $migration->up();
        $migration->up();

        $typen = $seite->blocks()->orderBy('position')->pluck('typ')->all();
        $this->assertSame(1, count(array_keys($typen, 'seitenkarten', true)));
        $this->assertSame('seitenkarten', $typen[array_search('text', $typen, true) + 1]);
        $this->assertSame(VereinsseiteSeeder::KARTEN, $seite->blocks()->where('typ', 'seitenkarten')->value('data'));
    }

    /** KEV-175: statt des grünen Bands der Spendenkasten der Startseite, ohne Strich. */
    public function test_spendenkasten_statt_gruenem_band(): void
    {
        $seite = Page::where('slug', 'verein')->where('locale', 'de')->first();
        $this->assertSame(['text', 'seitenkarten', 'donation_options'], $seite->blocks()->orderBy('position')->pluck('typ')->all());

        $html = $this->get('/verein')->assertOk()->getContent();
        $this->assertStringContainsString('IBAN kopieren', $html);
        // Ohne Kopf: Im Handy-Menü steht derselbe Satz als Unterzeile von „Spenden“.
        $this->assertStringNotContainsString('Sei Du dabei, jede Unterstützung zählt', substr($html, strpos($html, '<main')));

        $abschnitt = substr($html, strpos($html, 'id="spenden"'));
        $abschnitt = substr($abschnitt, 0, strpos($abschnitt, '</section>'));
        $this->assertStringNotContainsString('bg-green-brand', $abschnitt);

        // Stand davor: das Band am Ende. Die Migration tauscht es an derselben Stelle.
        $seite->blocks()->where('typ', 'donation_options')->delete();
        $seite->blocks()->create(['typ' => 'cta_band', 'position' => 2, 'data' => UnterstuetzungsbandSeeder::BAND]);
        $migration = require database_path('migrations/2026_10_26_150000_verein_spendenkasten.php');
        $migration->up();
        $migration->up();
        $this->assertSame(['text', 'seitenkarten', 'donation_options'], $seite->blocks()->orderBy('position')->pluck('typ')->all());

        // Ein neuer Lauf des Bands hängt keins mehr an.
        UnterstuetzungsbandSeeder::anhaengen();
        $this->assertFalse($seite->blocks()->where('typ', 'cta_band')->exists());
    }

    /**
     * KEV-176: Die Kacheln „Mehr über …“ als 3 × 3, spaltenweise in Taddis
     * Reihenfolge, ohne Gremium UKFB.
     */
    public function test_kacheln_in_taddis_reihenfolge_ohne_ukfb(): void
    {
        $html = $this->get('/verein')->assertOk()->getContent();
        $aside = substr($html, strpos($html, 'id="weiterlesen-titel"'));
        $aside = substr($aside, 0, strpos($aside, '</aside>'));

        preg_match_all('#<a href="(/[^"]+)"#', $aside, $treffer);
        $this->assertSame([
            '/ueber-uns-vorstand-und-team', '/satzung', '/mitgliedschaft',
            '/kinderkodex', '/istanbul-konvention', '/schutz-und-wertekonzept',
            '/red-flags', '/beschwerdemanagement', '/taetigkeits-und-jahresberichte',
        ], $treffer[1]);
        $this->assertStringContainsString('lg:grid-flow-col lg:grid-rows-3', $aside);

        // Auch auf den Unterseiten keine Kachel zum Gremium.
        $html = $this->get('/satzung')->assertOk()->getContent();
        $aside = substr($html, strpos($html, 'id="weiterlesen-titel"'));
        $aside = substr($aside, 0, strpos($aside, '</aside>'));
        $this->assertStringContainsString('href="/mitgliedschaft"', $aside);
        $this->assertStringNotContainsString('/gremium-ukfb', $aside);
    }

    /** KEV-178: Taddis Reihenfolge im Menü „Verein“, mit Gremium UKFB. */
    public function test_menue_verein_in_taddis_reihenfolge(): void
    {
        $verein = collect(config('navigation.main'))->firstWhere('url', '/verein');

        $this->assertSame([
            '/ueber-uns-vorstand-und-team', '/satzung', '/mitgliedschaft', '/schutz-und-wertekonzept',
            '/red-flags', '/kinderkodex', '/istanbul-konvention', '/gremium-ukfb',
            '/beschwerdemanagement', '/taetigkeits-und-jahresberichte',
        ], array_column($verein['children'], 'url'));

        $this->get('/verein')->assertOk()->assertSeeInOrder(
            ['Schutz- und Wertekonzept', 'Red Flags', 'Kinderkodex', 'Istanbul-Konvention', 'Gremium UKFB', 'Beschwerdemanagement'], false);
    }
}
