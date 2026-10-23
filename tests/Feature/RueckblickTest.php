<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\RueckblickSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-142: Rückblick mit dem Abschnitt „KE!N EINZELFALL im Dialog – Trauma,
 * Bindung und Beziehung“ und Taddis Bild. KEV-141: dazu „Unsere
 * Veranstaltungen zum Fonds Sexueller Missbrauch (FSM)“ mit ihrem P.S.
 */
class RueckblickTest extends TestCase
{
    use RefreshDatabase;

    /** Die FSM-Seite, auf die das P.S. verlinkt, kommt aus dem Altbestand. */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_abschnitt_mit_taddis_text_und_bild(): void
    {
        $html = $this->get('/rueckblick')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h2[^>]*>\s*KE!N EINZELFALL im Dialog – Trauma, Bindung und Beziehung\s*<\/h2>/u', $html);
        $this->assertStringContainsString('startete am 10. August 2026 unsere Veranstaltungsreihe', $html);
        $this->assertStringContainsString('Als Referenten durften wir Lutz-Ulrich Besser, Facharzt für Psychiatrie und Psychotherapie', $html);
        $this->assertStringContainsString('Fragen konnten auch ausschließlich über den Chat gestellt werden.', $html);
        // Komma ergänzt
        $this->assertStringContainsString('Mit den Fragen, die Menschen, die mit den Folgen von Trauma leben, beschäftigen.', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>KE!N EINZELFALL im Dialog – Wissen trifft Erfahrung\.<\/strong>/u', $html);

        // Stimmungsbild ohne Bildbeschreibung
        $this->assertStringContainsString('src="/img/rueckblick/trauma-bindung-und-beziehung.webp?v=', $html);
        $this->assertMatchesRegularExpression('/<img src="\/img\/rueckblick\/trauma-bindung-und-beziehung\.webp[^"]*" alt=""/', $html);
        $this->assertFileExists(public_path('img/rueckblick/trauma-bindung-und-beziehung.webp'));

        // Langer Text: Bild oben und mitlaufend, nach vier Absätzen eingeklappt
        $this->assertStringContainsString('lg:sticky', $html);
        $this->assertStringContainsString('Weiterlesen (4 weitere Absätze)', $html);
    }

    public function test_fsm_abschnitt_mit_taddis_text_bild_und_ps(): void
    {
        $html = $this->get('/rueckblick')->getContent();

        $this->assertMatchesRegularExpression('/<h2[^>]*>\s*Unsere Veranstaltungen zum Fonds Sexueller Missbrauch \(FSM\)\s*<\/h2>/u', $html);
        // Neueste Veranstaltung oben
        $this->assertLessThan(strpos($html, 'Unsere Veranstaltungen zum Fonds'), strpos($html, 'im Dialog – Trauma, Bindung und Beziehung'));

        $this->assertStringContainsString('haben viele Betroffene nicht nur verunsichert – sie haben Vertrauen erschüttert.', $html);
        $this->assertStringContainsString('zum Thema „FSM vs. offene Fragen“ angeboten', $html);
        $this->assertStringContainsString('Dann kam der 24. Juni 2025.', $html);
        $this->assertStringContainsString('Hilfe darf nicht selbst zur nächsten Belastung werden.', $html);
        // Korrigiert
        $this->assertStringContainsString('–, und dass ab sofort keine weiteren Neuanträge mehr möglich waren.', $html);
        $this->assertStringContainsString('So setzten wir im November 2025 unsere Veranstaltungen fort.', $html);
        $this->assertStringContainsString('Aus einer geplanten Ausfüllhilfe-Veranstaltung', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Gemeinsam KE!N EINZELFALL\.<\/strong>/u', $html);
        $this->assertStringContainsString('Weiterlesen (13 weitere Absätze)', $html);

        // Bild links, die Abschnitte wechseln sich ab
        $this->assertMatchesRegularExpression('/class="order-1 lg:order-1[^"]*"[^>]*>\s*<img src="\/img\/rueckblick\/fsm\.webp/', $html);
        $this->assertMatchesRegularExpression('/class="order-1 lg:order-2[^"]*"[^>]*>\s*<img src="\/img\/rueckblick\/trauma/', $html);

        // Ihr P.S. als Kasten, der Link führt zu den Musterwidersprüchen
        $this->assertStringContainsString('Ablehnungsbescheid bekommen?', $html);
        $this->assertStringContainsString('Ablehnungsbescheid zum behinderungsbedingten Mehrbedarf', $html);
        $this->assertStringContainsString('href="/fsm-erweitertes-hilfesystem#dokumente"', $html);
        $this->assertStringNotContainsString('LINK zu den Widersprüchen', $html);

        $ziel = $this->get('/fsm-erweitertes-hilfesystem')->getContent();
        $this->assertStringContainsString('id="dokumente"', $ziel);
        $this->assertStringContainsString('Musterwiderspruch', $ziel);
    }

    public function test_mit_inhalt_fuer_suchmaschinen_offen(): void
    {
        $seite = Page::where('slug', 'rueckblick')->where('locale', 'de')->firstOrFail();
        $this->assertFalse($seite->noindex);
        $this->assertStringContainsString('/rueckblick</loc>', $this->get('/sitemap.xml')->getContent());
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'rueckblick')->where('locale', 'de')->firstOrFail();
        $block = $seite->blocks()->firstOrFail();
        $block->update(['data' => array_merge($block->data, ['absaetze' => ['Vom Verein geändert.']])]);

        RueckblickSeeder::anlegen();
        (require database_path('migrations/2026_10_23_180000_rueckblick_trauma_bindung.php'))->up();

        (require database_path('migrations/2026_10_23_190000_rueckblick_fsm.php'))->up();

        // Trauma, FSM, Hinweis
        $this->assertSame(3, $seite->blocks()->count());
        $this->assertSame(['Vom Verein geändert.'], $block->fresh()->data['absaetze']);
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/rueckblick')->assertOk()->getContent();

        $this->assertStringContainsString('KE!N EINZELFALL im Dialog – Trauma, attachment and relationships', $html);
        $this->assertStringContainsString('our event series “KE!N EINZELFALL im Dialog – Knowledge meets experience” began on 10 August 2026', $html);
        $this->assertStringContainsString('/img/rueckblick/trauma-bindung-und-beziehung.webp', $html);

        $this->assertStringContainsString('Our events on the Fund for Sexual Abuse', $html);
        $this->assertStringContainsString('Received a rejection notice?', $html);
        $this->assertStringContainsString('href="/en/fsm-erweitertes-hilfesystem#dokumente"', $html);
        $this->assertStringContainsString('id="dokumente"', $this->get('/en/fsm-erweitertes-hilfesystem')->getContent());
    }
}
