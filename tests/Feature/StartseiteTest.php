<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\StartseiteSeeder;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Die Startseite war bis hierher die einzige Seite ohne Datensatz: eine feste
 * Blade-Datei. Der Verein konnte im Panel weder ihre Überschrift noch einen
 * Knopf ändern — gesucht hat er sie trotzdem, und das zu Recht.
 *
 * Diese Tests halten fest, dass sie jetzt eine Seite wie jede andere ist,
 * ohne dabei ihre Adresse aufzugeben.
 */
class StartseiteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StartseiteSeeder::class);
    }

    private function startseite(): Page
    {
        return Page::where('slug', Page::STARTSEITE_SLUG)->firstOrFail();
    }

    public function test_startseite_ist_ein_datensatz_und_liegt_unter_dem_wurzelpfad(): void
    {
        $this->assertSame('/', $this->startseite()->pfad());

        $this->get('/')->assertOk();
    }

    public function test_verein_kann_die_ueberschrift_im_panel_aendern(): void
    {
        /*
         * Der Anlass für die ganze Umstellung: Die Überschrift der Startseite
         * war nirgends im Panel zu finden.
         *
         * Bewusst über das echte Formular und nicht über das Modell — sonst
         * prüfte der Test nur, dass Eloquent speichern kann. Die Frage ist,
         * ob das Feld im Panel überhaupt existiert.
         */
        $seite = $this->startseite();

        $formular = Livewire::actingAs(User::factory()->redaktion()->create())
            ->test(EditPage::class, ['record' => $seite->getKey()]);

        $bausteine = $formular->get('data')['blocks'];
        $schluessel = array_key_first(array_filter($bausteine, fn ($b) => $b['typ'] === 'hero'));

        $this->assertNotNull($schluessel, 'Kein Aufmacher im Formular');

        $formular
            ->set("data.blocks.{$schluessel}.data.titel", 'Eine *ganz neue* Überschrift')
            ->call('save')
            ->assertHasNoErrors();

        $this->get('/')
            ->assertSee('ganz neue', false)
            ->assertDontSee('*ganz neue*', false);
    }

    public function test_speichern_im_panel_laesst_die_startseite_unveraendert(): void
    {
        $seite = $this->startseite();
        $vorher = $seite->blocks()->pluck('data')->toJson();

        Livewire::actingAs(User::factory()->redaktion()->create())
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($vorher, $seite->fresh()->blocks()->pluck('data')->toJson());
    }

    public function test_die_startseite_laesst_sich_im_panel_nicht_kaputtmachen(): void
    {
        /*
         * Zwei Wege, mit einem Klick die Adresse der Website abzuschalten:
         * den Slug ändern (der Aufruf sucht genau danach) oder die Seite
         * löschen. Beides ist im Panel gesperrt — wiederherstellen liesse sich
         * keins von beidem dort.
         */
        $formular = Livewire::actingAs(User::factory()->redaktion()->create())
            ->test(EditPage::class, ['record' => $this->startseite()->getKey()]);

        $formular->assertFormFieldIsDisabled('slug');
        $formular->assertActionHidden('delete');
    }

    public function test_andere_seiten_bleiben_loeschbar(): void
    {
        // Die Sperre gilt der Startseite, nicht der Redaktion.
        $seite = Page::create(['slug' => 'irgendwas', 'titel' => 'Irgendwas', 'published_at' => now()]);

        Livewire::actingAs(User::factory()->redaktion()->create())
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->assertActionVisible('delete');
    }

    public function test_der_slug_der_startseite_leitet_dauerhaft_auf_die_wurzel(): void
    {
        $this->get('/startseite')->assertRedirect('/')->assertStatus(301);
    }

    public function test_startseite_steht_genau_einmal_in_der_sitemap(): void
    {
        // Sie kommt aus dem Datensatz. Stünde sie zusätzlich in der Liste der
        // festen Übersichten, wäre derselbe Eintrag zweimal drin.
        $xml = $this->get('/sitemap.xml')->getContent();

        $this->assertSame(1, substr_count($xml, '<loc>'.url('/').'</loc>'));
        $this->assertStringNotContainsString(url('/startseite'), $xml);
    }

    public function test_startseite_hat_genau_eine_ueberschrift_erster_ordnung(): void
    {
        // Sie trägt ihre Überschrift im Aufmacher und bekommt deshalb keinen
        // Seitenkopf. Beides zusammen wäre eine zweite <h1>.
        $html = $this->get('/')->getContent();

        $this->assertSame(1, preg_match_all('/<h1[^>]*>/', $html));
    }

    public function test_startseite_hat_keine_brotkrumen(): void
    {
        // „Start › Startseite“ wäre ein Weg, der im Kreis führt.
        $this->get('/')->assertDontSee('aria-label="'.__('rahmen.sie_sind_hier').'"', false);
    }

    public function test_ohne_datensatz_faellt_die_startseite_nicht_auf_alte_texte_zurueck(): void
    {
        // Ein stiller Rückfall auf fest verdrahtete Texte hiesse: Die Startseite
        // hat wieder zwei Quellen, und niemand merkt, welche gerade gilt.
        Page::query()->delete();

        $this->get('/')->assertNotFound();
    }
}
