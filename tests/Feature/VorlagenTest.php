<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\VorlagenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * „Vorlagen & Schreiben“ unter Wissen (KEV-323/324).
 */
class VorlagenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    /** Taddis zwölf Kategorien in ihrer Reihenfolge, alle Dateien da. */
    public function test_kategorien_und_dateien(): void
    {
        $kategorien = VorlagenSeeder::kategorien();
        $this->assertCount(12, $kategorien);
        $this->assertSame('Allgemeine Behörden-Schreiben', $kategorien[0]['titel']);
        $this->assertSame('Datenschutz & Akten', $kategorien[11]['titel']);

        $vorlagen = collect($kategorien)->pluck('gruppen')->flatten(1)->pluck('vorlagen')->flatten(1);
        $this->assertSame(145, $vorlagen->count());
        foreach ($vorlagen as $v) {
            $this->assertFileExists(public_path(ltrim($v['url'], '/')), $v['titel']);
            $this->assertStringEndsWith('.docx', $v['url']);
        }
        $this->assertSame($vorlagen->count(), $vorlagen->pluck('url')->unique()->count());
    }

    /** Menü: dritte Spalte „Selbst aktiv werden“, die Kategorien darunter. */
    public function test_menue_und_wissen(): void
    {
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $this->assertSame('Selbst aktiv werden', $wissen['gruppen'][2]['titel']);
        $eintrag = collect($wissen['children'])->firstWhere('url', '/vorlagen-und-schreiben');
        $this->assertCount(12, $eintrag['children']);

        // KEV-324: der fehlende Knopf auf /wissen.
        $this->assertMatchesRegularExpression('#href="/klartext-statt-fachsprache".*?href="/vorlagen-und-schreiben"#s', $this->get('/wissen')->getContent());
    }

    /** Übersicht mit Taddis Text und den Kacheln, Kategorie mit Verzeichnis daneben und den Vorlagen. */
    public function test_seiten(): void
    {
        $this->get('/vorlagen-und-schreiben')->assertOk()
            ->assertSee('Orientierung für Deine nächsten Schritte.')
            ->assertSee('Damit aus deinem Anliegen ein klares Schreiben wird.')
            ->assertSee('Damit aus Unsicherheit ein nächster Schritt werden kann.')
            ->assertSee('href="/vorlagen-datenschutz-und-akten"', false);

        $html = $this->get('/vorlagen-behoerdenschreiben')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<nav aria-label="Auf dieser Seite".*?href="\#vorlagen-antrag-eingang-zustaendigkeit"#s', $html);
        $this->assertStringContainsString('id="vorlagen-antrag-eingang-zustaendigkeit"', $html);
        $this->assertStringContainsString('lg:columns-2', $html);
        $this->assertStringContainsString('Eingangsbestätigung anfordern', $html);
        $this->assertStringContainsString('/dokumente/vorlagen/vorlagen-behoerdenschreiben/eingangsbestaetigung-anfordern.docx', $html);

        $this->seed(\Database\Seeders\UebersetzungenSeeder::class);
        \App\Models\Language::finden('en')->update(['aktiv' => true]);
        \App\Models\Language::memoLeeren();
        $this->get('/en/vorlagen-behoerdenschreiben')->assertOk()->assertSee('Sample letters to adapt');
    }

    /** Bestehende Datenbanken: Migration legt an und ergänzt den Knopf, einmal. */
    public function test_migration(): void
    {
        Page::where('slug', 'like', 'vorlagen-%')->each(fn ($p) => $p->delete());
        $knoepfe = Page::where('slug', 'wissen')->where('locale', 'de')->first()->blocks()->where('typ', 'seitenkarten')->first();
        $knoepfe->update(['data' => array_replace($knoepfe->data, ['seiten' => AltseiteSeeder::WISSEN_KNOEPFE_BIS_KEV_324])]);

        $migration = require database_path('migrations/2026_11_02_210000_vorlagen_und_schreiben.php');
        $migration->up();
        $migration->up();

        $this->assertSame(13, Page::where('slug', 'like', 'vorlagen-%')->where('locale', 'de')->count());
        $this->assertSame(AltseiteSeeder::WISSEN_KNOEPFE['seiten'], $knoepfe->fresh()->data['seiten']);
    }
}
