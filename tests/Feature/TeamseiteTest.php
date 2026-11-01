<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\TeamseiteSeeder;
use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Seite „Vorstand und Team“ nach Taddis KEV-294.
 */
class TeamseiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    private function seite(): Page
    {
        return Page::where('slug', TeamseiteSeeder::SLUG)->where('locale', 'de')->first();
    }

    /** Der Abschnitt mit „Auf dieser Seite“ bis zum Ende seiner section. */
    private function einleitung(string $html): string
    {
        $titel = strpos($html, 'WIR – mit Herz, Haltung und Vision');
        $abschnitt = substr($html, strrpos(substr($html, 0, $titel), '<section'));

        return substr($abschnitt, 0, strpos($abschnitt, '</section>'));
    }

    /** KEV-294: Einleitung links, rechts daneben Vorstand, Team, Herr und Frau Unbekannt. */
    public function test_verzeichnis_steht_neben_der_einleitung(): void
    {
        $html = $this->get('/'.TeamseiteSeeder::SLUG)->assertOk()->getContent();
        $abschnitt = $this->einleitung($html);

        $this->assertMatchesRegularExpression('#<h2[^>]*>\s*WIR – mit Herz, Haltung und Vision\s*</h2>#', $abschnitt);
        $this->assertStringContainsString(TeamseiteSeeder::SCHLUSS, $abschnitt);
        $this->assertStringContainsString('lg:grid-cols-[minmax(0,65ch)_minmax(0,1fr)]', $html);

        // Nach dem Text, im selben Abschnitt: genau die drei, ohne Spenden.
        $nav = substr($abschnitt, strpos($abschnitt, '<nav aria-label="Auf dieser Seite"'));
        preg_match_all('#<a href="\#([^"]+)"[^>]*>\s*([^<]+?)\s*</a>#', $nav, $treffer);
        $this->assertSame(['team-vorstand-titel', 'abschnitt-team', 'abschnitt-herr-und-frau-unbekannt'], $treffer[1]);
        $this->assertSame(['Vorstand', 'Team', 'Herr und Frau Unbekannt'], $treffer[2]);
        $this->assertGreaterThan(strpos($abschnitt, TeamseiteSeeder::SCHLUSS), strpos($abschnitt, '<nav'));

        // Jedes Ziel gibt es, und der Kasten über dem Inhalt fällt weg.
        foreach ($treffer[1] as $anker) {
            $this->assertStringContainsString('id="'.$anker.'"', $html);
        }
        $this->assertSame(1, substr_count($html, '<nav aria-label="Auf dieser Seite"'));
        $this->assertStringNotContainsString('Unser Team – mit Herz', $html);
    }

    /** Ohne den Schalter bleibt alles wie vorher: Kasten oben, mit Spenden. */
    public function test_ohne_schalter_bleibt_der_kasten_oben(): void
    {
        $einleitung = $this->seite()->blocks()->where('typ', 'text')->orderBy('position')->first();
        $einleitung->update(['data' => array_diff_key($einleitung->data, ['verzeichnis_daneben' => true])]);

        $html = $this->get('/'.TeamseiteSeeder::SLUG)->assertOk()->getContent();
        $nav = substr($html, strpos($html, '<nav aria-label="Auf dieser Seite"'));
        $nav = substr($nav, 0, strpos($nav, '</nav>'));

        $this->assertStringContainsString('WIR – mit Herz, Haltung und Vision', $nav);
        $this->assertStringContainsString('Jetzt spenden', $nav);
        $this->assertStringNotContainsString(TeamseiteSeeder::SCHLUSS, substr($html, 0, strpos($html, '<nav aria-label="Auf dieser Seite"')));
    }

    /** Bestehende Seiten bekommen es per Migration, Englisch mit. Geändertes bleibt. */
    public function test_migration(): void
    {
        $seite = $this->seite();
        $einleitung = $seite->blocks()->where('typ', 'text')->orderBy('position')->first();
        $vorstand = $seite->blocks()->where('typ', 'team_grid')->get()->first(fn ($b) => ($b->data['bereich'] ?? null) === 'Vorstand');
        $alt = $einleitung->data['absaetze'];
        array_pop($alt);
        $einleitung->update(['data' => ['titel' => TeamseiteSeeder::ALTER_TITEL, 'absaetze' => $alt]]);
        $vorstand->update(['data' => ['titel' => 'Vorstandsebene', 'bereich' => 'Vorstand']]);

        $migration = require database_path('migrations/2026_11_01_120000_teamseite_verzeichnis_daneben.php');
        $migration->up();
        $migration->up();

        $data = $einleitung->fresh()->data;
        $this->assertSame(TeamseiteSeeder::TITEL, $data['titel']);
        $this->assertSame([...$alt, TeamseiteSeeder::SCHLUSS], $data['absaetze']);
        $this->assertTrue($data['verzeichnis_daneben']);
        $this->assertSame('Vorstand', $vorstand->fresh()->data['verzeichnis_titel']);

        $woerterbuch = json_decode(file_get_contents(database_path('seeders/data/uebersetzungen.json')), true);
        foreach ([TeamseiteSeeder::TITEL, TeamseiteSeeder::SCHLUSS, TeamseiteSeeder::VORSTAND_IM_VERZEICHNIS] as $text) {
            $this->assertArrayHasKey($text, $woerterbuch);
        }

        // Eine im Panel geänderte Überschrift bleibt.
        $einleitung->update(['data' => array_replace($einleitung->data, ['titel' => 'Eigene Überschrift'])]);
        $migration->up();
        $this->assertSame('Eigene Überschrift', $einleitung->fresh()->data['titel']);
    }

    /** Baut der TeamUndGruppenSeeder die Seite neu (Nils' Review auf frischer DB), bleibt es. */
    public function test_neuaufbau_der_teamseite_behaelt_es(): void
    {
        (new TeamUndGruppenSeeder)->teamseiteAufbauen();

        $html = $this->get('/'.TeamseiteSeeder::SLUG)->assertOk()->getContent();
        $abschnitt = $this->einleitung($html);
        $this->assertMatchesRegularExpression('#<nav aria-label="Auf dieser Seite".*?href="\#team-vorstand-titel"[^>]*>\s*Vorstand\s*</a>#s', $abschnitt);
        $this->assertStringContainsString(TeamseiteSeeder::SCHLUSS, $html);
    }
}
