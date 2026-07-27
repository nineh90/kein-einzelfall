<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Redirect;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sichert die SEO-Migration ab — der Kunde hat ausdrücklich zugesagt bekommen,
 * dass die Seite "nicht schlechter dasteht als jetzt".
 *
 * Hinweis: Diese Tests laufen über den Laravel-Kernel. `php artisan serve`
 * entfernt abschließende Schrägstriche selbst, bevor Laravel sie sieht — dort
 * lässt sich das Verhalten also gar nicht prüfen. Unter Apache/nginx kommt der
 * Pfad unverändert an, deshalb ist der Test hier die verlässliche Quelle.
 */
class SeitenUndRedirectsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_alle_seiten_der_altseite_sind_erreichbar(): void
    {
        $this->assertSame(23, Page::count());

        foreach (Page::pluck('slug') as $slug) {
            $this->get("/{$slug}")->assertOk();
        }
    }

    public function test_wordpress_urls_mit_schraegstrich_leiten_dauerhaft_um(): void
    {
        // Die Altseite veröffentlicht ausschließlich Adressen mit Schrägstrich.
        // Ohne diese Umleitung wäre jeder indexierte Treffer doppelter Inhalt.
        //
        // Die Middleware wird hier direkt geprüft, weil sich der Fall über die
        // üblichen Test-Helfer nicht abbilden lässt: $this->get('/verein/') trimmt
        // den Schrägstrich schon in prepareUrlForRequest(), und `php artisan serve`
        // entfernt ihn ebenfalls. Unter Apache/nginx kommt er dagegen an.
        $middleware = new \App\Http\Middleware\SchraegstrichEntfernen;

        foreach (['/verein/' => '/verein', '/spenden/' => '/spenden'] as $von => $nach) {
            $antwort = $middleware->handle(
                \Illuminate\Http\Request::create($von, 'GET'),
                fn () => response('sollte nicht durchlaufen')
            );

            $this->assertSame(301, $antwort->getStatusCode(), "für {$von}");
            $this->assertStringEndsWith($nach, $antwort->headers->get('Location'));
        }
    }

    public function test_schraegstrich_umleitung_erhaelt_query_parameter_und_laesst_startseite_in_ruhe(): void
    {
        $middleware = new \App\Http\Middleware\SchraegstrichEntfernen;

        // Filter- und Suchparameter dürfen nicht verloren gehen
        $mitParametern = $middleware->handle(
            \Illuminate\Http\Request::create('/wissen/?kategorie=recht&seite=2', 'GET'),
            fn () => response('x')
        );
        $this->assertSame(301, $mitParametern->getStatusCode());
        $this->assertStringEndsWith('/wissen?kategorie=recht&seite=2', $mitParametern->headers->get('Location'));

        // Die Startseite ist "/" — die darf nicht zu "" werden
        $start = $middleware->handle(
            \Illuminate\Http\Request::create('/', 'GET'),
            fn () => response('durchgelaufen')
        );
        $this->assertSame('durchgelaufen', $start->getContent());
    }

    public function test_impressum_slug_wird_bereinigt(): void
    {
        // "impressum-2" ist ein WordPress-Unfall, den wir nicht mitschleppen.
        $this->get('/impressum-2')->assertRedirect('/impressum')->assertStatus(301);
        $this->get('/impressum')->assertOk();
    }

    public function test_auf_der_altseite_kaputte_links_werden_gerettet(): void
    {
        // Beide liefern auf kein-einzelfall.de einen 404, können aber extern
        // verlinkt oder gebookmarkt sein.
        $this->get('/selbsthilfegruppen-2')->assertRedirect('/selbsthilfegruppen');
        $this->get('/kontaktformular')->assertRedirect('/anfragen');
    }

    public function test_unbekannte_adressen_liefern_weiterhin_404(): void
    {
        $this->get('/gibt-es-nicht')->assertNotFound();
    }

    public function test_seiten_uebernehmen_die_metadaten_der_altseite(): void
    {
        $verein = Page::where('slug', 'verein')->first();

        $this->assertNotEmpty($verein->meta_description);
        $this->assertStringContainsString('Kein Einzelfall e.V.', $verein->meta_title);

        // Kanonische Adresse auf jeder Seite — hat die Altseite auch.
        $this->get('/verein')->assertSee('<link rel="canonical"', false);
    }

    public function test_jede_seite_hat_genau_eine_h1(): void
    {
        foreach (Page::pluck('slug') as $slug) {
            $html = $this->get("/{$slug}")->getContent();
            $this->assertSame(
                1,
                preg_match_all('/<h1[^>]*>/', $html),
                "Seite /{$slug} hat nicht genau eine h1"
            );
        }
    }

    public function test_inhalte_stammen_woertlich_von_der_altseite(): void
    {
        // Stichproben aus verschiedenen Seiten — nichts davon ist von uns formuliert.
        $this->get('/verein')->assertSee('aus einer persönlichen Betroffenheit heraus', false);
        $this->get('/wissen')->assertOk();
        $this->get('/erwerbsminderungsrente')->assertSee('Infoblatt', false);
    }

    public function test_dokumente_werden_als_downloadliste_ausgegeben(): void
    {
        // /erwerbsminderungsrente/ hat 12 verlinkte PDFs.
        $html = $this->get('/erwerbsminderungsrente')->getContent();

        $this->assertStringContainsString('PDF-Datei', $html);
        $this->assertGreaterThanOrEqual(12, substr_count($html, 'download'));
    }

    public function test_lange_seiten_bekommen_sprungmarken_kurze_nicht(): void
    {
        // 1.600 Wörter Datenschutzerklärung ohne Inhaltsverzeichnis sind für
        // Menschen mit Konzentrationsschwierigkeiten praktisch unbenutzbar.
        $lang = $this->get('/datenschutz')->getContent();
        $this->assertStringContainsString('<nav aria-label="Auf dieser Seite"', $lang);
        $this->assertGreaterThanOrEqual(4, substr_count($lang, 'href="#abschnitt-'));

        // Jede Sprungmarke braucht ihr Ziel, sonst führt der Link ins Leere.
        preg_match_all('/href="#(abschnitt-[a-z0-9-]+)"/', $lang, $ziele);
        foreach (array_unique($ziele[1]) as $anker) {
            $this->assertStringContainsString('id="'.$anker.'"', $lang);
        }

        // Kurze Seiten bleiben ohne — Inhaltsverzeichnis wäre hier nur Ballast.
        $this->assertStringNotContainsString(
            '<nav aria-label="Auf dieser Seite"',
            $this->get('/wissen')->getContent()
        );
    }

    public function test_weiterleitungen_zaehlen_ihre_treffer(): void
    {
        // Nach dem Go-Live wollen wir sehen, welche Regeln tatsächlich greifen.
        $this->get('/impressum-2');

        $this->assertSame(1, Redirect::where('von', 'impressum-2')->first()->treffer);
    }
}
