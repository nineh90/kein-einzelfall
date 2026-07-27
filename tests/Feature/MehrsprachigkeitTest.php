<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Zusage an den Kunden lautet: „SEO darf nicht schlechter werden.“
 *
 * Übersetzt in Prüfbares heisst das vor allem, was *nicht* passieren darf —
 * kein Präfix für Deutsch, keine zweite Adresse für denselben Inhalt, keine
 * Seite, die durch ein Sprachpräfix unerreichbar wird.
 */
class MehrsprachigkeitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    private function englischFreischalten(): Language
    {
        $englisch = Language::finden('en');
        $englisch->update(['aktiv' => true]);
        Language::memoLeeren();

        return $englisch->refresh();
    }

    public function test_deutsche_adressen_bleiben_ohne_praefix(): void
    {
        // Der Kern der Zusage: die 24 Adressen der Altseite ändern sich nicht.
        foreach (Page::where('locale', 'de')->pluck('slug') as $slug) {
            $this->get('/'.$slug)->assertOk();
        }

        $this->get('/')->assertOk();
    }

    public function test_standardsprache_hat_keine_zweite_adresse(): void
    {
        // /de/verein und /verein wären derselbe Inhalt unter zwei Adressen —
        // genau die SEO-Substanz, die wir schützen sollen.
        $this->get('/de/verein')->assertRedirect('/verein');
        $this->get('/de')->assertRedirect('/');
    }

    public function test_weiterleitung_der_standardsprache_behaelt_suchparameter(): void
    {
        $this->get('/de/aktuelles?suche=trauma')->assertRedirect('/aktuelles?suche=trauma');
    }

    public function test_nicht_freigeschaltete_sprache_ist_nicht_erreichbar(): void
    {
        // Englisch und Russisch sind angelegt, aber unübersetzt. Niemand soll
        // auf einer leeren Sprachfassung landen, bevor der Verein sie freigibt.
        $this->get('/en/verein')->assertNotFound();
        $this->get('/ru/verein')->assertNotFound();
    }

    public function test_unbekannter_sprachcode_ist_ein_404(): void
    {
        $this->get('/xy/verein')->assertNotFound();
    }

    public function test_freigeschaltete_sprache_liefert_ihre_eigene_seite_aus(): void
    {
        $englisch = $this->englischFreischalten();
        $deutsch = Page::where('locale', 'de')->where('slug', 'verein')->firstOrFail();

        Page::create([
            'locale' => $englisch->code,
            'uebersetzungs_gruppe' => $deutsch->uebersetzungs_gruppe,
            'slug' => 'about-us',
            'titel' => 'About us',
            'published_at' => now(),
        ]);

        $this->get('/en/about-us')
            ->assertOk()
            ->assertSee('About us', false);
    }

    public function test_fehlende_uebersetzung_faellt_sichtbar_auf_deutsch_zurueck(): void
    {
        $this->englischFreischalten();

        // Nicht 404: Es geht um Opferrechte und Fristen. Eine Seite, die still
        // verschwindet, ist schlechter als eine Seite in einer anderen Sprache
        // mit dem Hinweis, dass sie noch nicht übersetzt ist.
        $antwort = $this->get('/en/verein')->assertOk()->assertSee('Verein');

        // Der Hinweis muss sichtbar sein — sonst liest jemand unbemerkt eine
        // Sprache, die er nicht erwartet hat.
        $antwort->assertSee('not available in English yet', false);

        // Und der deutsche Inhalt muss als deutsch ausgezeichnet sein, sonst
        // spricht eine Vorlesehilfe ihn englisch aus (WCAG 3.1.2).
        $antwort->assertSee('<main id="inhalt" tabindex="-1" class="flex-1"', false);
        $antwort->assertSee('lang="de" dir="ltr"', false);
    }

    public function test_seite_ohne_rueckfall_zeichnet_den_inhalt_nicht_um(): void
    {
        // Auf Deutsch darf kein lang-Attribut am Inhalt stehen — es gäbe nichts
        // auszuzeichnen, und ein falsches wäre schlimmer als keines.
        $this->get('/verein')
            ->assertOk()
            ->assertDontSee('rueckfall')
            ->assertSee('<html lang="de" dir="ltr">', false);
    }

    public function test_uebersetzung_darf_denselben_slug_behalten(): void
    {
        $englisch = $this->englischFreischalten();
        $deutsch = Page::where('locale', 'de')->where('slug', 'kontakt')->firstOrFail();

        // Der frühere globale Unique-Index auf slug hätte das verhindert.
        Page::create([
            'locale' => $englisch->code,
            'uebersetzungs_gruppe' => $deutsch->uebersetzungs_gruppe,
            'slug' => 'kontakt',
            'titel' => 'Contact',
            'published_at' => now(),
        ]);

        $this->get('/kontakt')->assertOk()->assertSee('Kontakt');
        $this->get('/en/kontakt')->assertOk()->assertSee('Contact');
    }

    public function test_kein_bestehender_slug_sieht_aus_wie_ein_sprachpraefix(): void
    {
        // Sonst würde die Sprach-Route die Seite verschlucken, weil sie vor der
        // Sammelroute /{slug} steht — und niemand käme auf die Idee, das zu
        // vermuten. Die Validierungsregel hält neue Slugs davon fern; dieser
        // Test deckt den Bestand ab.
        foreach (Page::where('locale', 'de')->pluck('slug') as $slug) {
            $this->assertSame(
                0,
                preg_match('/^'.Language::ADRESS_MUSTER.'$/', $slug),
                "Der Slug „{$slug}“ wird vom Routing als Sprachpräfix gelesen."
            );
        }
    }

    public function test_genau_eine_sprache_ist_standard(): void
    {
        Language::finden('en')->update(['ist_standard' => true]);
        Language::memoLeeren();

        $this->assertSame(1, Language::query()->where('ist_standard', true)->count());
        $this->assertSame('en', Language::standardCode());
    }

    public function test_standardsprache_ist_immer_sichtbar(): void
    {
        // Sie unsichtbar zu schalten hiesse, die Website abzuschalten.
        $deutsch = Language::finden('de');
        $deutsch->update(['aktiv' => false]);

        $this->assertTrue($deutsch->refresh()->aktiv);
    }

    public function test_sitemap_nennt_die_seiten_und_ihre_sprachfassungen(): void
    {
        $englisch = $this->englischFreischalten();
        $deutsch = Page::where('locale', 'de')->where('slug', 'verein')->firstOrFail();

        Page::create([
            'locale' => $englisch->code,
            'uebersetzungs_gruppe' => $deutsch->uebersetzungs_gruppe,
            'slug' => 'about-us',
            'titel' => 'About us',
            'published_at' => now(),
        ]);

        $antwort = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $antwort->assertSee(url('/verein'));
        $antwort->assertSee(url('/en/about-us'));

        // Ohne die gegenseitigen Verweise wertet Google Übersetzungen als
        // doppelten Inhalt.
        $antwort->assertSee('hreflang="en"', false);
    }

    public function test_sitemap_verschweigt_seiten_mit_noindex(): void
    {
        Page::where('locale', 'de')->where('slug', 'verein')->update(['noindex' => true]);

        $this->get('/sitemap.xml')->assertOk()->assertDontSee(url('/verein').'<');
    }
}
