<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Textpflege;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\DatenschutzSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Die Datenschutzerklärung beschreibt diese Website, nicht die Altseite:
 * kein Cookie-Banner, die zwei echten Cookies, der Speicher im Browser,
 * betterplace erst nach Klick, Kontaktformular mit den echten Fristen.
 */
class DatenschutzerklaerungTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    private function seite(): Page
    {
        return Page::where('slug', 'datenschutz')->where('locale', 'de')->firstOrFail();
    }

    /** @return list<string> */
    private function reihenfolge(): array
    {
        return $this->seite()->blocks()->orderBy('position')->get()
            ->map(fn ($b) => $b->typ === 'text' ? ($b->data['titel'] ?? '') : $b->typ)
            ->all();
    }

    public function test_beschreibt_diese_website(): void
    {
        $html = $this->get('/datenschutz')->assertOk()->getContent();

        $this->assertStringNotContainsString('Real Cookie Banner', $html);
        $this->assertStringNotContainsString('Marketing', $html);
        $this->assertStringContainsString('Diese Website setzt nur zwei Cookies.', $html);
        $this->assertStringContainsString('XSRF-TOKEN', $html);
        $this->assertStringContainsString('Speicher im Browser (Local Storage)', $html);
        $this->assertStringContainsString('href="/datenschutz#gespeicherte-einstellungen"', $html);
        $this->assertStringContainsString('betterplace.org gGmbH, Schlesische Straße 26, 10997 Berlin', $html);
        $this->assertStringContainsString('href="https://www.betterplace.org/c/regeln/datenschutz"', $html);
        $this->assertStringContainsString('Ihre IP-Adresse speichern wir nicht.', $html);
        $this->assertStringContainsString('90 Tage nach Abschluss der Bearbeitung', $html);
        $this->assertStringNotContainsString('Ihre IP-Adresse in den Logfiles', $html);

        // Die Liste kommt aus config/speicher.php und nennt damit jeden Schlüssel.
        $this->assertStringContainsString('id="gespeicherte-einstellungen"', $html);
        foreach (config('speicher.eintraege') as $eintrag) {
            $this->assertStringContainsString(e(__($eintrag['label'])), $html);
        }

        // Die neuen Abschnitte hinter „Cookies“, die Liste am Ende: Zwischen
        // den Abschnitten bräche sie die Seitenleiste auf.
        // Seit KEV-226 dahinter die Anmeldung zu Selbsthilfegruppen.
        $this->assertSame(
            ['Cookies', 'Speicher im Browser (Local Storage)', 'Spendenformulare von betterplace.org', 'Kontaktformular',
                'Anmeldung zu Selbsthilfegruppen', 'speicher_uebersicht'],
            array_slice($this->reihenfolge(), -6),
        );
    }

    public function test_migration_ersetzt_nur_den_text_der_altseite(): void
    {
        // Stand vor der Migration nachstellen: Altseite, wie importiert.
        $seite = $this->seite();
        $seite->blocks()->get()->filter(fn ($b) => $b->typ === 'speicher_uebersicht'
            || in_array($b->data['titel'] ?? '', [DatenschutzSeeder::SPEICHER['titel'], DatenschutzSeeder::BETTERPLACE['titel']], true))
            ->each->delete();

        $altseite = collect(json_decode(file_get_contents(base_path('docs/altseite-inhalt.json')), true)['/datenschutz/']['bloecke'])
            ->keyBy('titel')
            ->map(fn ($b) => Textpflege::bausteinDaten(['absaetze' => $b['absaetze']], 'datenschutz')['absaetze']);

        $seite->blocks()->get()->each(function ($b) use ($altseite) {
            $titel = $b->data['titel'] ?? '';
            if ($titel === 'Cookies') {
                $b->update(['data' => [...$b->data, 'absaetze' => $altseite['Cookies']]]);
            }
            if ($titel === 'Kontaktformular') {
                // Vom Verein im Panel geändert: bleibt.
                $b->update(['data' => [...$b->data, 'absaetze' => ['Eigener Text des Vereins.']]]);
            }
        });
        $seite->blocks()->create(['typ' => 'text', 'position' => 99, 'data' => [
            'titel' => DatenschutzSeeder::ALT_EINWILLIGUNGSTOOL, 'absaetze' => $altseite[DatenschutzSeeder::ALT_EINWILLIGUNGSTOOL],
        ]]);

        $migration = require database_path('migrations/2026_10_24_120000_datenschutz_aktualisieren.php');
        $migration->up();
        $migration->up();

        $bloecke = $this->seite()->blocks()->get()->keyBy(fn ($b) => $b->data['titel'] ?? $b->typ);

        $this->assertFalse($bloecke->has(DatenschutzSeeder::ALT_EINWILLIGUNGSTOOL));
        $this->assertSame(DatenschutzSeeder::COOKIES, $bloecke['Cookies']->data['absaetze']);
        $this->assertSame(['Eigener Text des Vereins.'], $bloecke['Kontaktformular']->data['absaetze']);
        // Zweimal ausgeführt, trotzdem jeder Abschnitt nur einmal.
        $this->assertSame(1, collect($this->reihenfolge())->filter(fn ($t) => $t === DatenschutzSeeder::SPEICHER['titel'])->count());
        $this->assertSame(1, collect($this->reihenfolge())->filter(fn ($t) => $t === 'speicher_uebersicht')->count());
        $this->assertSame('speicher_uebersicht', collect($this->reihenfolge())->last());
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/datenschutz')->assertOk()->getContent();

        $this->assertStringContainsString('This website sets only two cookies.', $html);
        $this->assertStringContainsString('Storage in your browser (local storage)', $html);
        $this->assertStringContainsString('Donation forms from betterplace.org', $html);
        $this->assertStringContainsString('We do not store your IP address.', $html);
        $this->assertStringContainsString('href="/en/datenschutz#gespeicherte-einstellungen"', $html);
        $this->assertStringNotContainsString('Diese Website setzt nur zwei Cookies.', $html);
    }
}
