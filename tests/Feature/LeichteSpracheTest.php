<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Rules\KollidiertNichtMitSprachpraefix;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Leichte Sprache ist eine eigene Fassung einer Seite, keine eigene Sprache.
 *
 * Die Entscheidung ist die ganze Sache: Sie *ist* Deutsch (lang bleibt „de“,
 * kein hreflang), bekommt aber eine eigene Adresse — nur so ist sie
 * verlinkbar, als Lesezeichen speicherbar und auffindbar, wie es BITV 2.0 § 4
 * verlangt. Die Begründung im Langen steht in der Migration
 * `add_fassung_to_pages_table`.
 */
class LeichteSpracheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    /** Legt zur Seite $slug eine Fassung in Leichter Sprache an. */
    private function leichteFassung(string $slug): Page
    {
        $schwer = Page::where('locale', 'de')
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->where('slug', $slug)
            ->firstOrFail();

        return Page::create([
            'locale' => 'de',
            'fassung' => Page::FASSUNG_LEICHTE_SPRACHE,
            'uebersetzungs_gruppe' => $schwer->uebersetzungs_gruppe,
            'slug' => $slug,
            'titel' => 'Der Verein — in Leichter Sprache',
            'published_at' => now(),
        ]);
    }

    public function test_leichte_sprache_hat_eine_eigene_adresse(): void
    {
        $this->leichteFassung('verein');

        // Eigene Adresse, kein aufklappbarer Kasten irgendwo auf der Seite.
        $this->get('/leichte-sprache/verein')
            ->assertOk()
            ->assertSee('in Leichter Sprache');
    }

    public function test_schwere_und_leichte_fassung_teilen_den_slug(): void
    {
        $this->leichteFassung('verein');

        // /verein und /leichte-sprache/verein sind zwei Seiten mit demselben
        // Slug. Der frühere Unique-Index auf (locale, slug) hätte das verhindert.
        $this->get('/verein')->assertOk();
        $this->get('/leichte-sprache/verein')->assertOk();

        $this->assertSame(2, Page::where('slug', 'verein')->count());
    }

    public function test_leichte_sprache_bleibt_deutsch(): void
    {
        $this->leichteFassung('verein');

        // Kein abweichendes lang-Attribut: Leichte Sprache *ist* Deutsch. Ein
        // Wechsel würde eine Vorlesehilfe zu falscher Aussprache verleiten.
        $antwort = $this->get('/leichte-sprache/verein')->assertOk();

        $antwort->assertSee('<html lang="de" dir="ltr">', false);
        // Und der Inhaltsbereich trägt die Fassungs-Kennung für die Typografie.
        $antwort->assertSee('data-fassung="leichte-sprache"', false);
    }

    public function test_leichte_sprache_bekommt_kein_hreflang(): void
    {
        $this->leichteFassung('verein');

        // de-x-leicht ist ein Private-Use-Subtag; Google meldet ihn als Fehler
        // statt ihn zu verstehen. Also gar kein hreflang auf dieser Fassung.
        $this->get('/leichte-sprache/verein')
            ->assertOk()
            ->assertDontSee('hreflang', false);
    }

    public function test_beide_fassungen_verweisen_aufeinander(): void
    {
        $this->leichteFassung('verein');

        // Von der schweren Fassung führt ein sichtbarer Weg zur leichten …
        $this->get('/verein')
            ->assertOk()
            ->assertSee('Diese Seite in Leichter Sprache')
            ->assertSee('/leichte-sprache/verein');

        // … und zurück. Der Link ist ein relativer Pfad, keine absolute URL.
        $this->get('/leichte-sprache/verein')
            ->assertOk()
            ->assertSee('Diese Seite in Alltags-Sprache')
            ->assertSee('href="/verein"', false);
    }

    public function test_ohne_leichte_fassung_kein_hinweis(): void
    {
        // Kein Angebot vortäuschen, das es nicht gibt.
        $this->get('/verein')
            ->assertOk()
            ->assertDontSee('Diese Seite in Leichter Sprache');
    }

    public function test_fehlende_leichte_fassung_ist_ein_404(): void
    {
        // Kein Rückfall auf den schweren Text: Wer Leichte Sprache braucht,
        // dem hilft der schwere Text nicht. Ein ehrliches 404 mit den Auswegen
        // der Fehlerseite ist besser.
        $this->get('/leichte-sprache/verein')->assertNotFound();
    }

    public function test_leichte_sprache_steht_in_der_sitemap(): void
    {
        $this->leichteFassung('verein');

        $antwort = $this->get('/sitemap.xml')->assertOk();

        // Sie soll gefunden werden …
        $antwort->assertSee(url('/leichte-sprache/verein'));
    }

    public function test_slug_darf_nicht_das_fassungspraefix_belegen(): void
    {
        // „leichte-sprache“ als Seiten-Slug wäre von der Route verdeckt.
        $rule = KollidiertNichtMitSprachpraefix::fuerSeitenSlug();

        $fehler = null;
        $rule->validate('slug', 'leichte-sprache', function ($nachricht) use (&$fehler) {
            $fehler = $nachricht;
        });

        $this->assertNotNull($fehler);
    }
}
