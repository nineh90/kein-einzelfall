<?php

namespace Tests\Feature;

use App\Models\PageBlock;
use App\Support\Dokument;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Dokumente der Altseite liegen jetzt bei uns.
 *
 * Vorher zeigten 32 Verweise in 8 Bausteinen auf `/wp-content/uploads/…` und
 * liefen ins Leere. Das war der letzte Grund, warum die Seite nicht live
 * gehen konnte.
 *
 * Geholt werden sie mit `php artisan dokumente:holen`.
 */
class DokumenteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_kein_verweis_zeigt_mehr_auf_die_altseite(): void
    {
        // Der Kern: Nach dem Umzug darf nichts mehr die alte WordPress-Adresse
        // ansteuern. Sonst haengt die neue Seite an der alten.
        foreach (PageBlock::where('typ', 'download_list')->get() as $block) {
            foreach ($block->data['dokumente'] ?? [] as $dokument) {
                $this->assertStringNotContainsString(
                    '/wp-content/',
                    $dokument['url'],
                    "„{$dokument['titel']}“ zeigt noch auf die Altseite."
                );
            }
        }
    }

    public function test_jeder_verlinkte_download_existiert_auch(): void
    {
        $geprueft = 0;

        foreach (PageBlock::where('typ', 'download_list')->get() as $block) {
            foreach ($block->data['dokumente'] ?? [] as $dokument) {
                if (! str_starts_with($dokument['url'], '/'.Dokument::ORDNER.'/')) {
                    continue;   // externe Adresse, koennen wir nicht pruefen
                }

                $geprueft++;

                $this->assertFileExists(
                    public_path(ltrim($dokument['url'], '/')),
                    "Die Datei zu „{$dokument['titel']}“ fehlt. "
                    .'Fehlt sie wirklich, hilft `php artisan dokumente:holen`.'
                );
            }
        }

        // Wenn hier nichts geprueft wurde, ist der Test wertlos geworden.
        $this->assertGreaterThan(20, $geprueft, 'Es sollten gut 30 Dokumente verlinkt sein.');
    }

    public function test_die_angegebene_groesse_stimmt_mit_der_datei_ueberein(): void
    {
        // Die Groesse steht im Linktext (WCAG 3.2.5) — wer ueber Mobilfunk
        // liest, soll vor dem Tippen wissen, was auf ihn zukommt. Eine falsche
        // Angabe waere schlimmer als keine.
        foreach (PageBlock::where('typ', 'download_list')->get() as $block) {
            foreach ($block->data['dokumente'] ?? [] as $dokument) {
                $datei = public_path(ltrim($dokument['url'], '/'));

                if (! is_file($datei) || empty($dokument['bytes'])) {
                    continue;
                }

                $this->assertSame(
                    filesize($datei),
                    $dokument['bytes'],
                    "Groessenangabe zu „{$dokument['titel']}“ passt nicht zur Datei."
                );
            }
        }
    }

    public function test_alte_dokument_adressen_leiten_dauerhaft_weiter(): void
    {
        // Diese Adressen sind indexiert, extern verlinkt und stehen in PDFs,
        // die der Verein verschickt hat.
        $alt = '/wp-content/uploads/2026/05/26.04.02.-Satzung-II.pdf';

        if (! Dokument::vorhanden($alt)) {
            $this->markTestSkipped('Dokumente noch nicht geholt.');
        }

        $this->get($alt)->assertRedirect(Dokument::pfad($alt));
        $this->assertSame(301, $this->get($alt)->getStatusCode());
    }

    public function test_unbekannte_dokument_adresse_ist_ein_404(): void
    {
        $this->get('/wp-content/uploads/2020/01/gibt-es-nicht.pdf')->assertNotFound();
    }

    public function test_die_weiterleitung_laesst_sich_nicht_aus_dem_ordner_locken(): void
    {
        // Der Pfad kommt aus der Adresszeile. Ohne Pruefung waere das ein Weg,
        // beliebige Dateien des Servers anzusteuern.
        foreach ([
            '/wp-content/uploads/../../.env',
            '/wp-content/uploads/..%2F..%2F.env',
            '/wp-content/uploads/../storage/logs/laravel.log',
        ] as $angriff) {
            $antwort = $this->get($angriff);

            $this->assertNotEquals(
                301,
                $antwort->getStatusCode(),
                "{$angriff} haette nicht weiterleiten duerfen."
            );
        }
    }

    public function test_baustein_blendet_fehlende_dateien_aus(): void
    {
        // Ein Download-Knopf, der ins Leere fuehrt, ist fuer diese Zielgruppe
        // schlechter als kein Knopf.
        $block = PageBlock::where('typ', 'download_list')->firstOrFail();
        $daten = $block->data;
        $daten['dokumente'][] = [
            'titel' => 'Diese Datei gibt es nicht',
            'url' => '/'.Dokument::ORDNER.'/2020/01/fehlt.pdf',
            'bytes' => 1234,
        ];
        $block->update(['data' => $daten]);

        $this->get('/'.$block->page->slug)
            ->assertOk()
            ->assertDontSee('Diese Datei gibt es nicht');
    }

    public function test_externe_adressen_bleiben_unangetastet(): void
    {
        $extern = 'https://www.gesetze-im-internet.de/oeg/BJNR011810976.html';

        $this->assertSame($extern, Dokument::pfad($extern));
    }
}
