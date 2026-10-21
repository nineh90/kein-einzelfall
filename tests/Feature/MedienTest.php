<?php

namespace Tests\Feature;

use App\Filament\Resources\Groups\Pages\EditGroup;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\TeamMembers\Pages\EditTeamMember;
use App\Models\Group;
use App\Models\Page;
use App\Models\TeamMember;
use App\Models\User;
use App\Support\Medien;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Bilder und Dokumente im Panel hochladen (21.10.2026, Kevin: „Der Kunde
 * soll das ganze ja auch komplett selbst pflegen können“).
 */
class MedienTest extends TestCase
{
    use RefreshDatabase;

    private User $redaktion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
        $this->redaktion = User::factory()->redaktion()->create();
        Storage::fake('public');
    }

    public function test_titelbild_hochladen(): void
    {
        $seite = Page::where('slug', 'verein')->where('locale', 'de')->firstOrFail();

        Livewire::actingAs($this->redaktion)
            ->test(EditPage::class, ['record' => $seite->getKey()])
            // Wie im Panel: erst das alte Bild entfernen (×), dann das neue.
            ->fillForm(['titelbild' => null])
            // Ein echtes Bild statt fake()->image(): Das bräuchte GD, und das
            // fehlt auf dem Entwicklungsrechner.
            ->fillForm(['titelbild' => [UploadedFile::fake()->createWithContent(
                'Stand Berlin.png', file_get_contents(public_path('img/logo.png')),
            )]])
            ->call('save')
            ->assertHasNoFormErrors();

        $pfad = $seite->fresh()->titelbild;

        // Ein Pfad der Website wie vorher, lesbarer Name, eindeutig
        $this->assertMatchesRegularExpression('#^/storage/medien/bilder/\d{4}/\d{2}/stand-berlin-[a-z0-9]{6}\.png$#', $pfad);
        Storage::disk('public')->assertExists(substr($pfad, strlen('/storage/')));

        // Die Seite zeigt es, und die Adresse liefert die Datei aus, auch ohne
        // Verknüpfung public/storage.
        $this->get('/verein')->assertOk()->assertSee('src="'.$pfad.'?v=', false);
        $this->get($pfad)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_svg_wird_abgelehnt(): void
    {
        // Eine SVG-Datei kann Skript enthalten und liefe unter unserer Adresse.
        $seite = Page::where('slug', 'verein')->where('locale', 'de')->firstOrFail();

        Livewire::actingAs($this->redaktion)
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->fillForm(['titelbild' => UploadedFile::fake()->create('logo.svg', 2, 'image/svg+xml')])
            ->call('save')
            ->assertHasFormErrors(['titelbild']);
    }

    /**
     * Der eigentliche Prüfstein: Filament wirft beim Laden Werte weg, die es
     * auf der Festplatte des Felds nicht findet. Bestehende Pfade müssen
     * jedes Speichern überstehen, auch solche, deren Datei (noch) fehlt.
     */
    public function test_bestehende_pfade_ueberstehen_das_speichern(): void
    {
        $seite = Page::where('slug', 'verein')->where('locale', 'de')->firstOrFail();
        $seite->update(['titelbild' => '/img/titelbilder/fehlt-noch.webp']);

        Livewire::actingAs($this->redaktion)
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('/img/titelbilder/fehlt-noch.webp', $seite->fresh()->titelbild);

        $person = TeamMember::whereNotNull('foto_pfad')->firstOrFail();
        $foto = $person->foto_pfad;

        Livewire::actingAs($this->redaktion)
            ->test(EditTeamMember::class, ['record' => $person->getKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($foto, $person->fresh()->foto_pfad);

        $gruppe = Group::whereNotNull('titelbild')->firstOrFail();
        $bild = $gruppe->titelbild;

        Livewire::actingAs($this->redaktion)
            ->test(EditGroup::class, ['record' => $gruppe->getKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($bild, $gruppe->fresh()->titelbild);
    }

    public function test_hochgeladenes_dokument_steht_in_der_liste(): void
    {
        Storage::disk('public')->put('medien/dokumente/2026/10/jahresbericht-2025-k3x9qa.pdf', str_repeat('x', 4096));

        $seite = Page::create(['slug' => 'dokumentprobe', 'titel' => 'Dokumentprobe', 'published_at' => now()]);
        $seite->blocks()->create(['typ' => 'download_list', 'position' => 0, 'data' => [
            'titel' => 'Berichte',
            'dokumente' => [
                ['titel' => 'Jahresbericht 2025', 'datei' => '/storage/medien/dokumente/2026/10/jahresbericht-2025-k3x9qa.pdf'],
                // Hochgeladen, aber die Datei ist weg: fliegt raus wie bei /dokumente
                ['titel' => 'Verschwunden', 'datei' => '/storage/medien/dokumente/2026/10/weg.pdf'],
            ],
        ]]);

        $html = $this->get('/dokumentprobe')->assertOk()->getContent();

        $this->assertStringContainsString('href="/storage/medien/dokumente/2026/10/jahresbericht-2025-k3x9qa.pdf"', $html);
        // Größe aus der Datei, niemand hat Bytes eingetragen
        $this->assertStringContainsString('PDF-Datei, 4 KB', $html);
        $this->assertStringNotContainsString('Verschwunden', $html);
    }

    public function test_auslieferung_bleibt_im_ordner(): void
    {
        Storage::disk('public')->put('medien/bilder/probe.jpg', 'bild');

        $this->get('/storage/medien/bilder/probe.jpg')->assertOk();
        $this->get('/storage/medien/bilder/fehlt.jpg')->assertNotFound();
        $this->get('/storage/%2e%2e/%2e%2e/.env')->assertNotFound();
        $this->get('/storage/medien/../../../.env')->assertNotFound();
    }

    public function test_dateipfad(): void
    {
        $this->assertSame(public_path('img/logo.png'), Medien::dateipfad('/img/logo.png?v=123'));
        $this->assertSame(Storage::disk('public')->path('medien/a.jpg'), Medien::dateipfad('/storage/medien/a.jpg'));

        foreach (['https://example.org/a.jpg', '//example.org/a.jpg', 'img/logo.png', '/storage/../.env', '/%2e%2e/.env', null, ''] as $fremd) {
            $this->assertNull(Medien::dateipfad($fremd), (string) $fremd);
        }
    }
}
