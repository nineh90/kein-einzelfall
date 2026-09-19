<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Page;
use App\Support\Spenden;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\StartseiteSeeder;
use Tests\TestCase;

/**
 * Die Spendenseite (KEV-5): PayPal und betterplace wie auf der Altseite —
 * nur dass dort nichts ungefragt lädt.
 *
 * Der Altseiten-Import hatte Konto und PayPal als Fliesstext übernommen und
 * die betterplace-iframes gar nicht. Seit KEV-5 macht `Spenden::
 * spendenseiteUmstellen()` daraus den Spenden-Baustein — im Seeder auf
 * frischer Datenbank, per Migration auf bestehenden.
 */
class SpendenseiteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
        $this->seed(StartseiteSeeder::class);
    }

    private function spendenseite(string $locale = 'de'): Page
    {
        return Page::where('slug', 'spenden')->where('locale', $locale)->firstOrFail();
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_09_19_140000_spendenseite_auf_den_baustein_umstellen.php');
    }

    public function test_die_spendenseite_bringt_paypal_und_betterplace_mit(): void
    {
        $html = $this->get('/spenden')->assertOk()->getContent();

        $this->assertStringContainsString('paypal.com/donate', $html, 'PayPal-Link fehlt');
        $this->assertStringContainsString('DE79 8306 5408 0006 8893 10', $html, 'IBAN fehlt');
        $this->assertMatchesRegularExpression('/girocode-flaeche[^>]*role="img"/', $html, 'QR-Code fehlt');

        // Beide Projekte der Altseite, als Zwei-Klick-Einbettung mit Direktlink.
        foreach (Spenden::projekte() as $projekt) {
            $this->assertStringContainsString(e($projekt['titel']), $html);
            $this->assertStringContainsString($projekt['url'], $html);
        }

        $this->assertStringContainsString('verwaltung@kein-einzelfall.de', $html, 'Spendenbescheinigung fehlt');
    }

    public function test_der_textblock_der_altseite_ist_durch_den_baustein_ersetzt(): void
    {
        // Nicht zusätzlich, sondern statt: Sonst stünde die IBAN zweimal da.
        $typen = $this->spendenseite()->blocks()->pluck('typ')->all();

        $this->assertSame(['text', 'text', 'donation_options'], $typen);
        $this->get('/spenden')->assertDontSee('IBAN: DE79', false);
    }

    public function test_konto_und_paypal_sind_auf_start_und_spendenseite_dieselben(): void
    {
        // Eine IBAN, eine Stelle (App\Support\Spenden). Laufen die beiden
        // auseinander, hat jemand eine Kopie angelegt.
        $start = Page::where('slug', Page::STARTSEITE_SLUG)->firstOrFail()
            ->blocks()->where('typ', 'donation_options')->firstOrFail()->data;
        $spenden = $this->spendenseite()
            ->blocks()->where('typ', 'donation_options')->firstOrFail()->data;

        $this->assertSame($start['bank'], $spenden['bank']);
        $this->assertSame($start['paypal'], $spenden['paypal']);
    }

    public function test_eine_bestehende_spendenseite_wird_per_migration_umgestellt(): void
    {
        // Der Zustand jeder Installation vor KEV-5: Konto als Textblock.
        $seite = $this->spendenseite();
        $seite->blocks()->where('typ', 'donation_options')->delete();
        $seite->blocks()->create([
            'typ' => 'text', 'position' => 2,
            'data' => ['titel' => 'Jetzt spenden', 'absaetze' => ['Überweisung (Deutsche Skatbank)', 'IBAN: DE79 8306 5408 0006 8893 10', 'BIC: GENO DEF1 SLR']],
        ]);
        $seite->blocks()->create([
            'typ' => 'text', 'position' => 3,
            'data' => ['titel' => 'Spendenbescheinigung', 'absaetze' => ['Schreib an verwaltung@kein-einzelfall.de.']],
        ]);

        $this->migration()->up();

        $bloecke = $seite->fresh()->blocks;
        $this->assertSame(['text', 'text', 'donation_options'], $bloecke->pluck('typ')->all());
        // Der Text der Bescheinigung kommt aus dem Block der Seite — so, wie
        // er dort stand.
        $this->assertSame('Schreib an verwaltung@kein-einzelfall.de.', $bloecke->last()->data['bescheinigung']['text']);
        $this->assertSame('Jetzt spenden', $bloecke->last()->data['titel']);
    }

    public function test_die_umstellung_laeuft_zweimal_ohne_schaden(): void
    {
        $this->migration()->up();
        $this->migration()->up();

        $this->assertSame(1, $this->spendenseite()->blocks()->where('typ', 'donation_options')->count());
    }

    public function test_eine_bearbeitete_spendenseite_bleibt_unangetastet(): void
    {
        // Kein Kontoblock mit der bekannten IBAN mehr: Jemand hat die Seite
        // umgebaut. Dann ist sie seine, nicht unsere.
        $seite = $this->spendenseite();
        $seite->blocks()->delete();
        $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['titel' => 'Neu vom Verein', 'absaetze' => ['Eigener Text.']]]);

        $this->assertFalse(Spenden::spendenseiteUmstellen($seite->fresh()));
        $this->assertSame(['text'], $seite->fresh()->blocks()->pluck('typ')->all());
    }

    public function test_die_englische_fassung_behaelt_ihren_bescheinigungstext(): void
    {
        /*
         * Ein Klon mit übersetzten Blöcken, wie ihn der UebersetzungenSeeder
         * anlegt. Die deutsche Seite ist hier schon umgestellt — genau der
         * Fall, in dem ein ->each() in der Migration nach der ersten Seite
         * abbrach, weil „nichts zu tun“ als false zurückkam.
         */
        $de = $this->spendenseite();
        $en = $de->replicate()->fill(['locale' => 'en', 'uebersetzungs_gruppe' => $de->uebersetzungs_gruppe]);
        $en->save();
        $en->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['titel' => 'Donate now', 'absaetze' => ['IBAN: DE79 8306 5408 0006 8893 10']]]);
        $en->blocks()->create(['typ' => 'text', 'position' => 1, 'data' => ['titel' => 'Donation receipt', 'absaetze' => ['Write to verwaltung@kein-einzelfall.de.']]]);
        Language::where('code', 'en')->update(['aktiv' => true]);
        Language::memoLeeren();

        $this->migration()->up();

        $baustein = $en->fresh()->blocks()->where('typ', 'donation_options')->firstOrFail();
        $this->assertSame('Donate now', $baustein->data['titel']);
        $this->assertSame('Write to verwaltung@kein-einzelfall.de.', $baustein->data['bescheinigung']['text']);

        // Und die Beschriftungen der Einbettung sind englisch.
        $this->get('/en/spenden')->assertOk()
            ->assertSee('Show content this once')
            ->assertDontSee('Inhalt einmalig anzeigen');
    }
}
