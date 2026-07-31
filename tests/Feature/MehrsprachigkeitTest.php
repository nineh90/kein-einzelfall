<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
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
        // Über pfad() statt über den Slug — die Startseite liegt unter „/“.
        foreach (Page::where('locale', 'de')->get() as $seite) {
            $this->get($seite->pfad())->assertOk();
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

    public function test_deutsche_seiten_enthalten_keine_kyrillischen_zeichen(): void
    {
        // Der Sprachumschalter steht auf jeder Seite. Stünde dort dauerhaft
        // „Русский“, lüde jede deutsche Seite die kyrillischen Schriftschnitte
        // mit — gemessen 137 KB, die die Hauptzielgruppe auf dem Mobilfunknetz
        // bezahlen würde, ohne sie je zu sehen.
        $this->englischFreischalten();
        Language::finden('ru')->update(['aktiv' => true]);
        Language::memoLeeren();

        $html = $this->get('/verein')->assertOk()->getContent();

        // Ausgenommen ist das aufklappbare Mobilmenü: Wer dort gezielt seine
        // Sprache sucht, erkennt nur die Eigenbezeichnung. Zugeklappt lädt der
        // Browser die Schrift nicht.
        $ohneMenue = preg_replace('/<details.*?<\/details>/s', '', $html);

        $this->assertSame(
            0,
            preg_match('/\p{Cyrillic}/u', $ohneMenue),
            'Kyrillische Zeichen ausserhalb des Mobilmenues ziehen die kyrillischen '
            .'Schriftschnitte auch auf deutschen Seiten mit.'
        );
    }

    public function test_kyrillische_schriftschnitte_liegen_vor(): void
    {
        // Fraunces hat keine kyrillischen Zeichen (geprüft gegen die
        // Google-Fonts-API: latin, latin-ext, vietnamese). Ohne Ersatz fielen
        // auf Russisch alle Überschriften auf eine Systemschrift zurück.
        foreach ([
            'literata-cyrillic',
            'literata-cyrillic-ext',
            'source-serif-4-cyrillic',
            'caveat-cyrillic',
        ] as $datei) {
            $this->assertFileExists(public_path("fonts/{$datei}.woff2"));
        }

        $css = file_get_contents(resource_path('css/fonts.css'));

        // Jede eingebundene Datei muss auch existieren. Variable Fonts liefern
        // für mehrere Schnitte dieselbe Datei — wer nach Schnitt statt nach URL
        // dedupliziert, schreibt Regeln auf Dateien, die es nie gab.
        preg_match_all('#url\(/fonts/([^)]+\.woff2)\)#', $css, $treffer);
        $this->assertNotEmpty($treffer[1]);

        foreach (array_unique($treffer[1]) as $datei) {
            $this->assertFileExists(public_path('fonts/'.$datei));
        }

        // Kein Request an Google — DSGVO-Anforderung des Projekts.
        $this->assertStringNotContainsString('googleapis', $css);
        $this->assertStringNotContainsString('gstatic', $css);

        // Literata muss im Schriftstapel hinter Fraunces stehen, nicht statt ihr.
        $app = file_get_contents(resource_path('css/app.css'));
        $this->assertMatchesRegularExpression(
            "/--font-display:\s*'Fraunces',\s*'Literata'/",
            $app
        );
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

    // --- Demo-Übersetzungen (UebersetzungenSeeder) ------------------------------

    /** Die Migration füllt die Sprachtabelle auf einer bestehenden Datenbank. */
    private function sprachenMigration(): object
    {
        return require database_path('migrations/2026_07_31_120000_sprachen_sicherstellen.php');
    }

    public function test_migration_fuellt_eine_leere_sprachtabelle(): void
    {
        // Der gemeldete Zustand: bestehende DB, „Sprachen“ im Panel leer. Der
        // Seeder lief nie, weil er nur auf frischer Datenbank läuft.
        Language::query()->delete();
        Language::memoLeeren();
        $this->assertSame(0, Language::query()->count());

        $this->sprachenMigration()->up();
        Language::memoLeeren();

        $this->assertEqualsCanonicalizing(
            ['de', 'en', 'ru'],
            Language::query()->pluck('code')->all(),
        );
    }

    public function test_demo_seeder_schaltet_englisch_und_russisch_frei(): void
    {
        // Vorher inaktiv, deshalb nicht erreichbar.
        $this->get('/en')->assertNotFound();

        $this->seed(UebersetzungenSeeder::class);

        $this->assertTrue(Language::finden('en')->aktiv);
        $this->assertTrue(Language::finden('ru')->aktiv);
        $this->assertCount(3, Language::aktive());
    }

    public function test_kernseiten_liegen_auf_englisch_und_russisch_vor(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        // Englische Startseite unter „/en“ — die Startseite behält den Wurzelpfad.
        $this->get('/en')
            ->assertOk()
            ->assertSee('No one should ever have to say', false)
            ->assertSee('<html lang="en"', false);

        // Russische Fassung des Vereins.
        $this->get('/ru/verein')
            ->assertOk()
            ->assertSee('основано в 2024 году', false)
            ->assertSee('<html lang="ru"', false);
    }

    public function test_der_umschalter_bietet_nach_dem_seeden_drei_sprachen(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/verein')->assertOk()->getContent();

        // Alle drei Sprachfassungen des Vereins sind verlinkt.
        $this->assertStringContainsString('hreflang="de"', $html);
        $this->assertStringContainsString('hreflang="en"', $html);
        $this->assertStringContainsString('hreflang="ru"', $html);
    }

    public function test_seite_ausserhalb_des_kerns_faellt_weiter_sichtbar_zurueck(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        // „satzung“ ist nicht im Kern-Set — Englisch ist trotzdem freigeschaltet.
        // Kein 404, sondern der deutsche Inhalt mit dem Hinweis in der Zielsprache.
        $this->get('/en/satzung')
            ->assertOk()
            ->assertSee('not available in English yet', false)
            ->assertSee('lang="de"', false);
    }

    public function test_uebersetzung_laesst_adressen_und_kontodaten_unangetastet(): void
    {
        // Nur Textfelder werden übersetzt. Ein übersetzter Link wäre ein toter
        // Link, eine „übersetzte“ IBAN schlicht falsch.
        $this->seed(UebersetzungenSeeder::class);

        // Die Bankverbindung steht wörtlich auch auf der englischen Spendenseite.
        $this->get('/en/spenden')
            ->assertOk()
            ->assertSee('DE79 8306 5408 0006 8893 10');

        // Der Knopf der englischen Startseite zeigt weiter auf die interne Adresse.
        $this->get('/en')->assertOk()->assertSee('href="/anfragen"', false);
    }

    public function test_demo_seeder_laeuft_zweimal_ohne_dubletten(): void
    {
        $this->seed(UebersetzungenSeeder::class);
        $this->seed(UebersetzungenSeeder::class);

        $this->assertSame(1, Page::where('locale', 'en')->where('slug', 'verein')->count());
    }

    public function test_demo_seeder_ueberschreibt_gepflegte_uebersetzungen_nicht(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        // Der Verein korrigiert eine maschinelle Übersetzung im Panel …
        Page::where('locale', 'en')->where('slug', 'verein')
            ->firstOrFail()
            ->update(['titel' => 'About us — reviewed']);

        // … ein erneuter Lauf darf das nicht zurücksetzen.
        $this->seed(UebersetzungenSeeder::class);

        $this->assertSame(
            'About us — reviewed',
            Page::where('locale', 'en')->where('slug', 'verein')->value('titel'),
        );
    }
}
