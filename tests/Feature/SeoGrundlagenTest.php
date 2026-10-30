<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\KlartextStattFachspracheSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SEO-Prüfung vom 28.10.2026: was der Crawler über alle Seiten gefunden hat
 * und seitdem gilt.
 */
class SeoGrundlagenTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_php_leitet_auf_die_saubere_adresse_um(): void
    {
        $this->get('/index.php/verein')->assertStatus(301)->assertRedirect('http://localhost/verein');
        $this->get('/index.php')->assertStatus(301)->assertRedirect('http://localhost/');
    }

    public function test_panel_nicht_im_index(): void
    {
        $this->get('/admin/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/verein')->assertHeaderMissing('X-Robots-Tag');
    }

    /**
     * Seit 30.10.2026 stehen die maschinellen Übersetzungen im Index (Kevin):
     * hreflang und Sitemap nennen sie, solange die deutsche Fassung selbst in
     * den Index darf.
     */
    public function test_hreflang_und_sitemap_nur_mit_indexierbaren_fassungen(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        // (Altseiten wie /verein gibt es in der Test-Datenbank nicht.)
        $html = $this->get('/klartext-statt-fachsprache')->getContent();
        $this->assertStringContainsString('<link rel="alternate" hreflang="en" href="http://localhost/en/klartext-statt-fachsprache">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="x-default" href="http://localhost/klartext-statt-fachsprache">', $html);

        $sitemap = $this->get('/sitemap.xml')->getContent();
        $this->assertStringContainsString('/en/klartext-statt-fachsprache</loc>', $sitemap);
        $this->assertStringContainsString('/veranstaltungen</loc>', $sitemap);
        // Veranstaltungen sind nicht übersetzt (keine Seite, keine Termine): nur deutsch
        $this->assertStringNotContainsString('/en/veranstaltungen</loc>', $sitemap);
        // Aktuelles erst mit Beiträgen
        $this->assertStringNotContainsString('/aktuelles</loc>', $sitemap);

        // Darf die deutsche Fassung nicht in den Index, die englische auch nicht
        Page::whereIn('uebersetzungs_gruppe',
            Page::where('slug', 'klartext-statt-fachsprache')->where('locale', 'de')->pluck('uebersetzungs_gruppe'))
            ->where('locale', 'en')->update(['noindex' => true]);
        $html = $this->get('/klartext-statt-fachsprache')->getContent();
        $this->assertStringNotContainsString('<link rel="alternate"', $html);
        // Der Sprachumschalter bietet Englisch trotzdem an
        $this->assertStringContainsString('href="/en/klartext-statt-fachsprache"', $html);
    }

    /** Die Übersetzung übernimmt das noindex der deutschen Seite, auch per Migration. */
    public function test_englisch_folgt_dem_noindex_der_deutschen_fassung(): void
    {
        $de = Page::where('slug', 'klartext-statt-fachsprache')->where('locale', 'de')->firstOrFail();
        $de->update(['noindex' => true]);
        $this->seed(UebersetzungenSeeder::class);
        $en = Page::where('uebersetzungs_gruppe', $de->uebersetzungs_gruppe)->where('locale', 'en')->firstOrFail();
        $this->assertTrue($en->noindex);
        $this->assertTrue($en->ungeprueft);

        $de->update(['noindex' => false]);
        $en->update(['noindex' => true]);
        (require database_path('migrations/2026_10_30_100000_englisch_in_den_index.php'))->up();
        $this->assertFalse($en->fresh()->noindex);
    }

    /**
     * Ohne Seitendatensatz, auf Englisch: immer mit Vermerk. Gruppen sind
     * übersetzt und dürfen in den Index (mit hreflang und in der Sitemap),
     * Übersichten ohne übersetzten Inhalt nicht.
     */
    public function test_englische_seiten_ohne_datensatz_noindex_mit_vermerk(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        foreach (['/en/selbsthilfegruppen/seelenfarben', '/en/arbeitsgruppen/ag-01-ser'] as $pfad) {
            $html = $this->get($pfad)->assertOk()->getContent();
            $this->assertStringNotContainsString('name="robots"', $html, $pfad);
            $this->assertStringContainsString('role="status"', $html, $pfad);
            $this->assertStringContainsString('hreflang="de"', $html, $pfad);
        }

        $html = $this->get('/en/veranstaltungen')->assertOk()->getContent();
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $html);
        $this->assertStringContainsString('role="status"', $html);

        $sitemap = $this->get('/sitemap.xml')->getContent();
        $this->assertStringContainsString('/en/selbsthilfegruppen/seelenfarben</loc>', $sitemap);
        $this->assertStringContainsString('/selbsthilfegruppen/seelenfarben</loc>', $sitemap);

        $deutsch = $this->get('/selbsthilfegruppen/seelenfarben')->getContent();
        $this->assertStringNotContainsString('name="robots"', $deutsch);
    }

    public function test_aktuelles_ohne_beitraege_nicht_im_index(): void
    {
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">',
            $this->get('/aktuelles')->getContent());
    }

    public function test_vorschaubild_fuer_geteilte_links(): void
    {
        $html = $this->get('/klartext-statt-fachsprache')->getContent();
        $this->assertStringContainsString('<meta property="og:image" content="http://localhost/img/og/klartext-statt-fachsprache.jpg">', $html);
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $html);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);

        // Gruppe mit eigenem Bild, Seite ohne Bild: Vereinsbild
        $this->assertStringContainsString('/img/og/gruppe-seelenfarben.jpg', $this->get('/selbsthilfegruppen/seelenfarben')->getContent());
        $this->assertStringContainsString('/img/og/verein.jpg', $this->get('/aktuelles')->getContent());

        // Zu jedem Titelbild gibt es das Vorschaubild (bin/og-bilder)
        foreach (glob(public_path('img/titelbilder/*.webp')) as $datei) {
            $name = basename($datei, '.webp');
            if (str_ends_with($name, '-1000') || $name === 'platzhalter') {
                continue;
            }
            $this->assertFileExists(public_path("img/og/{$name}.jpg"), "bin/og-bilder für {$name} laufen lassen");
        }
    }

    public function test_strukturierte_daten(): void
    {
        $start = $this->get('/')->getContent();
        preg_match('#<script type="application/ld\+json"[^>]*>(.*?)</script>#s', $start, $m);
        $verein = json_decode($m[1], true);
        $this->assertSame('NGO', $verein['@type']);
        $this->assertSame('KE!N EINZELFALL e.V.', $verein['name']);
        $this->assertSame('22119', $verein['address']['postalCode']);
        $this->assertContains('https://www.instagram.com/kein_einzelfall_opferhilfe', $verein['sameAs']);

        $seite = $this->get('/gericht-und-verfahren')->getContent();
        preg_match('#<script type="application/ld\+json"[^>]*>\s*(\{"@context":"https://schema.org","@type":"BreadcrumbList".*?)\s*</script>#s', $seite, $m);
        $krumen = json_decode($m[1], true)['itemListElement'];
        $this->assertSame(['Start', 'Wissen', 'Klartext statt Fachsprache', 'Gericht & Verfahren'], array_column($krumen, 'name'));
        $this->assertSame('http://localhost/gericht-und-verfahren', end($krumen)['item']);
    }

    public function test_beschreibungen_im_suchergebnis(): void
    {
        foreach (['klartext-statt-fachsprache', 'selbsthilfegruppen/skillin-me-softly', 'arbeitsgruppen/ag-04-soziale-medien'] as $pfad) {
            preg_match('/<meta name="description" content="([^"]*)"/', $this->get('/'.$pfad)->getContent(), $m);
            $laenge = mb_strlen(html_entity_decode($m[1]));
            $this->assertTrue($laenge >= 70 && $laenge <= 160, "{$pfad}: {$laenge} Zeichen");
        }
    }

    public function test_migration_ersetzt_nur_den_alten_wortlaut(): void
    {
        $klartext = Page::where('slug', KlartextStattFachspracheSeeder::SLUG)->where('locale', 'de')->sole();
        $migration = require database_path('migrations/2026_10_28_200000_seo_beschreibungen.php');

        $klartext->update(['meta_description' => KlartextStattFachspracheSeeder::META_DESCRIPTION_BIS_28_10]);
        $migration->up();
        $this->assertSame(KlartextStattFachspracheSeeder::META_DESCRIPTION, $klartext->fresh()->meta_description);

        $klartext->update(['meta_description' => 'Vom Verein gepflegt.']);
        $migration->up();
        $this->assertSame('Vom Verein gepflegt.', $klartext->fresh()->meta_description);

        // Die neuen Texte der Altseiten ersetzen genau die alten
        $this->assertSame(['verein', 'mitgliedschaft'], array_keys(AltseiteSeeder::BESCHREIBUNGEN_BIS_28_10));
        foreach (AltseiteSeeder::BESCHREIBUNGEN_BIS_28_10 as $slug => $alt) {
            $this->assertLessThanOrEqual(160, mb_strlen(AltseiteSeeder::NEUE_BESCHREIBUNGEN[$slug]));
        }
    }
}
