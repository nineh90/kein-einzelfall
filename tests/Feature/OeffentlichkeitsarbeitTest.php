<?php

namespace Tests\Feature;

use App\Filament\Resources\OutreachItems\Pages\CreateOutreachItem;
use App\Filament\Resources\OutreachItems\Pages\EditOutreachItem;
use App\Models\OutreachItem;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\OeffentlichkeitsarbeitSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Messen, Petitionen, Presse (KEV-134): auf /oeffentlichkeitsarbeit als
 * kleine Karten je Art, jede mit eigener Seite.
 */
class OeffentlichkeitsarbeitTest extends TestCase
{
    use RefreshDatabase;

    private const BAYREUTH = '/oeffentlichkeitsarbeit/ehrenamtsmesse-bayreuth-2026';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    /** Kevin: Der Abschnitt heißt „Messen“, darin eine kleine Karte. */
    public function test_uebersicht_zeigt_messen_als_kleine_karten(): void
    {
        $html = $this->get('/oeffentlichkeitsarbeit')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h2 id="oa-messen"[^>]*>\s*Messen\s*</h2>#', $html);
        $this->assertStringNotContainsString('Einblicke</h2>', $html);

        // Die Karte: Foto (Schmuck), Datum, Ort, Titel als Link, Anriss
        $this->assertMatchesRegularExpression('#<img src="/img/oeffentlichkeitsarbeit/ehrenamtsmesse-bayreuth-2026-1\.webp\?v=[^"]+" alt=""#', $html);
        $this->assertStringContainsString('<time datetime="2026-10-10">10.10.2026</time>', $html);
        $this->assertMatchesRegularExpression('#<a href="http://localhost'.preg_quote(self::BAYREUTH, '#').'"[^>]*>\s*Ehrenamtsmesse Bayreuth\s*</a>#', $html);
        $this->assertStringContainsString('line-clamp-3', $html);
        $this->assertStringContainsString('Nach langer Anreise präsentierte unsere 1. Vorsitzende', $html);

        // Klein: drei Spalten, auch mit nur einem Eintrag; der ganze Text
        // und die übrigen Fotos stehen nicht hier.
        $this->assertStringContainsString('<ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">', $html);
        $this->assertStringNotContainsString('Vielen Dank für die vielen offenen Gespräche', $html);
        $this->assertStringNotContainsString('ehrenamtsmesse-bayreuth-2026-2.webp', $html);
    }

    /** Die Unterseite: ganzer Text, Dank fett, alle drei Fotos mit Beschreibung. */
    public function test_seite_der_messe_zeigt_alles(): void
    {
        $html = $this->get(self::BAYREUTH)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h1[^>]*>\s*Ehrenamtsmesse Bayreuth\s*</h1>#', $html);
        $this->assertStringContainsString('<time datetime="2026-10-10">10. Oktober 2026</time>', $html);
        $this->assertStringContainsString('Bayreuth</span>', $html);
        $this->assertStringContainsString('>Messe</span>', $html);
        $this->assertStringContainsString('<p><strong>Vielen Dank für die vielen offenen Gespräche und das große '
            .'Interesse an unserer Arbeit.</strong></p>', $html);

        foreach ([1, 2, 3] as $nr) {
            $this->assertMatchesRegularExpression(
                '#<img src="/img/oeffentlichkeitsarbeit/ehrenamtsmesse-bayreuth-2026-'.$nr.'\.webp\?v=[^"]+" alt="[^"]{20,}"#',
                $html,
            );
        }

        // Maße am Bild, damit die Seite beim Laden nicht springt
        $this->assertStringContainsString('width="745" height="994"', $html);
        $this->assertStringContainsString('Weitere Fotos', $html);

        // Brotkrumen zurück zur Übersicht
        $this->assertMatchesRegularExpression('#href="[^"]*/oeffentlichkeitsarbeit"[^>]*>\s*Öffentlichkeitsarbeit#', $html);
    }

