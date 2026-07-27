<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Unterseiten bestanden anfangs nur aus einer Überschrift und gleich
 * aussehenden Textblöcken — im Vergleich zur Startseite wirkten sie unfertig.
 * Diese Tests halten fest, was dagegen eingebaut wurde.
 */
class SeitengestaltungTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_unterseiten_haben_einen_seitenkopf_mit_brotkrumen(): void
    {
        $html = $this->get('/satzung')->getContent();

        $this->assertStringContainsString('aria-label="Sie sind hier"', $html);
        // Bereichsangabe über der Überschrift, abgeleitet aus der Navigation
        $this->assertStringContainsString('Verein', $html);
    }

    public function test_brotkrumen_markieren_die_aktuelle_seite_und_verlinken_sie_nicht(): void
    {
        $html = $this->get('/satzung')->getContent();

        preg_match('/aria-label="Sie sind hier".*?<\/nav>/s', $html, $treffer);
        $krumen = $treffer[0];

        $this->assertStringContainsString('aria-current="page"', $krumen);
        $this->assertStringContainsString('href="/"', $krumen);
    }

    public function test_aufeinanderfolgende_textbausteine_wechseln_die_flaeche(): void
    {
        // Ohne Wechsel laufen zehn Abschnitte optisch ununterscheidbar ineinander.
        $html = $this->get('/datenschutz')->getContent();
        $inhalt = preg_match('/<main[^>]*>(.*?)<\/main>/s', $html, $m) ? $m[1] : '';

        preg_match_all('/<section[^>]*class="([^"]*)"/', $inhalt, $treffer);
        $flaechen = array_map(
            fn ($k) => str_contains($k, 'bg-card') ? 'card' : 'cream',
            $treffer[1]
        );

        $this->assertGreaterThan(3, count($flaechen));
        $this->assertContains('card', $flaechen);
        $this->assertContains('cream', $flaechen);
    }

    public function test_seiten_fuehren_zu_verwandten_seiten_statt_in_eine_sackgasse(): void
    {
        $this->get('/satzung')
            ->assertSee('weiterlesen-titel', false)
            ->assertSee('Mitgliedschaft');
    }

    public function test_bereichsuebersichten_fuehren_zu_ihren_unterseiten(): void
    {
        // /verein ist selbst ein Bereich — dort sollen die Unterseiten stehen,
        // nicht die Geschwister.
        $this->get('/verein')
            ->assertSee('Satzung')
            ->assertSee('Mitgliedschaft');
    }

    public function test_rechtstexte_bekommen_keinen_kontakt_aufruf(): void
    {
        // „Fragen zu diesem Thema?" unter einer Datenschutzerklärung wäre
        // deplatziert.
        $this->get('/datenschutz')->assertDontSee('Fragen zu diesem Thema');
        $this->get('/impressum')->assertDontSee('Fragen zu diesem Thema');

        // Inhaltsseiten dagegen schon
        $this->get('/erwerbsminderungsrente')->assertSee('Fragen zu diesem Thema');
    }

    public function test_erster_absatz_wird_zum_vorspann_ohne_verloren_zu_gehen(): void
    {
        $seite = Page::where('slug', 'verein')->first();

        // Der Text des ersten Absatzes muss weiterhin auf der Seite stehen —
        // er wandert nur in den Kopf, er verschwindet nicht.
        $ersterAbsatz = $seite->blocks->first()->data['absaetze'][0] ?? null;
        $this->assertNotNull($ersterAbsatz);

        $this->get('/verein')->assertSee(mb_substr($ersterAbsatz, 0, 60), false);
    }

    public function test_jede_seite_hat_weiterhin_genau_eine_h1(): void
    {
        // Der Seitenkopf bringt eine eigene Überschrift mit — es darf keine
        // zweite dazukommen.
        foreach (['verein', 'satzung', 'datenschutz', 'wissen'] as $slug) {
            $html = $this->get("/{$slug}")->getContent();

            $this->assertSame(1, preg_match_all('/<h1[^>]*>/', $html), "Seite /{$slug}");
        }
    }
}
