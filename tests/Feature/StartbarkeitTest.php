<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\StartseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Hält zwei Fehler fest, die beim ersten Start auf einem fremden Rechner
 * aufgetreten sind: Die Seite lud ohne Inhalte und ohne Gestaltung, und das
 * Einstellungs-Panel stand dabei dauerhaft offen, ohne bedienbar zu sein.
 */
class StartbarkeitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Die Startseite ist ein Datensatz. Ohne ihn liefert `/` ein 404 und
        // dieser Test prüfte die Fehlerseite statt der Startseite.
        $this->seed(StartseiteSeeder::class);
    }

    /**
     * Das Panel startet zugeklappt und hängt dabei nicht an der Stilvorlage.
     *
     * Zwei Fehlversuche stecken darin: Ohne Absicherung stand das Panel bei
     * fehlenden Assets offen und liess sich nicht schliessen; ein zusätzliches
     * style="display:none" legte anschliessend den Knopf lahm. Geblieben ist
     * das HTML-Attribut `hidden` — der Browser versteht es auch ohne CSS.
     */
    public function test_einstellungs_panel_ist_zu_und_bleibt_bedienbar(): void
    {
        $html = $this->get('/')->getContent();

        preg_match('/<div id="a11y-panel"(.*?)>/s', $html, $treffer);
        $panel = $treffer[1] ?? '';

        $this->assertStringContainsString('hidden', $panel, 'Panel wäre ohne Stilvorlage offen');

        $this->assertStringNotContainsString(
            'style="display:none"',
            $panel,
            'Inline-display kollidiert mit dem Umschalten — der Knopf reagiert dann nicht'
        );

        // Der Knopf muss das Panel benennen, sonst weiss ein Screenreader nicht,
        // was sich da öffnet.
        $this->assertMatchesRegularExpression('/<button[^>]*aria-controls="a11y-panel"/s', $html);
    }

    /**
     * Der Fehler, der dreimal gemeldet wurde: Der Knopf neben dem Notausgang
     * liess sich nicht drücken — ohne Fehlermeldung, ohne sichtbare Ursache.
     *
     * Grund war Alpine. Es wertet den Inhalt von @click, x-show, x-text … zur
     * Laufzeit über new Function() aus. Unsere CSP erlaubt kein 'unsafe-eval',
     * also blockierte der Browser jede dieser Auswertungen. Am HTML war davon
     * nichts zu erkennen; gemeldet wurde es nur in der Entwicklerkonsole.
     *
     * Entweder ohne solche Attribute arbeiten oder die CSP öffnen — dieser Test
     * hält fest, dass wir uns für Ersteres entschieden haben.
     */
    public function test_keine_bedienung_haengt_an_zur_laufzeit_ausgewertetem_quelltext(): void
    {
        foreach (['/', '/verein', '/aktuelles', '/veranstaltungen'] as $pfad) {
            $html = $this->get($pfad)->getContent();

            // Skript- und Stilblöcke raus: Dort ist Quelltext erlaubt und
            // erwartet, geprüft werden nur die HTML-Attribute.
            $markup = preg_replace('/<(script|style)\b.*?<\/\1>/is', '', $html);

            preg_match_all('/\s(x-[a-z:._-]+|@[a-z]+|:[a-z-]+)=/i', $markup, $treffer);
            $gefunden = array_values(array_unique($treffer[1]));

            $this->assertSame([], $gefunden,
                "Auswertbare Attribute auf {$pfad}: ".implode(', ', $gefunden)
                ." — die CSP verbietet 'unsafe-eval', solche Ausdrücke laufen im Browser nie.");
        }
    }

    public function test_die_csp_erlaubt_weiterhin_kein_unsafe_eval(): void
    {
        // Die naheliegende „Lösung" für den toten Knopf wäre gewesen, hier
        // 'unsafe-eval' einzutragen. Bei einer Seite, auf der Menschen über
        // erlebte Straftaten schreiben, ist das der falsche Handel.
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringNotContainsString('unsafe-eval', $csp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);
    }

    /**
     * Das Panel wurde früher per JavaScript aus einer Liste im Bundle
     * zusammengebaut. Vor dem Ausführen des Skripts war es damit gar nicht
     * vorhanden — ausgerechnet bei den Barrierefreiheits-Einstellungen.
     */
    public function test_die_darstellungs_optionen_stehen_im_ausgelieferten_html(): void
    {
        $html = $this->get('/')->getContent();

        foreach (config('darstellung.optionen') as $schluessel => $opt) {
            // e(): „Kontrast & Farben" steht im HTML als „Kontrast &amp; Farben".
            $this->assertStringContainsString(e($opt['label']), $html, "Beschriftung „{$opt['label']}\" fehlt");

            foreach ($opt['werte'] ?? [] as $eintrag) {
                $this->assertMatchesRegularExpression(
                    '/data-a11y-setzen="'.$schluessel.'"\s+data-a11y-wert="'.preg_quote($eintrag['wert'], '/').'"/',
                    $html,
                    "Schaltfläche {$schluessel}={$eintrag['wert']} fehlt"
                );
            }
        }
    }

    public function test_mobilmenue_kommt_ohne_javascript_aus(): void
    {
        // Erster Versuch war ein per JavaScript umgeschaltetes `hidden` plus
        // eine <noscript>-Regel, die es wieder aufhebt. Die griff nicht:
        // Tailwinds [hidden]-Regel liegt in einem CSS-Layer, und bei
        // !important gewinnt die Layer-Regel gegen eine ungelayerte — die
        // Navigation blieb ohne JavaScript unerreichbar.
        //
        // Jetzt natives <details> wie überall sonst im Projekt. Kein Zustand,
        // der davon abhängt, ob ein Bundle geladen hat.
        $html = $this->get('/')->getContent();

        $this->assertMatchesRegularExpression(
            '/<details[^>]*>\s*<summary.*?Hauptnavigation \(mobil\)/s',
            $html,
            'Mobil-Navigation hängt nicht mehr an <details>'
        );

        $this->assertStringNotContainsString('<noscript>', $html,
            'Wenn es ohne JavaScript funktioniert, braucht es keine Ausnahmeregel');
    }

    public function test_startskript_pflegt_inhalte_ein_wenn_die_datenbank_leer_ist(): void
    {
        // Ohne diesen Schritt startet die Seite mit leeren Tabellen und
        // sämtliche Links laufen ins Leere.
        $skript = file_get_contents(base_path('bin/start'));

        $this->assertStringContainsString('AltseiteSeeder', $skript);
    }

    public function test_startskript_bricht_bei_fehlgeschlagenem_build_ab(): void
    {
        // Vorher wurde der Fehler mit "|| true" geschluckt: Die Seite startete,
        // sah aber kaputt aus, ohne dass die Ursache erkennbar war.
        $skript = file_get_contents(base_path('bin/start'));

        $this->assertStringContainsString('PIPESTATUS', $skript);
        $this->assertStringContainsString('manifest.json', $skript);
    }

    public function test_startskript_fuehrt_ausstehende_migrationen_hart_aus(): void
    {
        // Vorher lief das Skript bei einem Migrationsfehler einfach weiter.
        // Ergebnis: Der Server startete, und Seiten brachen mit einem
        // SQL-Fehler ab („Table 'groups' doesn't exist").
        $skript = file_get_contents(base_path('bin/start'));

        $this->assertStringContainsString('migrate:status', $skript);
        $this->assertMatchesRegularExpression(
            '/if ! php artisan migrate --force -q; then.*?exit 1/s',
            $skript,
            'Migrationsfehler müssen den Start abbrechen'
        );
    }

    public function test_benoetigte_php_erweiterungen_sind_dokumentiert(): void
    {
        // Ohne intl wirft Filament beim Öffnen der Seitenliste:
        // „The intl PHP extension is required to use the [format] method."
        // Ohne Eintrag in composer.json fällt das erst im Betrieb auf.
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $this->assertArrayHasKey('ext-intl', $composer['require']);
        $this->assertArrayHasKey('ext-mbstring', $composer['require']);

        // zip braucht nur Composer, nicht die laufende Anwendung. Es zur
        // Startbedingung zu machen hiesse, unnötig Pakete zu verlangen.
        $this->assertArrayNotHasKey('ext-zip', $composer['require']);

        // Laravel 12 ist auf PHP 8.2 bis 8.4 getestet — 8.5 nicht
        // stillschweigend zulassen.
        $this->assertStringContainsString('<8.6', $composer['require']['php']);
    }

    public function test_startskript_prueft_die_php_erweiterungen(): void
    {
        // Lieber beim Start abbrechen als später an unerwarteter Stelle.
        $skript = file_get_contents(base_path('bin/start'));

        $this->assertStringContainsString('intl', $skript);
        $this->assertStringContainsString('php -m', $skript);
        $this->assertStringContainsString('dnf install', $skript, 'Hinweis für Fedora fehlt');

        // Auf Nobara scheitert dnf gern an fehlenden Signaturschlüsseln —
        // ohne Hinweis steht man davor und weiss nicht weiter.
        $this->assertStringContainsString('fedora.gpg', $skript);
    }

    public function test_startskript_legt_ein_verwaltungskonto_an(): void
    {
        // Das Konto wurde früher von Hand erzeugt und existierte damit auf
        // genau einem Rechner — während das README behauptete, es gäbe eines.
        $this->assertStringContainsString('AdminSeeder', file_get_contents(base_path('bin/start')));
    }

    public function test_verwaltungskonto_wird_angelegt_und_ist_freigeschaltet(): void
    {
        $this->seed(AdminSeeder::class);

        $konto = User::where('email', AdminSeeder::EMAIL)->first();

        $this->assertNotNull($konto);
        $this->assertTrue($konto->panel_zugang);
        $this->assertTrue(Hash::check(
            AdminSeeder::PASSWORT,
            $konto->password
        ));

        $this->actingAs($konto)->get('/admin')->assertOk();
    }

    public function test_verwaltungskonto_wird_nicht_doppelt_angelegt(): void
    {
        // Sonst käme bei jedem Start ein weiteres Konto dazu.
        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->assertSame(1, User::where('panel_zugang', true)->count());
    }

    public function test_readme_nennt_die_zugangsdaten_die_wirklich_angelegt_werden(): void
    {
        // Genau hier lag der Fehler: Das README nannte ein Konto, das nur in
        // einer bestimmten Datenbank existierte.
        $readme = file_get_contents(base_path('README.md'));

        $this->assertStringContainsString(AdminSeeder::EMAIL, $readme);
        $this->assertStringContainsString(AdminSeeder::PASSWORT, $readme);
    }

    public function test_alle_verlinkten_seiten_der_navigation_sind_erreichbar(): void
    {
        // Fängt tote Verweise in der Navigation ab — genau das war das
        // auffälligste Symptom.
        $this->seed(AltseiteSeeder::class);

        $ziele = collect(config('navigation.main'))
            ->flatMap(fn ($p) => array_merge([$p['url']], array_column($p['children'] ?? [], 'url')))
            ->merge(array_column(config('navigation.footer.informationen'), 'url'))
            ->merge(array_column(config('navigation.mobile_bar'), 'url'))
            ->unique()
            ->filter(fn ($u) => str_starts_with($u, '/'));

        $tot = [];

        foreach ($ziele as $url) {
            $status = $this->get($url)->getStatusCode();

            if ($status !== 200) {
                $tot[] = "{$url} ({$status})";
            }
        }

        $this->assertEmpty($tot, 'Tote Verweise in der Navigation: '.implode(', ', $tot));
    }
}