    public function test_unveroeffentlicht_oder_unbekannt_gibt_404(): void
    {
        OutreachItem::where('slug', 'ehrenamtsmesse-bayreuth-2026')->update(['published_at' => null]);

        $this->get(self::BAYREUTH)->assertNotFound();
        $this->get('/oeffentlichkeitsarbeit/gibt-es-nicht')->assertNotFound();

        // Ohne veröffentlichte Messe kein leerer Abschnitt
        $this->get('/oeffentlichkeitsarbeit')->assertOk()->assertDontSee('id="oa-messen"', false);
    }

    /** Neueste zuerst, ohne Datum ans Ende; nur die Art des Abschnitts. */
    public function test_reihenfolge_und_art(): void
    {
        $neu = fn (array $d) => OutreachItem::create($d + ['art' => 'messe', 'published_at' => now()]);
        $neu(['slug' => 'messe-berlin-2026', 'titel' => 'Messe Berlin', 'datum' => '2026-04-25']);
        $neu(['slug' => 'messe-ohne-datum', 'titel' => 'Messe ohne Datum']);
        $neu(['slug' => 'messe-luebeck-2026', 'titel' => 'Messe Lübeck', 'datum' => '2026-09-06']);
        $neu(['slug' => 'eine-petition', 'titel' => 'Eine Petition', 'art' => 'petition']);

        $html = $this->get('/oeffentlichkeitsarbeit')->assertOk()->getContent();

        $stellen = array_map(fn ($t) => strpos($html, $t), [
            'Ehrenamtsmesse Bayreuth', 'Messe Lübeck', 'Messe Berlin', 'Messe ohne Datum',
        ]);
        $this->assertNotContains(false, $stellen);
        $this->assertSame($stellen, collect($stellen)->sort()->values()->all());

        // Petitionen stehen nicht unter „Messen“, einen Abschnitt dafür gibt es noch nicht
        $this->assertStringNotContainsString('Eine Petition', $html);
        $this->get('/oeffentlichkeitsarbeit/eine-petition')->assertOk();
    }

    public function test_migration_legt_bayreuth_nur_einmal_an(): void
    {
        $migration = require database_path('migrations/2026_10_21_200000_oeffentlichkeitsarbeit_messe_bayreuth.php');

        $migration->up();
        $this->assertSame(1, OutreachItem::where('slug', 'ehrenamtsmesse-bayreuth-2026')->count());

        // Vom Verein geändert: bleibt
        OutreachItem::where('slug', 'ehrenamtsmesse-bayreuth-2026')->update(['titel' => 'Freiwilligenmesse Bayreuth']);
        $migration->up();
        $this->assertSame('Freiwilligenmesse Bayreuth', OutreachItem::where('slug', 'ehrenamtsmesse-bayreuth-2026')->value('titel'));

        // Die Fotos liegen wirklich da
        foreach (OeffentlichkeitsarbeitSeeder::MESSE_BAYREUTH['fotos'] as $foto) {
            $this->assertFileExists(public_path(ltrim($foto['bild'], '/')));
        }
    }

    public function test_englisch(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $this->get('/en/oeffentlichkeitsarbeit')->assertOk()
            ->assertSee('Fairs')
            ->assertSee('Volunteering fair Bayreuth')
            ->assertSee('10 October 2026')
            ->assertSee('/en/oeffentlichkeitsarbeit/ehrenamtsmesse-bayreuth-2026', false);

        $this->get('/en'.self::BAYREUTH)->assertOk()
            ->assertSee('Volunteering fair Bayreuth')
            ->assertSee('Fair')
            ->assertSee('Thank you for the many open conversations and the great interest in our work.')
            ->assertSee('More photos')
            ->assertSee('Two women in white T-shirts', false);
    }

