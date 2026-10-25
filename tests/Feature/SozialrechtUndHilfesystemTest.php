<?php

namespace Tests\Feature;

use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Support\Suche;
use Database\Seeders\SozialrechtUndHilfesystemSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-261: Glossar „Sozialrecht & Hilfesystem“ unter „Wissen“, mit Bild,
 * Unterzeile und grüner Überschrift aus dem Ticket und Taddis typischen
 * Sätzen darunter (KEV-262).
 */
class SozialrechtUndHilfesystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_eigene_seite_unter_wissen_nach_medizinische_berichte(): void
    {
        // Seit KEV-254 unter „Klartext statt Fachsprache“ im Bereich Wissen.
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $klartext = collect($wissen['children'])->firstWhere('url', '/klartext-statt-fachsprache');
        $urls = array_column($klartext['children'], 'url');
        // Taddis 6, seit KEV-260 nach ihrer 5.
        $this->assertSame(array_search('/medizinische-berichte-und-gutachten', $urls) + 1, array_search('/sozialrecht-und-hilfesystem', $urls));

        $html = $this->get('/sozialrecht-und-hilfesystem')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Sozialrecht &amp; Hilfesystem - Kein Einzelfall e.V.</title>', $html);
        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Sozialrecht &amp; Hilfesystem\s*<\/h1>/u', $html);
        $this->assertMatchesRegularExpression('/aria-label="Sie sind hier".*Wissen.*Sozialrecht &amp; Hilfesystem/s', $html);
        $this->assertStringContainsString('/sozialrecht-und-hilfesystem</loc>', $this->get('/sitemap.xml')->getContent());
    }

    public function test_bild_unterzeile_und_gruene_ueberschrift_aus_dem_ticket(): void
    {
        $html = $this->get('/sozialrecht-und-hilfesystem')->getContent();

        $this->assertStringContainsString('src="/img/titelbilder/sozialrecht-und-hilfesystem.webp?v=', $html);
        $this->assertFileExists(public_path('img/titelbilder/sozialrecht-und-hilfesystem-1000.webp'));
        $this->assertStringContainsString('Leistungen verstehen. Zuständigkeiten einordnen. Unterstützung finden.', $html);
        $this->assertMatchesRegularExpression(
            '/<h2[^>]*text-green[^>]*>\s*Zwischen Leistungsträgern, Teilhabe und Pflege den Überblick behalten\.\s*<\/h2>/u',
            $html,
        );
    }

    public function test_einleitung_von_taddi(): void
    {
        $html = $this->get('/sozialrecht-und-hilfesystem')->getContent();

        $this->assertStringContainsString('Hier findest Du typische Begriffe aus dem Sozialrecht und aus verschiedenen Hilfesystemen', $html);
        foreach (['Leistungsträger', 'Teilhabe und Eingliederungshilfe', 'GdB, GdS und Pflegegrad', 'Fehlt Dir hier etwas?'] as $fett) {
            $this->assertMatchesRegularExpression('/<strong[^>]*>'.preg_quote($fett, '/').'<\/strong>/u', $html, $fett);
        }
        $this->assertStringContainsString('Sie ersetzen keine individuelle Rechtsberatung', $html);
    }

    public function test_alle_begriffe_und_saetze(): void
    {
        $this->assertCount(99, SozialrechtUndHilfesystemSeeder::eintraege());
        $this->assertCount(61, SozialrechtUndHilfesystemSeeder::saetze());

        $html = $this->get('/sozialrecht-und-hilfesystem')->getContent();

        $this->assertSame(99, substr_count($html, 'data-glossar-eintrag'));
        $this->assertStringContainsString('id="gdb-grad-der-behinderung"', $html);
        $this->assertStringContainsString('Er wird in Zehnergraden festgestellt.', $html);
        $this->assertStringNotContainsString('Einfach erklärt', $html);

        // Begriffe, die es schon gibt, bekommen ein eigenes Sprungziel.
        $this->assertStringContainsString('id="antrag-sozialrecht"', $html);
        $this->assertStringNotContainsString('id="antrag-sozialrecht"', $this->get('/behoerden-und-bescheide')->getContent());

        // Typische Sätze darunter, mit Sprung aus dem Glossar.
        $this->assertSame(61, substr_count($html, 'Einfach übersetzt:'));
        $this->assertMatchesRegularExpression('/typische Formulierungen aus <strong[^>]*>dem Sozialrecht und aus verschiedenen Hilfesystemen<\/strong>/u', $html);
        $this->assertStringContainsString('„Der GdB beträgt 40.“', $html);
        $this->assertStringContainsString('href="#typische-saetze"', $html);
    }

    public function test_websuche_findet_die_seite(): void
    {
        $treffer = collect(app(Suche::class)->suchen('Eingliederungshilfe')['treffer'])
            ->firstWhere('url', '/sozialrecht-und-hilfesystem#eingliederungshilfe');

        $this->assertNotNull($treffer);
        $this->assertSame('Sozialrecht & Hilfesystem', $treffer['bereich']);
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        GlossaryTerm::where('slug', 'pflegegeld')->update(['erklaerung' => 'Vom Verein geändert.']);

        SozialrechtUndHilfesystemSeeder::anlegen();
        (require database_path('migrations/2026_10_25_130000_sozialrecht_und_hilfesystem.php'))->up();

        $seite = Page::where('slug', 'sozialrecht-und-hilfesystem')->where('locale', 'de')->sole();
        $this->assertSame(['text', 'glossar', 'typische_saetze'], $seite->blocks()->orderBy('position')->pluck('typ')->all());
        $this->assertSame(1, GlossaryTerm::where('slug', 'pflegegeld')->where('locale', 'de')->count());
        $this->assertSame('Vom Verein geändert.', GlossaryTerm::where('slug', 'pflegegeld')->where('locale', 'de')->value('erklaerung'));
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/sozialrecht-und-hilfesystem')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Social law &amp; support system\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Understand benefits. Work out who is responsible. Find support.', $html);
        $this->assertStringContainsString('Keeping track between benefit providers, participation and care.', $html);
        $this->assertStringContainsString('Integration assistance (Eingliederungshilfe)', $html);
        $this->assertSame(99, substr_count($html, 'data-glossar-eintrag'));
        $this->assertSame(61, substr_count($html, 'In plain words:'));
        $this->assertStringContainsString('No GdB of at least 50 has been determined.', $html);
    }
}
