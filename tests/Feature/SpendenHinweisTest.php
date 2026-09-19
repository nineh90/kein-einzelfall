<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\StartseiteSeeder;
use Tests\TestCase;

/**
 * Spendenhinweis für wiederkehrende Besucherinnen (KEV-6).
 *
 * Geprüft wird hier, was der Server entscheidet: auf welchen Seiten der Kasten
 * überhaupt im HTML steht, dass er ohne Skript unsichtbar bleibt, und dass die
 * Schwellen aus der Konfiguration ankommen. Ob er im Browser zur richtigen
 * Zeit erscheint und wieder verschwindet, prüft tests/Browser/bedienung.mjs.
 */
class SpendenHinweisTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
        $this->seed(StartseiteSeeder::class);
    }

    public function test_inhaltsseiten_bringen_den_hinweis_mit(): void
    {
        foreach (['/', '/verein', '/glossar', '/aktuelles', '/veranstaltungen'] as $pfad) {
            $this->get($pfad)->assertOk()->assertSee('data-spendenhinweis', false);
        }
    }

    public function test_der_hinweis_ist_ohne_javascript_unsichtbar(): void
    {
        /*
         * `hidden` aus dem Server-HTML. Ohne Skript gibt es keinen Zähler,
         * also kein „wiederkehrend“ — und ein Kasten, der bei jedem Aufruf da
         * wäre, ist genau das, was nicht gewollt ist.
         */
        $html = $this->get('/verein')->getContent();

        $this->assertMatchesRegularExpression('/<aside[^>]*\bhidden\b[^>]*data-spendenhinweis/s', $html);
    }

    public function test_die_schwellen_kommen_aus_der_konfiguration(): void
    {
        config(['spendenhinweis.ab_aufrufen' => 7, 'spendenhinweis.ruhe_tage' => 45]);

        $this->get('/verein')
            ->assertSee('data-ab="7"', false)
            ->assertSee('data-ruhe-tage="45"', false);
    }

    public function test_auf_spenden_anfragen_und_kontakt_gibt_es_keinen_hinweis(): void
    {
        /*
         * Auf der Spendenseite wäre er überflüssig. Auf Anfragen und Kontakt
         * wäre er falsch: Wer gerade eine Anfrage schreibt, wird nicht um Geld
         * gebeten. Das ist der Unterschied zwischen Opferhilfe und Vertrieb.
         */
        foreach (['/spenden', '/anfragen', '/kontakt', '/trigger-warnung'] as $pfad) {
            $this->get($pfad)->assertOk()->assertDontSee('data-spendenhinweis', false);
        }
    }

    public function test_die_ausnahmen_gelten_in_jeder_sprache(): void
    {
        // Dieselbe Seite unter /en/… ist dieselbe Seite.
        $verein = Page::where('slug', 'verein')->firstOrFail();
        $spenden = Page::where('slug', 'spenden')->firstOrFail();

        foreach ([$verein, $spenden] as $seite) {
            $seite->replicate()->fill(['locale' => 'en', 'uebersetzungs_gruppe' => $seite->uebersetzungs_gruppe])->save();
        }
        \App\Models\Language::where('code', 'en')->update(['aktiv' => true]);
        \App\Models\Language::memoLeeren();

        $this->get('/en/verein')->assertOk()->assertSee('data-spendenhinweis', false);
        $this->get('/en/spenden')->assertOk()->assertDontSee('data-spendenhinweis', false);
    }

    public function test_fehlerseiten_bitten_nicht_um_spenden(): void
    {
        $this->get('/diese-seite-gibt-es-nicht')
            ->assertNotFound()
            ->assertDontSee('data-spendenhinweis', false);
    }

    public function test_der_knopf_fuehrt_zur_spendenseite(): void
    {
        $this->get('/verein')->assertSee('data-spendenhinweis-ziel', false);

        $this->assertMatchesRegularExpression(
            '/href="\/spenden"[^>]*data-spendenhinweis-ziel|data-spendenhinweis-ziel[^>]*href="\/spenden"/',
            $this->get('/verein')->getContent(),
        );
    }

    public function test_die_schluessel_stehen_in_der_registratur(): void
    {
        // Sonst liesse sich der Zähler nirgends zurücksetzen, und die
        // Datenschutzerklärung wäre unvollständig (SpeicherTest wacht darüber).
        $eintrag = collect(config('speicher.eintraege'))->firstWhere('schluessel', 'spendenhinweis');

        $this->assertNotNull($eintrag);
        $this->assertSame(['ke.spenden.aufrufe', 'ke.spenden.ruhe'], $eintrag['local']);
    }

    public function test_die_texte_liegen_in_jeder_sprache_vor(): void
    {
        foreach (['de', 'en'] as $locale) {
            foreach (['eyebrow', 'titel', 'text', 'knopf', 'spaeter', 'schliessen'] as $feld) {
                $this->assertNotSame(
                    "rahmen.spendenhinweis.{$feld}",
                    __("rahmen.spendenhinweis.{$feld}", [], $locale),
                    "rahmen.spendenhinweis.{$feld} fehlt für {$locale}",
                );
            }
        }
    }
}