    public function test_in_suche_und_sitemap(): void
    {
        $this->get('/suche?q=Bayreuth')->assertOk()->assertSee('ehrenamtsmesse-bayreuth-2026', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee(url(self::BAYREUTH), false);
    }

    /** Der Verein legt eine Messe selbst an, mit hochgeladenem Foto. */
    public function test_messe_im_panel_anlegen(): void
    {
        Storage::fake('public');
        $redaktion = User::factory()->redaktion()->create();

        Livewire::actingAs($redaktion)
            ->test(CreateOutreachItem::class)
            ->fillForm([
                'art' => 'messe',
                'titel' => 'Ehrenamtsmesse Lübeck',
                'slug' => 'ehrenamtsmesse-luebeck-2026',
                'datum' => '2026-09-06',
                'ort' => 'Lübeck',
                'beschreibung' => '<p>Mit viel Freude …</p>',
                'fotos' => [['bild' => [UploadedFile::fake()->createWithContent(
                    'stand.png', file_get_contents(public_path('img/logo.png')),
                )], 'alt' => 'Unser Stand']],
                'published_at' => now()->subMinute(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $messe = OutreachItem::where('slug', 'ehrenamtsmesse-luebeck-2026')->firstOrFail();
        $foto = $messe->fotoListe()[0];
        $this->assertMatchesRegularExpression('#^/storage/medien/bilder/\d{4}/\d{2}/stand-[a-z0-9]{6}\.png$#', $foto['bild']);
        $this->assertSame('Unser Stand', $foto['alt']);

        $this->get('/oeffentlichkeitsarbeit')->assertSee('Ehrenamtsmesse Lübeck');
        $this->get('/oeffentlichkeitsarbeit/ehrenamtsmesse-luebeck-2026')->assertOk()->assertSee('Mit viel Freude');
    }

    /** Bestehende Fotos (Pfade unter /img) überstehen das Speichern. */
    public function test_bayreuth_ueberlebt_speichern_im_panel(): void
    {
        $messe = OutreachItem::where('slug', 'ehrenamtsmesse-bayreuth-2026')->firstOrFail();
        $vorher = $messe->fotos;

        Livewire::actingAs(User::factory()->redaktion()->create())
            ->test(EditOutreachItem::class, ['record' => $messe->getKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(array_values($vorher), array_values($messe->fresh()->fotos));
    }

    /** Die Seite selbst ist ein Baustein, den der Verein verschieben oder löschen kann. */
    public function test_abschnitt_ist_ein_baustein(): void
    {
        $seite = Page::where('slug', 'oeffentlichkeitsarbeit')->where('locale', 'de')->firstOrFail();
        $block = $seite->blocks()->where('typ', 'oeffentlichkeitsarbeit')->firstOrFail();

        $this->assertSame(['titel' => 'Messen', 'art' => 'messe'], $block->data);
        $this->assertFalse($block->zeigtNichts());

        $block->update(['data' => ['titel' => 'Presse', 'art' => 'presse']]);
        $this->assertTrue($block->fresh()->zeigtNichts());
    }

    /**
     * Deploy vom 22.10.2026: Die Migration von KEV-129 ruft die Übersetzungen
     * auf, und die lasen die Tabelle der Öffentlichkeitsarbeit, die erst
     * eine Migration später entsteht. Auf dem Server (mit Vereinsseite) brach
     * der Deploy deshalb ab. Fehlt eine Tabelle, wird sie übersprungen.
     */
    public function test_uebersetzungen_ueberspringen_fehlende_tabellen(): void
    {
        $ohneTabelle = new class extends \Illuminate\Database\Eloquent\Model
        {
            protected $table = 'gibt_es_noch_nicht';
        };

        $felder = new \ReflectionMethod(UebersetzungenSeeder::class, 'felder');
        $felder->invoke(new UebersetzungenSeeder, $ohneTabelle::class, 'slug', 'uebersetzungen-oeffentlichkeitsarbeit.json');

        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('gibt_es_noch_nicht'));
    }
}
