<?php

namespace Tests\Feature;

use App\Filament\Resources\GlossaryTerms\Pages\CreateGlossaryTerm;
use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Models\User;
use App\Support\Suche;
use Database\Seeders\BehoerdenUndBescheideSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * KEV-155: zweites Glossar „Behörden & Bescheide“ unter „Wissen“, und die
 * Glossar-Liste als Baustein, der auf jeder Seite stehen kann.
 */
class BehoerdenUndBescheideTest extends TestCase
{
    use RefreshDatabase;

    public function test_eigene_seite_unter_wissen_neben_dem_glossar(): void
    {
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $urls = array_column($wissen['children'], 'url');
        // Taddis Nummern: 1. Glossar, 2. sexueller Kontext (KEV-154), 3. diese.
        $this->assertSame(array_search('/glossar', $urls) + 2, array_search('/behoerden-und-bescheide', $urls));

        $html = $this->get('/behoerden-und-bescheide')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Behörden &amp; Bescheide - Kein Einzelfall e.V.</title>', $html);
        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Behörden &amp; Bescheide\s*<\/h1>/u', $html);
        $this->assertMatchesRegularExpression('/aria-label="Sie sind hier".*Wissen.*Behörden &amp; Bescheide/s', $html);
        $this->assertStringContainsString('src="/img/titelbilder/platzhalter.webp?v=', $html);
        $this->assertStringContainsString('/behoerden-und-bescheide</loc>', $this->get('/sitemap.xml')->getContent());
    }

    public function test_einleitung_von_taddi(): void
    {
        $html = $this->get('/behoerden-und-bescheide')->getContent();

        $this->assertStringContainsString('Hier findest du typische Begriffe aus Bescheiden und Behördenschreiben', $html);
        foreach (['Anhörung', 'Mitwirkungspflicht', 'Widerspruchsbescheid', 'Fehlt dir hier etwas?', 'Gemeinsam KE!N EINZELFALL'] as $fett) {
            $this->assertMatchesRegularExpression('/<strong[^>]*>'.preg_quote($fett, '/').'<\/strong>/u', $html, $fett);
        }
        $this->assertMatchesRegularExpression('/<strong[^>]*><a href="mailto:kontakt@kein-einzelfall\.de"/', $html);
    }

    public function test_alle_begriffe_mit_erklaerung_und_nur_diese(): void
    {
        $daten = BehoerdenUndBescheideSeeder::eintraege();
        $this->assertCount(128, $daten);

        $html = $this->get('/behoerden-und-bescheide')->getContent();

        $this->assertSame(128, substr_count($html, 'data-glossar-eintrag'));
        $this->assertStringContainsString('128 Einträge', $html);
        // Nur Begriffe, keine Abkürzungen: Das Suchfeld sagt es auch so.
        $this->assertStringContainsString('>Begriff suchen<', str_replace(["\n", '  '], '', $html));
        $this->assertStringContainsString('id="widerspruchsbescheid"', $html);
        $this->assertStringContainsString('Das ist die Entscheidung darüber, ob die Behörde nach deinem Widerspruch', $html);
        // Taddis „Einfach erklärt:“ vor jeder Erklärung ist weggelassen.
        $this->assertStringNotContainsString('Einfach erklärt', $html);

        // Die beiden Glossare mischen sich nicht.
        $this->assertStringNotContainsString('id="gdb"', $html);
        $glossar = $this->get('/glossar')->getContent();
        $this->assertStringContainsString('id="gdb"', $glossar);
        $this->assertStringNotContainsString('id="widerspruchsbescheid"', $glossar);
        $this->assertStringContainsString('Abkürzung oder Begriff suchen', $glossar);
    }

    /** Ohne JavaScript filtert der Server, mit Rücksprung auf diese Seite. */
    public function test_suche_auf_der_seite(): void
    {
        $seite = Page::where('slug', 'behoerden-und-bescheide')->where('locale', 'de')->firstOrFail();
        $passend = GlossaryTerm::aufSeite($seite)->where('locale', 'de')->get()->filter->passt('frist')->count();
        $this->assertGreaterThanOrEqual(5, $passend);

        $html = $this->get('/behoerden-und-bescheide?suche=frist')->assertOk()->getContent();

        $this->assertStringContainsString("Treffer: {$passend} von 128", $html);
        $this->assertStringContainsString('action="/behoerden-und-bescheide"', $html);
        $this->assertMatchesRegularExpression('/<div id="zahlungsfrist"\s+data-glossar-eintrag[^>]*\s+class=/', $html);
        $this->assertMatchesRegularExpression('/<div id="akte"\s+data-glossar-eintrag[^>]*hidden/', $html);
    }

