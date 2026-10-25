<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\KlartextSuche;
use Database\Seeders\GlossarSozialrechtSeeder;
use Database\Seeders\KlartextStattFachspracheSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-254: „Klartext statt Fachsprache“ unter „Wissen“ mit Taddis
 * Kategorien 1 bis 6 als Kacheln und einer Suche über alle.
 */
class KlartextStattFachspracheTest extends TestCase
{
    use RefreshDatabase;

    private const KATEGORIEN = [
        '/glossar', '/abkuerzungen-im-sexuellen-kontext', '/behoerden-und-bescheide',
        '/gericht-und-verfahren', '/medizinische-berichte-und-gutachten', '/sozialrecht-und-hilfesystem',
    ];

    public function test_im_menue_unter_wissen_mit_den_sechs_kategorien(): void
    {
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $klartext = collect($wissen['children'])->last();

        $this->assertSame('/klartext-statt-fachsprache', $klartext['url']);
        $this->assertSame(self::KATEGORIEN, array_column($klartext['children'], 'url'));
        // Keine Kategorie steht zusätzlich direkt unter „Wissen“.
        $this->assertSame([], array_intersect(self::KATEGORIEN, array_column($wissen['children'], 'url')));

        $this->assertSame(
            ['Abkürzungen im Sozialrecht', 'Abkürzungen im sexuellen Kontext', 'Behörden & Bescheide', 'Gericht & Verfahren',
                'Medizinische Berichte & Gutachten', 'Sozialrecht & Hilfesystem'],
            array_map(fn (Page $s) => $s->titel, KlartextSuche::kategorien()),
        );
    }

    public function test_uebersicht_mit_text_suche_und_kacheln(): void
    {
        $html = $this->get('/klartext-statt-fachsprache')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Klartext statt Fachsprache\s*<\/h1>/u', $html);
        $this->assertStringContainsString('src="/img/titelbilder/platzhalter.webp?v=', $html);
        $this->assertStringContainsString('Genau deshalb machen wir hier Fachsprache verständlich.', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Denn du bist ein Teil davon!<\/strong>/u', $html);
        // Fünf Absätze, nichts eingeklappt
        $this->assertStringNotContainsString('Weiterlesen', $html);

        $this->assertStringContainsString('id="klartext-suchfeld"', $html);

        // Die sechs Kacheln in Taddis Reihenfolge, mit Bild und Unterzeile
        preg_match_all('/<li class="flex min-w-0">\s*<a href="([^"]+)"/', $html, $kacheln);
        $this->assertSame(self::KATEGORIEN, $kacheln[1]);
        $this->assertStringContainsString('lg:grid-cols-3', $html);
        $this->assertStringContainsString('Damit aus Buchstaben wieder Bedeutung wird.', $html);

        $this->assertMatchesRegularExpression('/aria-label="Sie sind hier".*Wissen.*Klartext statt Fachsprache/s', $html);
    }

    /** Brotkrumen der Kategorien mit dem Weg zurück zu „Wissen“. */
    public function test_brotkrumen_der_kategorien(): void
    {
        $html = $this->get('/gericht-und-verfahren')->getContent();

        $this->assertMatchesRegularExpression(
            '/aria-label="Sie sind hier".*href="\/wissen".*href="\/klartext-statt-fachsprache".*Gericht &amp; Verfahren/s',
            $html,
        );
    }

    public function test_suche_findet_begriffe_und_saetze_in_allen_kategorien(): void
    {
        $html = $this->get('/klartext-statt-fachsprache?suche=GdB')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/Treffer: \d+ \(in \d von 6 Kategorien\)/u', $html);
        // Abkürzung im ersten Glossar, Begriff und Satz in Sozialrecht & Hilfesystem
        $this->assertStringContainsString('href="/glossar#gdb"', $html);
        $this->assertStringContainsString('href="/sozialrecht-und-hilfesystem#gdb-grad-der-behinderung"', $html);
        $this->assertStringContainsString('href="/sozialrecht-und-hilfesystem#typische-saetze"', $html);
        $this->assertStringContainsString('„Der GdB beträgt 40.“', $html);
        $this->assertStringContainsString('value="GdB"', $html);

        $leer = $this->get('/klartext-statt-fachsprache?suche=xyzxyz')->getContent();
        $this->assertStringContainsString('Zu „xyzxyz“ steht in keiner Kategorie etwas.', $leer);
        $this->assertStringContainsString('href="/suche?q=xyzxyz"', $leer);
    }

    public function test_umbenennung_nur_wo_noch_glossar_steht(): void
    {
        $glossar = Page::where('slug', 'glossar')->where('locale', 'de')->sole();
        $this->assertSame('Abkürzungen im Sozialrecht', $glossar->titel);

        $migration = require database_path('migrations/2026_10_25_200000_klartext_statt_fachsprache.php');

        $glossar->update(['titel' => GlossarSozialrechtSeeder::TITEL_BIS_25_10]);
        $migration->up();
        $this->assertSame('Abkürzungen im Sozialrecht', $glossar->fresh()->titel);

        $glossar->update(['titel' => 'Vom Verein']);
        $migration->up();
        $this->assertSame('Vom Verein', $glossar->fresh()->titel);
        $this->assertSame(1, Page::where('slug', KlartextStattFachspracheSeeder::SLUG)->where('locale', 'de')->count());
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/klartext-statt-fachsprache')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Plain language instead of jargon\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Search all categories', $html);
        $this->assertStringContainsString('Abbreviations in social law', $html);

        $treffer = $this->get('/en/klartext-statt-fachsprache?suche=objection')->getContent();
        $this->assertMatchesRegularExpression('/Results: \d+ \(in \d of 6 categories\)/u', $treffer);
        $this->assertStringContainsString('href="/en/behoerden-und-bescheide#widerspruch"', $treffer);
    }
}
