<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $redaktion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
        $this->redaktion = User::factory()->redaktion()->create();
    }

    public function test_panel_ist_ohne_anmeldung_gesperrt(): void
    {
        // Die Seite verarbeitet später Anfragen mit Gesundheitsdaten —
        // ein offenes Panel wäre ein Meldefall.
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/pages')->assertRedirect('/admin/login');
    }

    public function test_normales_konto_kommt_nicht_ins_panel(): void
    {
        // Entscheidend für später: Sobald Vereinsmitglieder eigene Konten haben,
        // liegen sie in derselben Tabelle wie die Redaktion. Ohne diese Sperre
        // käme jedes angemeldete Mitglied an die Anfragen — also an Art.-9-Daten.
        $mitglied = User::factory()->create();   // ohne ->redaktion()

        $this->assertFalse($mitglied->panel_zugang);
        $this->actingAs($mitglied)->get('/admin')->assertForbidden();
    }

    public function test_panel_seiten_rendern_fuer_angemeldete(): void
    {
        // Prüft den kompletten HTTP-Durchlauf inklusive Layout und Assets.
        // (Ein Login per curl ginge nicht: Filament läuft über Livewire,
        // ein klassisches Formular-POST beantwortet es mit 405.)
        $seite = Page::where('slug', 'verein')->first();

        $this->actingAs($this->redaktion);

        $this->get('/admin')->assertOk();
        $this->get('/admin/pages')->assertOk()->assertSee('Datenschutz');
        $this->get('/admin/pages/'.$seite->getKey().'/edit')->assertOk();
        $this->get('/admin/redirects')->assertOk();
    }

    public function test_seitenliste_zeigt_den_bestand(): void
    {
        Livewire::actingAs($this->redaktion)
            ->test(ListPages::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(Page::all());
    }

    public function test_bearbeiten_laedt_die_bausteine_der_seite(): void
    {
        $seite = Page::where('slug', 'verein')->first();

        Livewire::actingAs($this->redaktion)
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->assertSuccessful()
            ->assertSchemaStateSet([
                'titel' => $seite->titel,
                'slug' => 'verein',
            ]);
    }

    /**
     * Der wichtigste Test am ganzen Panel: Speichern darf keinen Inhalt verlieren.
     * Die Bausteine hängen an einer Relation und tragen ihren Inhalt als JSON —
     * genau dort gehen bei Repeatern gern Daten verloren.
     */
    public function test_speichern_erhaelt_alle_bausteine_und_texte(): void
    {
        $seite = Page::where('slug', 'datenschutz')->first();

        $vorherAnzahl = $seite->blocks()->count();
        $vorherTexte = $seite->blocks()->pluck('data')->toJson();

        Livewire::actingAs($this->redaktion)
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->call('save')
            ->assertHasNoErrors();

        $seite->refresh();

        $this->assertSame($vorherAnzahl, $seite->blocks()->count(), 'Bausteine verloren');
        $this->assertSame($vorherTexte, $seite->blocks()->pluck('data')->toJson(), 'Texte verändert');
    }

    public function test_reihenfolge_der_bausteine_bleibt_erhalten(): void
    {
        $seite = Page::where('slug', 'arbeitsgruppen')->first();
        $vorher = $seite->blocks()->pluck('typ')->all();

        Livewire::actingAs($this->redaktion)
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($vorher, $seite->fresh()->blocks()->pluck('typ')->all());
    }

    public function test_slug_laesst_nur_saubere_adressen_zu(): void
    {
        $seite = Page::where('slug', 'wissen')->first();

        Livewire::actingAs($this->redaktion)
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->fillForm(['slug' => 'Nicht Erlaubt!'])
            ->call('save')
            ->assertHasFormErrors(['slug']);
    }

    public function test_titel_ist_pflicht(): void
    {
        $seite = Page::where('slug', 'wissen')->first();

        Livewire::actingAs($this->redaktion)
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->fillForm(['titel' => ''])
            ->call('save')
            ->assertHasFormErrors(['titel']);
    }

    public function test_geaenderter_text_erscheint_auf_der_echten_seite(): void
    {
        $seite = Page::where('slug', 'wissen')->first();

        $seite->blocks()->first()->update([
            'data' => ['titel' => 'Geänderte Überschrift', 'absaetze' => ['Neuer Absatz.']],
        ]);

        $this->get('/wissen')
            ->assertSee('Geänderte Überschrift')
            ->assertSee('Neuer Absatz.');
    }
}
