<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hält zwei Fehler fest, die beim ersten Start auf einem fremden Rechner
 * aufgetreten sind: Die Seite lud ohne Inhalte und ohne Gestaltung, und das
 * Einstellungs-Panel stand dabei dauerhaft offen, ohne bedienbar zu sein.
 */
class StartbarkeitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Das Panel darf weder von der Stilvorlage allein abhängen noch durch die
     * Absicherung unbedienbar werden.
     *
     * Erst fehlte die Absicherung: Ohne gebaute Assets griff [x-cloak] nicht,
     * das Panel stand offen und liess sich mangels Alpine nicht schliessen.
     * Dann kollidierte ein zusätzliches style="display:none" mit x-show —
     * Alpine verwaltete denselben Inline-Stil, der Knopf tat nichts mehr.
     *
     * Lösung: das HTML-Attribut `hidden`. Es wirkt auch ohne Stilvorlage, und
     * Alpine setzt es über :hidden, statt am display herumzuschreiben.
     */
    public function test_einstellungs_panel_ist_zu_und_bleibt_bedienbar(): void
    {
        $html = $this->get('/')->getContent();

        preg_match('/<div id="a11y-panel"(.*?)>/s', $html, $treffer);
        $panel = $treffer[1] ?? '';

        $this->assertStringContainsString('hidden', $panel, 'Panel wäre ohne Stilvorlage offen');
        $this->assertStringContainsString(':hidden=', $panel, 'Alpine kann das Panel nicht öffnen');

        // Genau das hat den Knopf lahmgelegt:
        $this->assertStringNotContainsString(
            'style="display:none"',
            $panel,
            'Inline-display kollidiert mit Alpine — der Knopf reagiert dann nicht'
        );
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

    public function test_startskript_legt_ein_verwaltungskonto_an(): void
    {
        // Das Konto wurde früher von Hand erzeugt und existierte damit auf
        // genau einem Rechner — während das README behauptete, es gäbe eines.
        $this->assertStringContainsString('AdminSeeder', file_get_contents(base_path('bin/start')));
    }

    public function test_verwaltungskonto_wird_angelegt_und_ist_freigeschaltet(): void
    {
        $this->seed(\Database\Seeders\AdminSeeder::class);

        $konto = \App\Models\User::where('email', \Database\Seeders\AdminSeeder::EMAIL)->first();

        $this->assertNotNull($konto);
        $this->assertTrue($konto->panel_zugang);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check(
            \Database\Seeders\AdminSeeder::PASSWORT,
            $konto->password
        ));

        $this->actingAs($konto)->get('/admin')->assertOk();
    }

    public function test_verwaltungskonto_wird_nicht_doppelt_angelegt(): void
    {
        // Sonst käme bei jedem Start ein weiteres Konto dazu.
        $this->seed(\Database\Seeders\AdminSeeder::class);
        $this->seed(\Database\Seeders\AdminSeeder::class);

        $this->assertSame(1, \App\Models\User::where('panel_zugang', true)->count());
    }

    public function test_readme_nennt_die_zugangsdaten_die_wirklich_angelegt_werden(): void
    {
        // Genau hier lag der Fehler: Das README nannte ein Konto, das nur in
        // einer bestimmten Datenbank existierte.
        $readme = file_get_contents(base_path('README.md'));

        $this->assertStringContainsString(\Database\Seeders\AdminSeeder::EMAIL, $readme);
        $this->assertStringContainsString(\Database\Seeders\AdminSeeder::PASSWORT, $readme);
    }

    public function test_alle_verlinkten_seiten_der_navigation_sind_erreichbar(): void
    {
        // Fängt tote Verweise in der Navigation ab — genau das war das
        // auffälligste Symptom.
        $this->seed(\Database\Seeders\AltseiteSeeder::class);

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
