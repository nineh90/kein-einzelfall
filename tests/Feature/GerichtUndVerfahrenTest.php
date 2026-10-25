<?php

namespace Tests\Feature;

use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Support\Suche;
use Database\Seeders\GerichtUndVerfahrenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-259: Glossar „Gericht & Verfahren“ unter „Wissen“, mit Bild,
 * Unterzeile und grüner Überschrift aus dem Ticket und Taddis typischen
 * Sätzen darunter (KEV-262).
 */
class GerichtUndVerfahrenTest extends TestCase
{
    use RefreshDatabase;

    public function test_eigene_seite_unter_wissen_zwischen_behoerden_und_medizin(): void
    {
        // Seit KEV-254 unter „Klartext statt Fachsprache“ im Bereich Wissen.
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $klartext = collect($wissen['children'])->firstWhere('url', '/klartext-statt-fachsprache');
        $urls = array_column($klartext['children'], 'url');
        // Taddis 3, 4, 5, 6 hintereinander.
        $this->assertSame(
            ['/behoerden-und-bescheide', '/gericht-und-verfahren', '/medizinische-berichte-und-gutachten', '/sozialrecht-und-hilfesystem'],
            array_slice($urls, array_search('/behoerden-und-bescheide', $urls), 4),
        );

        $html = $this->get('/gericht-und-verfahren')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Gericht &amp; Verfahren - Kein Einzelfall e.V.</title>', $html);
        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Gericht &amp; Verfahren\s*<\/h1>/u', $html);
        $this->assertMatchesRegularExpression('/aria-label="Sie sind hier".*Wissen.*Gericht &amp; Verfahren/s', $html);
        $this->assertStringContainsString('/gericht-und-verfahren</loc>', $this->get('/sitemap.xml')->getContent());
    }

    public function test_bild_unterzeile_und_gruene_ueberschrift_aus_dem_ticket(): void
    {
        $html = $this->get('/gericht-und-verfahren')->getContent();

        $this->assertStringContainsString('src="/img/titelbilder/gericht-und-verfahren.webp?v=', $html);
        $this->assertFileExists(public_path('img/titelbilder/gericht-und-verfahren-1000.webp'));
        $this->assertStringContainsString('Damit juristische Begriffe verständlicher werden', $html);
        $this->assertMatchesRegularExpression(
            '/<h2[^>]*text-green[^>]*>\s*Begriffe aus Widerspruchs-, Klage- und Gerichtsverfahren für dich\s*<\/h2>/u',
            $html,
        );
    }

