<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Group;
use App\Models\Language;
use App\Models\Page;
use App\Models\TeamMember;
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

    public function test_verein_kann_eine_uebersetzung_anlegen(): void
    {
        // Der eigentliche Auftrag: Die Mehrsprachigkeit muss im Panel bedienbar
        // sein, nicht nur im Code existieren.
        $englisch = Language::finden('en');
        $englisch->update(['aktiv' => true]);
        Language::memoLeeren();

        $deutsch = Page::where('locale', 'de')->where('slug', 'verein')->firstOrFail();

        Livewire::actingAs($this->redaktion)
            ->test(CreatePage::class)
            ->fillForm([
                'locale' => 'en',
                'uebersetzungs_gruppe' => $deutsch->uebersetzungs_gruppe,
                'titel' => 'About us',
                'slug' => 'about-us',
                'published_at' => now(),
                // Der Baustein-Repeater bringt beim Anlegen einen leeren Eintrag
                // mit. Hier geht es um die Sprachfelder, nicht um die Inhalte.
                'blocks' => [],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $uebersetzung = Page::where('locale', 'en')->where('slug', 'about-us')->first();

        $this->assertNotNull($uebersetzung);
        $this->assertSame($deutsch->uebersetzungs_gruppe, $uebersetzung->uebersetzungs_gruppe);

        // Und sie muss unter ihrer eigenen Adresse erreichbar sein.
        $this->get('/en/about-us')->assertOk()->assertSee('About us');
    }

    public function test_slug_der_wie_eine_sprachkennung_aussieht_wird_abgelehnt(): void
    {
        // Sonst verschluckt die Sprach-Route die Seite, weil sie vor der
        // Sammelroute /{slug} steht — und niemand käme auf die Idee, das zu
        // vermuten.
        Livewire::actingAs($this->redaktion)
            ->test(CreatePage::class)
            ->fillForm([
                'locale' => 'de',
                'titel' => 'Fragen und Antworten',
                'slug' => 'fr',
                'published_at' => now(),
                // Der Baustein-Repeater bringt beim Anlegen einen leeren Eintrag
                // mit. Hier geht es um die Sprachfelder, nicht um die Inhalte.
                'blocks' => [],
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_uebersetzung_darf_den_slug_des_originals_behalten(): void
    {
        $englisch = Language::finden('en');
        $englisch->update(['aktiv' => true]);
        Language::memoLeeren();

        $deutsch = Page::where('locale', 'de')->where('slug', 'kontakt')->firstOrFail();

        // Der frühere globale Unique-Index auf slug hätte das verhindert.
        Livewire::actingAs($this->redaktion)
            ->test(CreatePage::class)
            ->fillForm([
                'locale' => 'en',
                'uebersetzungs_gruppe' => $deutsch->uebersetzungs_gruppe,
                'titel' => 'Contact',
                'slug' => 'kontakt',
                'published_at' => now(),
                // Der Baustein-Repeater bringt beim Anlegen einen leeren Eintrag
                // mit. Hier geht es um die Sprachfelder, nicht um die Inhalte.
                'blocks' => [],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(2, Page::where('slug', 'kontakt')->count());
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

    public function test_alle_bereiche_des_panels_laden(): void
    {
        // Zwei Bereiche warfen einen 500er: Bei sortierbaren Tabellen ruft
        // Filament die Spalten-Closures einmal ohne Datensatz auf, und
        // „$record->rolle" lief dann gegen null. Dieser Test geht alle
        // Bereiche durch, statt auf Zufallsfunde zu warten.
        $this->actingAs($this->redaktion);

        $bereiche = [
            '/admin',
            '/admin/pages',
            '/admin/groups',
            '/admin/team-members',
            '/admin/events',
            '/admin/posts',
            '/admin/categories',
            '/admin/inquiries',
            '/admin/redirects',
        ];

        foreach ($bereiche as $pfad) {
            $this->get($pfad)->assertOk("Panel-Bereich {$pfad} lädt nicht");
        }
    }

    public function test_tabellen_vertragen_einen_fehlenden_datensatz(): void
    {
        // Genau der Fall, der die 500er ausgelöst hat.
        $this->actingAs($this->redaktion);

        Group::query()->delete();
        TeamMember::query()->delete();

        $this->get('/admin/groups')->assertOk();
        $this->get('/admin/team-members')->assertOk();
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
