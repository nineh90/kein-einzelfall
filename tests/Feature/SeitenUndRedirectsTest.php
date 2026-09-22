<?php

namespace Tests\Feature;

use App\Http\Middleware\SchraegstrichEntfernen;
use App\Models\Page;
use App\Models\Redirect;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    public function test_gepflegter_meta_title_landet_im_seitentitel(): void
    {
        // Das Feld gibt es im Panel seit Anfang an — es hat nur nichts bewirkt.
        // Aufgefallen ist das lange nicht, weil alle 24 Altseiten zufaellig
        // genau das Suffix tragen, das das Layout ohnehin anhaengt.
        $seite = Page::where('slug', 'verein')->firstOrFail();
        $seite->update(['meta_title' => 'Der Verein KE!N EINZELFALL — Opferhilfe Hamburg']);

        $this->get('/verein')
            ->assertOk()
            ->assertSee('<title>Der Verein KE!N EINZELFALL — Opferhilfe Hamburg</title>', false);
    }

    public function test_ohne_meta_title_bleibt_das_muster_der_altseite(): void
    {
        // „%Seite% - Kein Einzelfall e.V.“ — damit sich die Suchergebnisse beim
        // Umzug nicht veraendern.
        Page::where('slug', 'verein')->update(['meta_title' => null]);

        $this->get('/verein')
            ->assertOk()
            ->assertSee('<title>Verein - Kein Einzelfall e.V.</title>', false);
    }

    public function test_seiten_ohne_vollertitel_bekommen_einen_echten_titel(): void
    {
        /*
         * Regression: Vom 30.07. bis 19.09.2026 stand auf jeder Seite, die nur
         * `title` und kein `vollertitel` setzt, wörtlich „@yield('title', …)“
         * im <title> — die Direktiven-Kette im Layout war für Blade an einer
         * Stelle unlesbar. Betroffen: Glossar, Aktuelles, Veranstaltungen,
         * Fehlerseiten. Aufgefallen ist es nur im ModuleTest, und dort an der
         * falschen Assertion.
         */
        foreach (['/glossar', '/aktuelles', '/veranstaltungen', '/gibt-es-nicht'] as $pfad) {
            $html = $this->get($pfad)->getContent();

            $this->assertStringNotContainsString('@yield', $html, "Unkompilierte Direktive auf {$pfad}");
            $this->assertMatchesRegularExpression('#<title>[^<@]+ - Kein Einzelfall e\.V\.</title>#', $html, $pfad);
        }
    }

    public function test_alle_veroeffentlichten_seiten_sind_erreichbar(): void
    {
        /*
         * 23 aus dem Altbestand, die Seite „Barrierefreiheit" (die es dort nicht
         * gab, auf die aber Footer und Einstellungs-Panel verweisen), die
         * Startseite, die inzwischen ebenfalls ein Datensatz ist, und die
         * Trigger-Warnung.
         *
         * Dazu vier Entwürfe: Schutzkonzept, Beschwerdemanagement, Projekte und
         * Publikationen stehen im Strukturpapier des Vereins, haben aber noch
         * keinen Text. Sie sind angelegt, damit sie im Panel als Arbeitsliste
         * stehen — und unveröffentlicht, damit auf der Website nichts Leeres
         * erscheint. Sie gehören deshalb in die Gesamtzahl, aber nicht in den
         * Durchlauf darunter.
         *
         * Und fünf aus Abschnitt 6.2 des Strukturpapiers (OEG, SER, GdB,
         * Pflegegrad, Persönliches Budget). Anders als die vier oben haben sie
         * Text — geltendes Recht aus amtlichen Quellen, nicht Aussagen des
         * Vereins. Sie sind deshalb veröffentlicht und müssen erreichbar sein,
         * tragen aber `ungeprueft` und `noindex`, bis der Verein sie freigibt.
         */
        $this->assertSame(35, Page::count());
        $this->assertSame(4, Page::whereNull('published_at')->count());
        $this->assertSame(5, Page::where('ungeprueft', true)->count());

        // Ungeprüfter Text gehört nicht in eine Suchmaschine: Wer ihn über
        // Google fände, käme ohne den Vermerk an und läse ihn als verbindlich.
        $this->assertSame(
            0,
            Page::where('ungeprueft', true)->where('noindex', false)->count(),
            'Eine ungepruefte Seite ist fuer Suchmaschinen freigegeben.'
        );

        // Über pfad() und nicht über den Slug: Die Startseite liegt unter „/“.
        foreach (Page::veroeffentlicht()->get() as $seite) {
            $this->get($seite->pfad())->assertOk();
        }
    }

    public function test_die_neuen_bereiche_liegen_als_entwurf_bereit(): void
    {
        /*
         * Ein Schutzkonzept ist eine Selbstverpflichtung — was darin steht,
         * muss der Verein einhalten können. Wir legen die Seite an und die
         * Struktur, den Text schreibt er selbst.
         */
        foreach (['schutzkonzept', 'beschwerdemanagement', 'projekte', 'publikationen'] as $slug) {
            $seite = Page::where('slug', $slug)->first();

            $this->assertNotNull($seite, "Die Seite '{$slug}' fehlt");
            $this->assertNull($seite->published_at, "'{$slug}' darf noch nicht veröffentlicht sein");

            // Entwürfe sind für Besucher nicht sichtbar.
            $this->get('/'.$slug)->assertNotFound();
        }
    }

    public function test_die_startseite_hat_genau_eine_adresse(): void
    {
        // Derselbe Inhalt unter „/“ und „/startseite“ wäre doppelter Inhalt —
        // genau das, was dem Kunden zu vermeiden zugesagt ist.
        $this->get('/')->assertOk();
        $this->get('/'.Page::STARTSEITE_SLUG)->assertRedirect('/')->assertStatus(301);
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
        $middleware = new SchraegstrichEntfernen;

        foreach (['/verein/' => '/verein', '/spenden/' => '/spenden'] as $von => $nach) {
            $antwort = $middleware->handle(
                Request::create($von, 'GET'),
                fn () => response('sollte nicht durchlaufen')
            );

            $this->assertSame(301, $antwort->getStatusCode(), "für {$von}");
            $this->assertStringEndsWith($nach, $antwort->headers->get('Location'));
        }
    }

    public function test_schraegstrich_umleitung_erhaelt_query_parameter_und_laesst_startseite_in_ruhe(): void
    {
        $middleware = new SchraegstrichEntfernen;

        // Filter- und Suchparameter dürfen nicht verloren gehen
        $mitParametern = $middleware->handle(
            Request::create('/wissen/?kategorie=recht&seite=2', 'GET'),
            fn () => response('x')
        );
        $this->assertSame(301, $mitParametern->getStatusCode());
        $this->assertStringEndsWith('/wissen?kategorie=recht&seite=2', $mitParametern->headers->get('Location'));

        // Die Startseite ist "/" — die darf nicht zu "" werden
        $start = $middleware->handle(
            Request::create('/', 'GET'),
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
        foreach (Page::all() as $seite) {
            $html = $this->get($seite->pfad())->getContent();
            $this->assertSame(
                1,
                preg_match_all('/<h1[^>]*>/', $html),
                "Seite {$seite->pfad()} hat nicht genau eine h1"
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

    public function test_seiten_ohne_ueberschrift_bekommen_den_ausgeschriebenen_titel(): void
    {
        /*
         * Neun Seiten der Altseite weisen keine Überschrift aus — der Importer
         * findet dort kein <h1>. Für sie hat der Seeder eine Liste
         * ausgeschriebener Titel; fehlt dort ein Eintrag, greift der Notbehelf
         * aus dem Slug, und der kennt keine Umlaute: „Ueber Uns Vorstand Und
         * Team". Vom ersten Import bis zum 22.09.2026 stand genau das auf acht
         * Seiten — als Überschrift, im Brotkrumenpfad und im Reiter des
         * Browsers.
         *
         * Geprüft wird gegen die Liste und nicht gegen Str::headline(): Bei
         * „das-hilfesystem" ist der richtige Titel zufällig wortgleich mit dem
         * Notbehelf, ein Vergleich mit ihm schlüge dort grundlos an.
         */
        $titel = (new \ReflectionClass(AltseiteSeeder::class))->getConstant('TITEL');

        foreach ($titel as $slug => $ausgeschrieben) {
            $seite = Page::where('slug', $slug)->where('locale', 'de')->first();

            if (! $seite) {
                continue;   // Slug der Altseite, den es nicht mehr gibt
            }

            $this->assertSame(
                $ausgeschrieben,
                $seite->titel,
                "Die Seite /{$slug} traegt nicht ihren ausgeschriebenen Titel."
            );
        }
    }

    public function test_der_notbehelf_aus_dem_slug_erreicht_keine_seite_mehr(): void
    {
        // Die Gegenprobe zum Test darüber: Kein Titel im Bestand sieht aus wie
        // aus dem Slug gebaut — also keiner ohne Umlaute, wo welche hingehören.
        foreach (Page::where('locale', 'de')->get() as $seite) {
            if ($seite->titel === Str::headline($seite->slug)) {
                // Zulässig, wenn der Slug den Titel wirklich hergibt
                // („spenden" → „Spenden"). Verdächtig wird es erst, wenn im
                // Slug ein umschriebener Umlaut steckt.
                $this->assertDoesNotMatchRegularExpression(
                    '/(ae|oe|ue)/i',
                    $seite->slug,
                    "Der Titel von /{$seite->slug} ist aus dem Slug gebaut und hat deshalb keine Umlaute."
                );
            }
        }
    }

    public function test_titel_aus_dem_panel_werden_nicht_ueberschrieben(): void
    {
        /*
         * Die Nachzieh-Methode läuft aus einer Migration, also auch auf einer
         * Datenbank, in der der Verein längst redigiert hat. Sie darf nur den
         * Notbehelf ersetzen — ein Titel aus dem Panel ist eine redaktionelle
         * Entscheidung und schlägt jede Liste im Code.
         */
        $seite = Page::where('slug', 'das-hilfesystem')->where('locale', 'de')->firstOrFail();
        $seite->update(['titel' => 'Wegweiser durchs Hilfesystem']);

        AltseiteSeeder::titelNachziehen();

        $this->assertSame('Wegweiser durchs Hilfesystem', $seite->fresh()->titel);
    }

    public function test_seitentitel_und_menuepunkt_sind_wortgleich(): void
    {
        /*
         * Wer im Menü „Über uns – Vorstand und Team" anklickt und dann unter
         * einer anders lautenden Überschrift landet, zweifelt, ob er richtig
         * ist. Für Menschen, die sich ohnehin schwer orientieren, ist das
         * keine Kleinigkeit — deshalb geprüft und nicht bloss angenommen.
         */
        $menue = [];
        $sammeln = function (array $eintraege) use (&$sammeln, &$menue): void {
            foreach ($eintraege as $eintrag) {
                if (! empty($eintrag['url'])) {
                    $menue[trim($eintrag['url'], '/')] = $eintrag['label'] ?? null;
                }

                $sammeln($eintrag['children'] ?? []);
            }
        };

        // exit_url ist ein einzelner Wert, kein Menuebereich.
        foreach (config('navigation') as $bereich) {
            if (is_array($bereich)) {
                $sammeln($bereich);
            }
        }

        $titel = (new \ReflectionClass(AltseiteSeeder::class))->getConstant('TITEL');

        foreach ($titel as $slug => $ausgeschrieben) {
            if (! isset($menue[$slug])) {
                continue;   // nicht jede Seite hängt im Hauptmenü
            }

            $this->assertSame(
                $menue[$slug],
                $ausgeschrieben,
                "Menuepunkt und Seitentitel von /{$slug} lauten unterschiedlich."
            );
        }
    }
}
