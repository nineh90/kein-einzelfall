<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use App\Support\Suche;
use Database\Seeders\BehoerdenUndBescheideSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * KEV-262: Taddis „Typische Sätze – einfach übersetzt“ sind keine eigene
 * Kategorie, sondern ein Baustein unter dem Glossar ihrer Kategorie.
 */
class TypischeSaetzeTest extends TestCase
{
    use RefreshDatabase;

    public function test_saetze_unter_dem_glossar_behoerden_und_bescheide(): void
    {
        $this->assertCount(47, BehoerdenUndBescheideSeeder::saetze());

        $seite = Page::where('slug', 'behoerden-und-bescheide')->where('locale', 'de')->firstOrFail();
        $this->assertSame(['text', 'glossar', 'typische_saetze'], $seite->blocks()->orderBy('position')->pluck('typ')->all());

        $html = $this->get('/behoerden-und-bescheide')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="typische-saetze".*<h2[^>]*>\s*Typische Sätze – einfach übersetzt/su', $html);
        $this->assertSame(47, substr_count($html, 'Einfach übersetzt:'));
        $this->assertStringContainsString('„Ihrem Antrag konnte nicht entsprochen werden.“', $html);
        $this->assertStringContainsString('Dein Antrag wurde ganz oder teilweise abgelehnt.', $html);
        $this->assertStringContainsString('„Die Kostenentscheidung beruht auf …“', $html);

        // Taddis Einleitung, die Kategorie fett eingesetzt, „da“ → „dar“.
        $this->assertMatchesRegularExpression('/typische Formulierungen aus <strong[^>]*>Bescheiden und Behördenschreiben<\/strong>/u', $html);
        $this->assertStringContainsString('Dies stellt keine Rechtsberatung dar.', $html);
        $this->assertStringContainsString('Fehlt Dir hier eine Formulierung?', $html);

        // Die Begriffe zählen weiter nur Begriffe.
        $this->assertSame(128, substr_count($html, 'data-glossar-eintrag'));
    }

    /** Hinter 128 Begriffen fände sonst niemand die Sätze. */
    public function test_glossar_springt_zu_den_saetzen(): void
    {
        $html = $this->get('/behoerden-und-bescheide')->getContent();
        $this->assertMatchesRegularExpression('/<a href="#typische-saetze"[^>]*>\s*Zu den typischen Sätzen/u', $html);

        // Ohne Sätze kein Sprung ins Leere.
        $this->assertStringNotContainsString('Zu den typischen Sätzen', $this->get('/glossar')->getContent());
    }

    public function test_websuche_findet_einen_satz(): void
    {
        $treffer = collect(app(Suche::class)->suchen('nicht entsprochen')['treffer'])
            ->firstWhere('url', '/behoerden-und-bescheide');

        $this->assertNotNull($treffer);
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'behoerden-und-bescheide')->where('locale', 'de')->firstOrFail();
        $block = $seite->blocks()->where('typ', 'typische_saetze')->firstOrFail();
        $block->update(['data' => ['titel' => 'Vom Verein geändert', 'formulierungen' => []]]);

        BehoerdenUndBescheideSeeder::saetzeAnlegen();
        (require database_path('migrations/2026_10_25_120000_typische_saetze_behoerden.php'))->up();

        $this->assertSame(1, $seite->blocks()->where('typ', 'typische_saetze')->count());
        $this->assertSame('Vom Verein geändert', $block->fresh()->data['titel']);
    }

    /** Der Verein pflegt die Sätze im Panel; Speichern lässt sie, wie sie sind. */
    public function test_im_panel_pflegbar(): void
    {
        $seite = Page::where('slug', 'behoerden-und-bescheide')->where('locale', 'de')->firstOrFail();
        $vorher = $seite->blocks()->where('typ', 'typische_saetze')->firstOrFail()->data;

        Livewire::actingAs(User::factory()->redaktion()->create())
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($vorher, $seite->blocks()->where('typ', 'typische_saetze')->firstOrFail()->data);
    }

    /** Der Satz bleibt deutsch, so steht er im Brief; übersetzt wird die Erklärung. */
    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/behoerden-und-bescheide')->assertOk()->getContent();

        $this->assertStringContainsString('Typical sentences – in plain words', $html);
        $this->assertSame(47, substr_count($html, 'In plain words:'));
        $this->assertSame(47, preg_match_all('/<dt\s+lang="de"/', $html));
        $this->assertStringContainsString('„Ihrem Antrag konnte nicht entsprochen werden.“', $html);
        $this->assertStringContainsString('Your application was rejected in whole or in part.', $html);
        $this->assertStringContainsString('To the typical sentences', $html);
    }
}