    public function test_einleitung_von_taddi(): void
    {
        $html = $this->get('/gericht-und-verfahren')->getContent();

        $this->assertStringContainsString('mit Schwerpunkt auf Verfahren vor den Sozialgerichten.', $html);
        $this->assertStringContainsString('Sie ersetzen keine individuelle Rechtsberatung.', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*><a href="mailto:kontakt@kein-einzelfall\.de"/', $html);

        // Wie in den anderen Glossaren (bei Taddi vergessen).
        $this->assertMatchesRegularExpression('/<strong[^>]*>Fehlt Dir hier ein Begriff\?<\/strong>/u', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Gemeinsam KE!N EINZELFALL<\/strong>/u', $html);
    }

    /** Bestehende Datenbanken: nur der alte Wortlaut wird ersetzt. */
    public function test_migration_gleicht_nur_den_alten_wortlaut_an(): void
    {
        $block = Page::where('slug', 'gericht-und-verfahren')->where('locale', 'de')->sole()
            ->blocks()->where('typ', 'text')->sole();
        $block->update(['data' => ['absaetze' => GerichtUndVerfahrenSeeder::ABSAETZE_BIS_25_10]]);
        GlossaryTerm::where('slug', 'gutachten-nach-109-sgg')->update(['mehr_url' => null, 'mehr_label' => null]);

        $migration = require database_path('migrations/2026_10_25_160000_gericht_und_verfahren_angleichen.php');
        $migration->up();

        $this->assertSame(GerichtUndVerfahrenSeeder::ABSAETZE, $block->fresh()->data['absaetze']);
        $this->assertSame('/109-sgg-gegengutachten', GlossaryTerm::where('slug', 'gutachten-nach-109-sgg')->where('locale', 'de')->value('mehr_url'));

        // Was der Verein geändert hat, bleibt.
        $block->update(['data' => ['absaetze' => ['Vom Verein geändert.']]]);
        GlossaryTerm::where('slug', 'gutachten-nach-109-sgg')->update(['mehr_url' => '/anderswo']);
        $migration->up();

        $this->assertSame(['Vom Verein geändert.'], $block->fresh()->data['absaetze']);
        $this->assertSame('/anderswo', GlossaryTerm::where('slug', 'gutachten-nach-109-sgg')->where('locale', 'de')->value('mehr_url'));
    }

    /** Das Gutachten nach § 109 SGG führt zu unserem Projekt dazu. */
    public function test_verweis_auf_das_projekt_109_sgg(): void
    {
        $html = $this->get('/gericht-und-verfahren')->getContent();

        $this->assertMatchesRegularExpression(
            '/id="gutachten-nach-109-sgg".*?<a href="\/109-sgg-gegengutachten"[^>]*>\s*Zu unserem Projekt § 109 SGG/su',
            $html,
        );
        $this->get('/109-sgg-gegengutachten')->assertOk();
    }

    public function test_alle_begriffe_und_saetze(): void
    {
        $this->assertCount(87, GerichtUndVerfahrenSeeder::eintraege());
        $this->assertCount(53, GerichtUndVerfahrenSeeder::saetze());

        $html = $this->get('/gericht-und-verfahren')->getContent();

        $this->assertSame(87, substr_count($html, 'data-glossar-eintrag'));
        $this->assertStringContainsString('id="prozesskostenhilfe-pkh"', $html);
        $this->assertStringContainsString('kann unter den gesetzlichen Voraussetzungen Prozesskostenhilfe erhalten', $html);
        $this->assertStringNotContainsString('Einfach erklärt', $html);

        // Begriffe, die es schon gibt, bekommen ein eigenes Sprungziel.
        $this->assertStringContainsString('id="frist-gericht"', $html);
        $this->assertStringContainsString('id="frist"', $this->get('/behoerden-und-bescheide')->getContent());

        // Typische Sätze darunter, mit Sprung aus dem Glossar.
        $this->assertSame(53, substr_count($html, 'Einfach übersetzt:'));
        $this->assertMatchesRegularExpression('/typische Formulierungen aus <strong[^>]*>Klage-, Gerichts- und Rechtsmittelverfahren<\/strong>/u', $html);
        $this->assertStringContainsString('„Die Klage wird abgewiesen.“', $html);
        $this->assertStringContainsString('href="#typische-saetze"', $html);
    }

    public function test_websuche_findet_die_seite(): void
    {
        $treffer = collect(app(Suche::class)->suchen('Prozesskostenhilfe')['treffer'])
            ->firstWhere('url', '/gericht-und-verfahren#prozesskostenhilfe-pkh');

        $this->assertNotNull($treffer);
        $this->assertSame('Gericht & Verfahren', $treffer['bereich']);
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        GlossaryTerm::where('slug', 'klage')->update(['erklaerung' => 'Vom Verein geändert.']);

        GerichtUndVerfahrenSeeder::anlegen();
        (require database_path('migrations/2026_10_25_150000_gericht_und_verfahren.php'))->up();

        $seite = Page::where('slug', 'gericht-und-verfahren')->where('locale', 'de')->sole();
        $this->assertSame(['text', 'glossar', 'typische_saetze'], $seite->blocks()->orderBy('position')->pluck('typ')->all());
        $this->assertSame(1, GlossaryTerm::where('slug', 'klage')->where('locale', 'de')->count());
        $this->assertSame('Vom Verein geändert.', GlossaryTerm::where('slug', 'klage')->where('locale', 'de')->value('erklaerung'));
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/gericht-und-verfahren')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Courts &amp; proceedings\s*<\/h1>/u', $html);
        $this->assertStringContainsString('So that legal terms become easier to understand', $html);
        $this->assertStringContainsString('Terms from objection procedures, actions and court proceedings for you', $html);
        $this->assertStringContainsString('Legal aid – PKH (Prozesskostenhilfe)', $html);
        $this->assertSame(87, substr_count($html, 'data-glossar-eintrag'));
        $this->assertSame(53, substr_count($html, 'In plain words:'));
    }
}
