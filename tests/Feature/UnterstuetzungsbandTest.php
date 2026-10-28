<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UnterstuetzungsbandSeeder;
use Tests\TestCase;

/**
 * KEV-120: Das Band „Unterstützung“ (Spenden, Mitglied werden) steht nicht
 * mehr auf der Startseite, sondern vereinzelt auf Unterseiten über den Verein
 * und seine Arbeit.
 */
class UnterstuetzungsbandTest extends TestCase
{
    private const ZITAT = 'Sei Du dabei, jede Unterstützung zählt, egal wie gering!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_ausgewaehlte_unterseiten_enden_mit_dem_band(): void
    {
        foreach (UnterstuetzungsbandSeeder::SEITEN as $slug) {
            $typen = $this->seite($slug)->blocks()->orderBy('position')->pluck('typ')->all();

            $this->assertSame('cta_band', end($typen), $slug);
            $this->assertCount(1, array_keys($typen, 'cta_band', true), $slug);

            $this->get('/'.$slug)->assertOk()
                ->assertSee(self::ZITAT)
                ->assertSee('href="/spenden"', false)
                ->assertSee('href="/mitgliedschaft"', false);
        }
    }

    /**
     * Wer Hilfe sucht, soll nicht um Geld gebeten werden. Gezählt werden die
     * Bänder, nicht der Satz: Auf /spenden steht er auch als Unterzeile im
     * Seitenkopf. Nicht auf /veranstaltungen, dort stünde es über dem Kalender.
     */
    public function test_nicht_auf_der_startseite_und_nicht_auf_hilfe_seiten(): void
    {
        foreach ([Page::STARTSEITE_SLUG, 'selbsthilfegruppen', 'anfragen', 'spenden', 'mitgliedschaft', 'veranstaltungen'] as $slug) {
            $this->assertFalse($this->seite($slug)->blocks()->where('typ', 'cta_band')->exists(), $slug);
        }
        // Ohne Kopf: Im Handy-Menü steht derselbe Satz als Unterzeile von „Spenden“.
        $this->assertStringNotContainsString(self::ZITAT, $this->inhalt('/'));
    }

    public function test_ein_zweiter_lauf_haengt_kein_zweites_band_an(): void
    {
        $this->assertSame(0, UnterstuetzungsbandSeeder::anhaengen());

        foreach (UnterstuetzungsbandSeeder::SEITEN as $slug) {
            $this->assertSame(1, $this->seite($slug)->blocks()->where('typ', 'cta_band')->count(), $slug);
        }
    }

    public function test_migration_nimmt_das_band_von_der_startseite_und_verteilt_es(): void
    {
        // Stand vor KEV-120: das Band auf der Startseite, keins auf den Unterseiten.
        $start = $this->seite(Page::STARTSEITE_SLUG);
        $start->blocks()->create(['typ' => 'cta_band', 'position' => 4, 'data' => UnterstuetzungsbandSeeder::BAND]);
        foreach (UnterstuetzungsbandSeeder::SEITEN as $slug) {
            $this->seite($slug)->blocks()->where('typ', 'cta_band')->delete();
        }

        $migration = require database_path('migrations/2026_10_18_110000_unterstuetzungsband_auf_unterseiten.php');
        $migration->up();

        $this->assertFalse($start->blocks()->where('typ', 'cta_band')->exists());

        foreach (UnterstuetzungsbandSeeder::SEITEN as $slug) {
            $this->assertSame(1, $this->seite($slug)->blocks()->where('typ', 'cta_band')->count(), $slug);

            // Die englische Fassung baut die Migration neu, mit dem Band.
            $this->get('/en/'.$slug)->assertOk()
                ->assertSee('Be part of it – every bit of support counts, no matter how small!');
        }
        $this->assertStringNotContainsString('every bit of support counts', $this->inhalt('/en'));
    }

    /**
     * KEV-219: Auf den Tätigkeits- und Jahresberichten statt des Bands der
     * Spendenkasten der Startseite („Jetzt spenden“, kompakt).
     */
    public function test_jahresberichte_mit_dem_spendenkasten_der_startseite(): void
    {
        $seite = $this->seite('taetigkeits-und-jahresberichte');
        $this->assertSame(['text', 'donation_options'], $seite->blocks()->orderBy('position')->pluck('typ')->all());
        $this->assertTrue((bool) $seite->blocks()->where('typ', 'donation_options')->value('data')['kompakt']);
        $this->get('/taetigkeits-und-jahresberichte')->assertOk()
            ->assertSee('Jetzt spenden')
            ->assertSee('IBAN kopieren');
        $this->assertStringNotContainsString(self::ZITAT, $this->inhalt('/taetigkeits-und-jahresberichte'));

        // Stand davor: das Band am Ende. Die Migration tauscht es an derselben Stelle.
        $seite->blocks()->where('typ', 'donation_options')->delete();
        $seite->blocks()->create(['typ' => 'cta_band', 'position' => 1, 'data' => UnterstuetzungsbandSeeder::BAND]);
        $migration = require database_path('migrations/2026_10_24_290000_jahresberichte_spendenkasten.php');
        $migration->up();
        $migration->up();
        $this->assertSame(['text', 'donation_options'], $seite->blocks()->orderBy('position')->pluck('typ')->all());

        // Ein vom Verein geändertes Band bleibt.
        $seite->blocks()->where('typ', 'donation_options')->delete();
        $seite->blocks()->create(['typ' => 'cta_band', 'position' => 1, 'data' => ['eyebrow' => 'Eigenes Band', 'zitat' => 'Vom Verein.']]);
        UnterstuetzungsbandSeeder::spendenStattBand();
        $this->assertSame(['text', 'cta_band'], $seite->blocks()->orderBy('position')->pluck('typ')->all());
    }

    private function seite(string $slug): Page
    {
        return Page::where('locale', 'de')->where('slug', $slug)->firstOrFail();
    }

    /** Der Seiteninhalt ohne Kopf (dort steht die Unterzeile von „Spenden“ im Menü). */
    private function inhalt(string $pfad): string
    {
        $html = $this->get($pfad)->assertOk()->getContent();

        return substr($html, strpos($html, '<main'));
    }
}