    /** Die Suche der Website führt auf die Seite, auf der der Begriff steht. */
    public function test_websuche_kennt_beide_glossare(): void
    {
        $treffer = collect(app(Suche::class)->suchen('Widerspruchsbescheid')['treffer'])->firstWhere('art', 'glossar');

        $this->assertSame('/behoerden-und-bescheide#widerspruchsbescheid', $treffer['url']);
        $this->assertSame('Behörden & Bescheide', $treffer['bereich']);

        $imGlossar = collect(app(Suche::class)->suchen('Frist')['im_glossar']);
        $this->assertSame('/behoerden-und-bescheide', $imGlossar->first()['url']);

        $html = $this->get('/suche?q=Frist')->getContent();
        $this->assertStringContainsString('href="/behoerden-und-bescheide?suche=Frist"', $html);
        $this->assertMatchesRegularExpression('/Alle \d+ passenden Einträge in „Behörden &amp; Bescheide“/u', $html);
    }

    /** Ein neues Glossar ist eine Seite mit dem Baustein; die Einträge ordnet der Verein zu. */
    public function test_verein_legt_glossare_selbst_an(): void
    {
        $neu = Page::create(['slug' => 'abkuerzungen-probe', 'titel' => 'Probe', 'published_at' => now()]);
        $neu->blocks()->create(['typ' => 'glossar', 'position' => 0, 'data' => []]);

        $this->assertStringContainsString('Hier entsteht ein Verzeichnis', $this->get('/abkuerzungen-probe')->getContent());
        $this->assertSame(
            ['Abkürzungen im sexuellen Kontext', 'Behörden & Bescheide', 'Gericht & Verfahren', 'Glossar',
                'Medizinische Berichte & Gutachten', 'Probe',
                'Sozialrecht & Hilfesystem'],
            array_values(GlossaryTerm::seitenAuswahl()),
        );

        Livewire::actingAs(User::factory()->redaktion()->create())
            ->test(CreateGlossaryTerm::class)
            ->assertSchemaStateSet(['seite_gruppe' => GlossaryTerm::hauptseitenGruppe()])
            ->fillForm([
                'begriff' => 'Probebegriff', 'slug' => 'probebegriff', 'erklaerung' => 'Nur zum Testen.',
                'seite_gruppe' => $neu->uebersetzungs_gruppe,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertStringContainsString('id="probebegriff"', $this->get('/abkuerzungen-probe')->getContent());
        $this->assertStringNotContainsString('id="probebegriff"', $this->get('/glossar')->getContent());
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        GlossaryTerm::where('slug', 'frist')->update(['erklaerung' => 'Vom Verein geändert.']);

        BehoerdenUndBescheideSeeder::anlegen();
        (require database_path('migrations/2026_10_23_120100_behoerden_und_bescheide.php'))->up();

        $this->assertSame(1, Page::where('slug', 'behoerden-und-bescheide')->where('locale', 'de')->count());
        $this->assertSame(1, GlossaryTerm::where('slug', 'frist')->where('locale', 'de')->count());
        $this->assertSame('Vom Verein geändert.', GlossaryTerm::where('slug', 'frist')->where('locale', 'de')->value('erklaerung'));
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/behoerden-und-bescheide')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Authorities &amp; official notices\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Here you will find typical terms from official notices', $html);
        $this->assertStringContainsString('Objection (Widerspruch)', $html);
        $this->assertStringContainsString('Search for a term', $html);
        $this->assertSame(128, substr_count($html, 'data-glossar-eintrag'));
        $this->assertStringContainsString('action="/en/behoerden-und-bescheide"', $html);

        // Die Suche auf Englisch führt in die englische Seite. Seit KEV-259
        // steht „Widerspruch“ auch unter „Gericht & Verfahren“.
        $urls = collect(app(Suche::class)->suchen('Objection', 'en')['treffer'])
            ->where('titel', 'Objection (Widerspruch)')->pluck('url');
        $this->assertContains('/en/behoerden-und-bescheide#widerspruch', $urls);
        $this->assertContains('/en/gericht-und-verfahren#widerspruch-gericht', $urls);
    }
}
