<?php

namespace Tests\Feature;

use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Support\Suche;
use Database\Seeders\AbkuerzungenSexuellerKontextSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-154: Glossar „Abkürzungen im sexuellen Kontext“ unter „Wissen“, mit
 * eigener Triggerwarnung vor der Seite.
 */
class AbkuerzungenSexuellerKontextTest extends TestCase
{
    use RefreshDatabase;

    private const ADRESSE = '/abkuerzungen-im-sexuellen-kontext';

    public function test_seite_unter_wissen_zwischen_den_glossaren(): void
    {
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $urls = array_column($wissen['children'], 'url');
        $hier = array_search(self::ADRESSE, $urls);

        $this->assertSame('/glossar', $urls[$hier - 1]);
        $this->assertSame('/behoerden-und-bescheide', $urls[$hier + 1]);

        $html = $this->get(self::ADRESSE)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Abkürzungen im sexuellen Kontext\s*<\/h1>/u', $html);
        $this->assertMatchesRegularExpression('/aria-label="Sie sind hier".*Wissen.*Abkürzungen im sexuellen Kontext/s', $html);
        $this->assertStringContainsString('src="/img/titelbilder/abkuerzungen-im-sexuellen-kontext.webp?v=', $html);
        $this->assertStringContainsString('object-position: 0% 85%', $html);
        $this->assertSame([2000, 776], array_slice(getimagesize(public_path('img/titelbilder/abkuerzungen-im-sexuellen-kontext.webp')), 0, 2));
        $this->assertStringContainsString(self::ADRESSE.'</loc>', $this->get('/sitemap.xml')->getContent());
    }

    public function test_einleitung_von_taddi(): void
    {
        $html = $this->get(self::ADRESSE)->getContent();

        $this->assertStringContainsString('Gerade im Bereich Sexdienstleistungen, Sexualität und BDSM begegnen einem viele Abkürzungen', $html);
        $this->assertStringContainsString('Uns ist wichtig, dabei nicht zu bewerten, sondern verständlich zu machen, was gemeint ist.', $html);
        // „e. V.“ wie überall sonst als „e.V.“
        $this->assertStringContainsString('KE!N EINZELFALL e.V. unterstützt Menschen aus der Sexdienstleistungsbranche', $html);
        $this->assertStringNotContainsString('e. V. unterstützt', $html);
    }

    /** Bei Taddi steht „TRIGGERWARNUNG“ über der Liste; sie steht vor der Seite. */
    public function test_triggerwarnung_vor_der_seite(): void
    {
        $seite = Page::where('slug', 'abkuerzungen-im-sexuellen-kontext')->where('locale', 'de')->firstOrFail();
        $this->assertSame(AbkuerzungenSexuellerKontextSeeder::TRIGGERWARNUNG, $seite->triggerwarnung);

        $html = $this->get(self::ADRESSE)->getContent();
        $this->assertStringContainsString('data-bereich="abkuerzungen-im-sexuellen-kontext"', $html);
        $this->assertStringContainsString('Manche Erklärungen beschreiben sexuelle Praktiken sehr direkt.', $html);
        $this->assertLessThan(strpos($html, '<h1'), strpos($html, '<dialog id="bereichswarnung"'));

        // Nur vor dieser Seite, nicht vor den anderen Glossaren oder „Wissen“.
        foreach (['/glossar', '/behoerden-und-bescheide', '/wissen'] as $adresse) {
            $this->assertStringNotContainsString('id="bereichswarnung"', $this->get($adresse)->getContent(), $adresse);
        }
    }

    public function test_alle_abkuerzungen_mit_aufloesung_und_erklaerung(): void
    {
        $this->assertCount(109, AbkuerzungenSexuellerKontextSeeder::eintraege());

        $html = $this->get(self::ADRESSE)->getContent();

        $this->assertSame(109, substr_count($html, 'data-glossar-eintrag'));
        $this->assertStringContainsString('Abkürzung oder Begriff suchen', $html);

        // Geteilt am Doppelpunkt: Auflösung neben der Abkürzung, die Erklärung darunter.
        $this->assertMatchesRegularExpression('/<abbr title="After Sex Talk"[^>]*>AST<\/abbr>/', $html);
        $this->assertStringContainsString('Nachgespräch nach sexueller Aktivität', $html);
        // … sonst am Semikolon.
        $this->assertMatchesRegularExpression('/<dd class="hyphens-auto text-ink">Anal-Zungen-Fick<\/dd>/', $html);

        // Doppelte Abkürzungen mit eigenem Sprungziel.
        $this->assertStringContainsString('id="ao-alles-ohne"', $html);
        $this->assertStringContainsString('id="ao-anal-dann-oral"', $html);
        // „BBB“ gibt es im ersten Glossar schon, beide bleiben verlinkbar.
        $this->assertStringContainsString('id="bbb-bauch-bart-und-brille"', $html);
        $this->assertStringContainsString('id="bbb"', $this->get('/glossar')->getContent());

        // Die Glossare mischen sich nicht.
        $this->assertStringNotContainsString('id="gdb"', $html);
        $this->assertStringNotContainsString('id="cim"', $this->get('/glossar')->getContent());
    }

    public function test_websuche_fuehrt_hierher(): void
    {
        $treffer = collect(app(Suche::class)->suchen('BDSM')['treffer'])->firstWhere('art', 'glossar');

        $this->assertSame(self::ADRESSE.'#bdsm', $treffer['url']);
        $this->assertSame('Abkürzungen im sexuellen Kontext', $treffer['bereich']);
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'abkuerzungen-im-sexuellen-kontext')->where('locale', 'de')->firstOrFail();
        $seite->update(['triggerwarnung' => ['Vom Verein geändert.']]);
        GlossaryTerm::where('slug', 'cim')->update(['erklaerung' => 'Auch geändert.']);

        AbkuerzungenSexuellerKontextSeeder::anlegen();
        (require database_path('migrations/2026_10_23_140000_abkuerzungen_sexueller_kontext.php'))->up();

        $this->assertSame(1, Page::where('slug', 'abkuerzungen-im-sexuellen-kontext')->where('locale', 'de')->count());
        $this->assertSame(109, GlossaryTerm::aufSeite($seite)->where('locale', 'de')->count());
        $this->assertSame(['Vom Verein geändert.'], $seite->fresh()->triggerwarnung);
        $this->assertSame('Auch geändert.', GlossaryTerm::where('slug', 'cim')->where('locale', 'de')->value('erklaerung'));
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en'.self::ADRESSE)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Abbreviations in a sexual context\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Some explanations describe sexual practices very directly.', $html);
        $this->assertStringContainsString('Everything without (Alles ohne)', $html);
        $this->assertSame(109, substr_count($html, 'data-glossar-eintrag'));
    }
}
