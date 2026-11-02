<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\VeranstaltungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /veranstaltungen nach Taddis Doc „3. Veranstaltungen“ (KEV-317).
 */
class VeranstaltungenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\CarbonImmutable::parse('2026-11-02 12:00', 'Europe/Berlin'));
        $this->seed(AltseiteSeeder::class);
        // Die Migration legt die Termine vor dem Zeitsprung an, mit „jetzt“.
        Event::query()->update(['published_at' => '2026-01-01 00:00:00']);
    }

    private function seite(): Page
    {
        return Page::where('slug', 'veranstaltungen')->where('locale', 'de')->firstOrFail();
    }

    /** Einleitung, Kalender, drei Reihen; die alten Abschnitte sind weg. */
    public function test_aufbau_der_seite(): void
    {
        $this->assertSame(
            ['text', 'download_list', 'kalender', 'veranstaltungsreihe', 'veranstaltungsreihe', 'veranstaltungsreihe'],
            $this->seite()->blocks()->orderBy('position')->pluck('typ')->all());

        $html = $this->get('/veranstaltungen')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#Manche Themen brauchen.*?Gemeinsam KE!N EINZELFALL.*?id="kalender".*?Wissen trifft Erfahrung.*?Frage trifft Antwort.*?zu Gast#s', $html);
        $this->assertStringNotContainsString('So finden unsere Veranstaltungen statt', $html);
        // Die Listen unten doppelten Kalender und Reihen.
        $this->assertStringNotContainsString('Regelmäßige Gruppentreffen', $html);
        $this->assertStringNotContainsString('id="einzeltermine-titel"', $html);
        // Die Dokumente zur Teilnahme stehen neben der Einleitung.
        $this->assertStringContainsString('Teilnahmevereinbarung', $html);
    }

    /** Die Termine mit Datum sind Veranstaltungen, die ohne stehen als „geplant“ bei ihrer Reihe. */
    public function test_termine_bei_ihren_reihen(): void
    {
        $this->assertSame(8, Event::count());
        $this->assertSame(4, Event::where('reihe', 'dialog-frage')->count());

        $html = $this->get('/veranstaltungen')->assertOk()->getContent();
        $wissen = substr($html, strpos($html, 'id="reihe-dialog-wissen"'));
        $wissen = substr($wissen, 0, strpos($wissen, '</section>'));
        $this->assertMatchesRegularExpression('#25\.11\.2026, 18:00 bis 20:00 Uhr.*?Das Hilfesystem.*?28\.04\.2027.*?Bürokratie-Labyrinth.*?Geplant: 1\. Quartal 2027.*?Trauma verstehen.*?Geplant: 3\. Quartal 2027.*?Gutachten#s', $wissen);
        $this->assertMatchesRegularExpression('#href="[^"]*/veranstaltungen/das-hilfesystem-2026-11-25"#', $wissen);
        $this->assertStringNotContainsString('Frage trifft Antwort</a>', $wissen);
    }

    /** Teams-Links mit Passcode aus dem Doc gehören nicht auf die Website. */
    public function test_keine_zugangsdaten(): void
    {
        $seiten = [$this->get('/veranstaltungen')->getContent()];
        foreach (Event::pluck('slug') as $slug) {
            $seiten[] = $this->get('/veranstaltungen/'.$slug)->assertOk()->getContent();
        }

        foreach ($seiten as $html) {
            $this->assertStringNotContainsString('teams.microsoft.com', $html);
            $this->assertStringNotContainsStringIgnoringCase('passcode', $html);
            $this->assertStringNotContainsString('Besprechungs-ID', $html);
        }
    }

    /** Seite eines Termins: Bild im Kopf, Reihe darüber, Anmeldung per Mail, Hinweis auf den Zugangslink. */
    public function test_seite_eines_termins(): void
    {
        $html = $this->get('/veranstaltungen/das-hilfesystem-2026-11-25')->assertOk()->getContent();
        $this->assertStringContainsString('veranstaltung-das-hilfesystem.webp', $html);
        $this->assertStringContainsString('KE!N EINZELFALL im Dialog – Wissen trifft Erfahrung', $html);
        $this->assertStringContainsString('href="mailto:veranstaltung@kein-einzelfall.de?subject=Anmeldung%3A%20Das%20Hilfesystem%20am%2025.11.2026"', $html);
        $this->assertStringContainsString('Nach deiner Anmeldung erhältst du den Zugangslink', $html);

        $rostock = $this->get('/veranstaltungen/wenn-aus-erfahrung-wissen-wird-2026-11-24')->assertOk()->getContent();
        $this->assertStringContainsString('Hermannstraße 36, 18055 Rostock', $rostock);
        $this->assertStringContainsString('antigewaltwoche-rostock-2026-programm.pdf', $rostock);
        $this->assertFileExists(public_path('dokumente/2026/11/antigewaltwoche-rostock-2026-programm.pdf'));
    }

    /** Bestehende Datenbanken: die Migration baut die Altseite um, einmal; Termine nur, wo sie fehlen. */
    public function test_migration(): void
    {
        $seite = $this->seite();
        $seite->blocks()->delete();
        $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['titel' => VeranstaltungenSeeder::ALTE_UEBERSCHRIFT, 'absaetze' => ['Alt.']]]);
        $seite->blocks()->create(['typ' => 'download_list', 'position' => 1, 'data' => ['titel' => 'Dokumente zum Herunterladen', 'dokumente' => []]]);
        Event::where('slug', 'frage-trifft-antwort-2027-01-07')->update(['titel' => 'Vom Verein geändert']);
        Event::where('slug', 'frage-trifft-antwort-2027-04-01')->delete();

        $migration = require database_path('migrations/2026_11_02_170000_veranstaltungen_nach_taddis_doc.php');
        $migration->up();
        $migration->up();

        $this->assertSame(6, $seite->blocks()->count());
        $this->assertTrue($seite->blocks()->where('typ', 'download_list')->first()->data['neben_text']);
        $this->assertSame('Vom Verein geändert', Event::where('slug', 'frage-trifft-antwort-2027-01-07')->value('titel'));
        $this->assertSame(8, Event::count());

        // Ein vom Verein umgebauter Aufbau bleibt.
        $seite->blocks()->orderBy('position')->first()->update(['data' => ['titel' => 'Eigene Einleitung']]);
        $migration->up();
        $this->assertSame('Eigene Einleitung', $seite->blocks()->orderBy('position')->first()->data['titel']);
    }

    /** Vergangene Termine: nur die Liste, ohne Kalender und Reihen. */
    public function test_vergangene_nur_als_liste(): void
    {
        $this->travelTo(\Carbon\CarbonImmutable::parse('2026-11-26 12:00', 'Europe/Berlin'));
        $html = $this->get('/veranstaltungen?zeitraum=vergangen')->assertOk()->getContent();
        $this->assertStringContainsString('Vergangene Veranstaltungen', $html);
        $this->assertStringContainsString('Das Hilfesystem', $html);
        $this->assertStringNotContainsString('id="kalender"', $html);
        $this->assertStringNotContainsString('Termine dieser Reihe', $html);
    }
}
