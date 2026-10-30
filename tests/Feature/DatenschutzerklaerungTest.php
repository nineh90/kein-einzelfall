<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\RechtstexteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Datenschutzerklärung und Impressum, neu geschrieben für diese Website
 * (RechtstexteSeeder, 30.10.2026): kein Cookie-Banner, die zwei echten
 * Cookies, der Speicher im Browser, Teams mit Aufzeichnungen, betterplace
 * erst nach Klick, die echten Fristen.
 */
class DatenschutzerklaerungTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    private function seite(string $slug = 'datenschutz'): Page
    {
        return Page::where('slug', $slug)->where('locale', 'de')->firstOrFail();
    }

    /** @return list<string> */
    private function reihenfolge(string $slug = 'datenschutz'): array
    {
        return $this->seite($slug)->blocks()->orderBy('position')->get()
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
        $this->assertStringContainsString('href="/datenschutz#gespeicherte-einstellungen"', $html);
        $this->assertStringContainsString('betterplace.org gGmbH, Schlesische Straße 26, 10997 Berlin', $html);
        $this->assertStringContainsString('href="https://www.betterplace.org/c/regeln/datenschutz"', $html);
        $this->assertStringContainsString('Deine IP-Adresse speichern wir nicht.', $html);
        $this->assertStringContainsString('90 Tage nach Abschluss der Bearbeitung', $html);
        $this->assertStringContainsString('mailto:haefner@kein-einzelfall.de', $html);
        $this->assertStringContainsString('Die Treffen der Selbsthilfegruppen zeichnen wir nie auf.', $html);
        $this->assertStringContainsString('nach 90 Tagen automatisch gelöscht', $html);
        $this->assertStringContainsString(RechtstexteSeeder::STAND, $html);
        $this->assertStringNotContainsString('[Speicherdauer]', $html);

        // Rechte als Liste, nichts hinter „Weiterlesen“ versteckt
        $this->assertStringContainsString('<li><strong class="font-semibold text-ink">Auskunft</strong> zu bekommen', $html);
        $this->assertStringNotContainsString('Weniger anzeigen', $html);

        // Die Liste kommt aus config/speicher.php und nennt damit jeden Schlüssel.
        $this->assertStringContainsString('id="gespeicherte-einstellungen"', $html);
        foreach (config('speicher.eintraege') as $eintrag) {
            $this->assertStringContainsString(e(__($eintrag['label'])), $html);
        }

        $this->assertSame('speicher_uebersicht', collect($this->reihenfolge())->last());
        $this->assertContains('Treffen und Veranstaltungen über Microsoft Teams', $this->reihenfolge());
    }

    public function test_impressum(): void
    {
        $html = $this->get('/impressum')->assertOk()->getContent();

        $this->assertStringContainsString('Tatjana Belmar (1. Vorsitzende)', $html);
        $this->assertStringContainsString('Registernummer: VR 25873', $html);
        $this->assertStringContainsString('href="/anfragen"', $html);
        $this->assertStringContainsString('Titelbilder und Illustrationen: mit KI erzeugt.', $html);
        $this->assertStringNotContainsString('Shutterstock', $html);
        $this->assertSame(array_column(RechtstexteSeeder::IMPRESSUM, 'titel'), $this->reihenfolge('impressum'));
    }

    public function test_migration_ersetzt_beide_seiten_vollstaendig(): void
    {
        // Stand vor der Migration: Texte der Altseite, etwa der Bildnachweis.
        foreach (['datenschutz', 'impressum'] as $slug) {
            $this->seite($slug)->blocks()->delete();
            $this->seite($slug)->blocks()->create(['typ' => 'text', 'position' => 0,
                'data' => ['titel' => 'Bildnachweis', 'absaetze' => ['Shutterstock']]]);
        }

        $migration = require database_path('migrations/2026_10_30_200000_rechtstexte_neu.php');
        $migration->up();
        $migration->up();

        $this->assertNotContains('Bildnachweis', $this->reihenfolge());
        $this->assertSame(1, collect($this->reihenfolge())->filter(fn ($t) => $t === 'speicher_uebersicht')->count());
        $this->assertCount(count(RechtstexteSeeder::DATENSCHUTZ) + 1, $this->reihenfolge());
        $this->assertSame(array_column(RechtstexteSeeder::IMPRESSUM, 'titel'), $this->reihenfolge('impressum'));
        $this->get('/impressum')->assertDontSee('Shutterstock');
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/datenschutz')->assertOk()->getContent();

        $this->assertStringContainsString('This website sets only two cookies.', $html);
        $this->assertStringContainsString('We do not store your IP address.', $html);
        $this->assertStringContainsString('We never record the meetings of the self-help groups.', $html);
        $this->assertStringContainsString('href="/en/datenschutz#gespeicherte-einstellungen"', $html);
        $this->assertStringNotContainsString('Diese Website setzt nur zwei Cookies.', $html);

        // Jeder Satz der beiden Seiten steht im Wörterbuch
        $woerterbuch = json_decode(file_get_contents(database_path('seeders/data/uebersetzungen.json')), true);
        foreach ([...RechtstexteSeeder::DATENSCHUTZ, ...RechtstexteSeeder::IMPRESSUM] as $abschnitt) {
            foreach ([...(isset($abschnitt['titel']) ? [$abschnitt['titel']] : []), ...$abschnitt['absaetze']] as $satz) {
                if (preg_match('/^[\p{Lu}][\p{Ll}]/u', $satz) && ! str_starts_with($satz, 'IBAN') && ! str_starts_with($satz, 'BIC')
                    && ! preg_match('/^(Deutsche Skatbank|Schiffbeker|Tatjana Belmar$|Registergericht|Registernummer|E-Mail:)/u', $satz)) {
                    $this->assertArrayHasKey(trim($satz), $woerterbuch, "Ohne Übersetzung: {$satz}");
                }
            }
        }

        $this->get('/en/impressum')->assertOk()->assertSee('Cover images and illustrations: generated with AI.');
    }
}
