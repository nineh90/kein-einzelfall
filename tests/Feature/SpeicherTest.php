<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\BarrierefreiheitSeeder;
use Tests\TestCase;

/**
 * Was diese Website im Browser ablegt — und der Weg zurück.
 *
 * Der Anlass: Bis zur Trigger-Warnung gab es genau einen gespeicherten Wert,
 * und die Darstellungs-Toolbar hatte ihren eigenen Knopf dafür. Mit dem zweiten
 * Wert gab es keinen Weg mehr zurück — wer „Hinweis nicht mehr anzeigen"
 * gewählt hatte, konnte das nirgends widerrufen. Auf einem geteilten Gerät ist
 * das kein theoretisches Problem, und bei dieser Zielgruppe erst recht nicht.
 */
class SpeicherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BarrierefreiheitSeeder::class);
    }

    public function test_die_uebersicht_steht_auf_der_barrierefreiheits_seite(): void
    {
        $this->get('/barrierefreiheit')
            ->assertOk()
            ->assertSee('id="gespeicherte-einstellungen"', false)
            ->assertSee('Gespeicherte Einstellungen');
    }

    public function test_die_uebersicht_nennt_jeden_eintrag_aus_der_registratur(): void
    {
        /*
         * Die Liste führt die Anwendung, nicht die Redaktion: Eine von Hand
         * gepflegte Aufzählung liefe dem hinterher, was wirklich gespeichert
         * wird — und genau daraus entsteht eine falsche Datenschutzerklärung.
         */
        $html = $this->get('/barrierefreiheit')->getContent();

        foreach (config('speicher.eintraege') as $eintrag) {
            $this->assertStringContainsString(
                __($eintrag['label']),
                $html,
                "Eintrag '{$eintrag['schluessel']}' fehlt in der Übersicht"
            );
            $this->assertStringContainsString(
                'data-speicher-eintrag="'.$eintrag['schluessel'].'"',
                $html
            );
        }
    }

    /** Alle Schlüsselnamen aus der Registratur. */
    private function bekannteSchluessel(): array
    {
        return collect(config('speicher.eintraege'))
            ->flatMap(fn ($e) => array_merge($e['local'] ?? [], $e['session'] ?? []))
            ->all();
    }

    public function test_direkt_geschriebene_schluessel_stehen_in_der_registratur(): void
    {
        /*
         * Der Wächter dieser Datei.
         *
         * Wer künftig etwas im Browser speichert, muss es in config/speicher.php
         * eintragen — sonst lässt es sich nicht zurücksetzen und fehlt in der
         * Datenschutzerklärung. Geprüft werden die Stellen, an denen ein
         * Schlüssel wörtlich im Code steht; das ist der Weg, auf dem so etwas
         * erfahrungsgemäss hinzukommt (ein schnelles getItem im Kopf der Seite).
         *
         * Wo Schlüssel über Konstanten laufen, greift der Test darunter.
         */
        $bekannt = $this->bekannteSchluessel();

        foreach ([
            resource_path('views/layouts/app.blade.php'),
            resource_path('js/trigger-warnung.js'),
            resource_path('js/spendenhinweis.js'),
            resource_path('js/a11y.js'),
        ] as $datei) {
            preg_match_all(
                "/(?:local|session)Storage\.(?:set|get|remove)Item\(\s*'([^']+)'/",
                file_get_contents($datei),
                $treffer
            );

            foreach (array_unique($treffer[1]) as $schluessel) {
                $this->assertContains(
                    $schluessel,
                    $bekannt,
                    "Der Schlüssel '{$schluessel}' aus ".basename($datei)
                        .' fehlt in config/speicher.php — er liesse sich dann nicht zurücksetzen.'
                );
            }
        }
    }

    public function test_kein_eintrag_der_registratur_zeigt_ins_leere(): void
    {
        /*
         * Die Gegenrichtung: Ein Schlüssel, den niemand mehr schreibt, steht
         * dem Verein gegenüber als „das speichern wir" — und in der
         * Datenschutzerklärung ebenso. Beides wäre falsch.
         */
        $quellen = collect([
            resource_path('views/layouts/app.blade.php'),
            resource_path('js/trigger-warnung.js'),
            resource_path('js/spendenhinweis.js'),
            base_path('config/darstellung.php'),
        ])->map(fn ($p) => file_get_contents($p))->implode("\n");

        foreach ($this->bekannteSchluessel() as $schluessel) {
            $this->assertStringContainsString(
                $schluessel,
                $quellen,
                "Der Schlüssel '{$schluessel}' steht in config/speicher.php, "
                    .'wird aber nirgends mehr geschrieben.'
            );
        }
    }

    public function test_die_schluesselliste_steht_auf_jeder_seite(): void
    {
        // „Alles zurücksetzen“ in der Darstellungs-Toolbar erreicht man von
        // überall — die Liste muss deshalb auch überall bereitliegen.
        foreach (['/', '/barrierefreiheit'] as $pfad) {
            $this->get($pfad)
                ->assertOk()
                ->assertSee('window.keSpeicher', false)
                ->assertSee('ke.trigger.aus', false);
        }
    }

    public function test_zuruecksetzen_verlangt_javascript(): void
    {
        // Ohne JavaScript speichert die Seite nichts — dann gibt es auch nichts
        // zurückzusetzen, und ein Knopf wäre eine leere Zusage.
        $html = $this->get('/barrierefreiheit')->getContent();

        $this->assertStringContainsString('data-speicher-loeschen', $html);
        $this->assertStringContainsString('data-speicher-braucht-js', $html);
        $this->assertStringContainsString('Ohne JavaScript speichert diese Seite nichts', $html);
    }

    public function test_der_fuss_fuehrt_zu_den_gespeicherten_einstellungen(): void
    {
        // Kevins Vorgabe: Der Weg zurück muss ohne Suchen erreichbar sein.
        $this->get('/')
            ->assertOk()
            ->assertSee('/barrierefreiheit#gespeicherte-einstellungen', false)
            ->assertSee('Gespeicherte Einstellungen');
    }

    public function test_die_uebersicht_wird_auf_bestehenden_installationen_nachgetragen(): void
    {
        /*
         * Seeder laufen auf dem Server nicht. Ohne die Migration bekäme keine
         * eingerichtete Installation den Weg zurück — und genau die haben ihn
         * am nötigsten, weil dort schon jemand etwas gespeichert haben kann.
         */
        $seite = Page::where('slug', 'barrierefreiheit')->firstOrFail();
        $seite->blocks()->where('typ', 'speicher_uebersicht')->delete();

        $this->get('/barrierefreiheit')->assertDontSee('id="gespeicherte-einstellungen"', false);

        $migration = require database_path('migrations/2026_08_05_125000_speicher_uebersicht_nachtragen.php');
        $migration->up();

        $this->get('/barrierefreiheit')->assertSee('id="gespeicherte-einstellungen"', false);
    }

    public function test_die_migration_legt_die_uebersicht_nicht_doppelt_an(): void
    {
        $seite = Page::where('slug', 'barrierefreiheit')->firstOrFail();
        $migration = require database_path('migrations/2026_08_05_125000_speicher_uebersicht_nachtragen.php');

        $migration->up();
        $migration->up();

        $this->assertSame(1, $seite->blocks()->where('typ', 'speicher_uebersicht')->count());
    }
}
