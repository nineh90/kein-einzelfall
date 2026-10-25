<?php

namespace Tests\Feature;

use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Support\Suche;
use Database\Seeders\MedizinischeBerichteUndGutachtenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-260: Glossar „Medizinische Berichte & Gutachten“ unter „Wissen“, mit Bild,
 * Unterzeile und grüner Überschrift aus dem Ticket und Taddis typischen
 * Sätzen darunter (KEV-262).
 */
class MedizinischeBerichteUndGutachtenTest extends TestCase
{
    use RefreshDatabase;

    public function test_eigene_seite_unter_wissen_vor_sozialrecht(): void
    {
        // Seit KEV-254 unter „Klartext statt Fachsprache“ im Bereich Wissen.
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $klartext = collect($wissen['children'])->firstWhere('url', '/klartext-statt-fachsprache');
        $urls = array_column($klartext['children'], 'url');
        // Taddis 5, zwischen ihrer 3 und 6 (ihre 4 kommt noch davor).
        $this->assertSame(array_search('/medizinische-berichte-und-gutachten', $urls) + 1, array_search('/sozialrecht-und-hilfesystem', $urls));
        $this->assertGreaterThan(array_search('/behoerden-und-bescheide', $urls), array_search('/medizinische-berichte-und-gutachten', $urls));

        $html = $this->get('/medizinische-berichte-und-gutachten')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Medizinische Berichte &amp; Gutachten - Kein Einzelfall e.V.</title>', $html);
        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Medizinische Berichte &amp; Gutachten\s*<\/h1>/u', $html);
        $this->assertMatchesRegularExpression('/aria-label="Sie sind hier".*Wissen.*Medizinische Berichte &amp; Gutachten/s', $html);
        $this->assertStringContainsString('/medizinische-berichte-und-gutachten</loc>', $this->get('/sitemap.xml')->getContent());
    }

    public function test_bild_unterzeile_und_gruene_ueberschrift_aus_dem_ticket(): void
    {
        $html = $this->get('/medizinische-berichte-und-gutachten')->getContent();

        $this->assertStringContainsString('src="/img/titelbilder/medizinische-berichte-und-gutachten.webp?v=', $html);
        $this->assertFileExists(public_path('img/titelbilder/medizinische-berichte-und-gutachten-1000.webp'));
        $this->assertStringContainsString('Befunde verstehen. Gutachten besser einordnen.', $html);
        $this->assertMatchesRegularExpression(
            '/<h2[^>]*text-green[^>]*>\s*Was medizinische Berichte wirklich sagen\s*<\/h2>/u',
            $html,
        );
    }

    public function test_einleitung_von_taddi(): void
    {
        $html = $this->get('/medizinische-berichte-und-gutachten')->getContent();

        $this->assertMatchesRegularExpression(
            '/Hier findest Du typische Begriffe aus <strong[^>]*>Arztbriefen, Befundberichten, medizinischen Gutachten, Reha-Berichten und sozialmedizinischen Einschätzungen<\/strong>/u',
            $html,
        );
        $this->assertStringContainsString('Sie ersetzen keine ärztliche Bewertung eines konkreten Befundes', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Fehlt Dir hier etwas\?<\/strong>/u', $html);
    }

    public function test_alle_begriffe_und_saetze(): void
    {
        $this->assertCount(115, MedizinischeBerichteUndGutachtenSeeder::eintraege());
        $this->assertCount(60, MedizinischeBerichteUndGutachtenSeeder::saetze());

        $html = $this->get('/medizinische-berichte-und-gutachten')->getContent();

        $this->assertSame(115, substr_count($html, 'data-glossar-eintrag'));
        $this->assertStringContainsString('id="anamnese"', $html);
        $this->assertStringContainsString('Das ist die Krankengeschichte.', $html);
        $this->assertStringNotContainsString('Einfach erklärt', $html);

        // Begriffe, die es schon gibt, bekommen ein eigenes Sprungziel.
        $this->assertStringContainsString('id="teilhabe-medizin"', $html);
        $this->assertStringContainsString('id="teilhabe"', $this->get('/sozialrecht-und-hilfesystem')->getContent());

        // Typische Sätze darunter, mit Sprung aus dem Glossar.
        $this->assertSame(60, substr_count($html, 'Einfach übersetzt:'));
        $this->assertMatchesRegularExpression('/typische Formulierungen aus <strong[^>]*>Arztbriefen, Befundberichten und Gutachten<\/strong>/u', $html);
        $this->assertStringContainsString('„Die Beschwerden sind nicht objektivierbar.“', $html);
        $this->assertStringContainsString('href="#typische-saetze"', $html);
    }

    public function test_websuche_findet_die_seite(): void
    {
        $treffer = collect(app(Suche::class)->suchen('Anamnese')['treffer'])
            ->firstWhere('url', '/medizinische-berichte-und-gutachten#anamnese');

        $this->assertNotNull($treffer);
        $this->assertSame('Medizinische Berichte & Gutachten', $treffer['bereich']);
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        GlossaryTerm::where('slug', 'befund')->update(['erklaerung' => 'Vom Verein geändert.']);

        MedizinischeBerichteUndGutachtenSeeder::anlegen();
        (require database_path('migrations/2026_10_25_140000_medizinische_berichte_und_gutachten.php'))->up();

        $seite = Page::where('slug', 'medizinische-berichte-und-gutachten')->where('locale', 'de')->sole();
        $this->assertSame(['text', 'glossar', 'typische_saetze'], $seite->blocks()->orderBy('position')->pluck('typ')->all());
        $this->assertSame(1, GlossaryTerm::where('slug', 'befund')->where('locale', 'de')->count());
        $this->assertSame('Vom Verein geändert.', GlossaryTerm::where('slug', 'befund')->where('locale', 'de')->value('erklaerung'));
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/medizinische-berichte-und-gutachten')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Medical reports &amp; expert opinions\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Understand findings. Make better sense of expert opinions.', $html);
        $this->assertStringContainsString('What medical reports really say', $html);
        $this->assertStringContainsString('Medical history (Anamnese)', $html);
        $this->assertSame(115, substr_count($html, 'data-glossar-eintrag'));
        $this->assertSame(60, substr_count($html, 'In plain words:'));
    }
}
