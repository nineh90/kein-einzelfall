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

    /** Die Migration, die die Startseite auf bestehenden Datenbanken nachträgt. */
    private function migration(): object
    {
        return require database_path('migrations/2026_07_30_120000_startseite_als_datensatz_anlegen.php');
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

    public function test_eine_bestehende_datenbank_bekommt_die_startseite_nachgetragen(): void
    {
        /*
         * Der Fehler, der nach dem Umbau auf jedem eingerichteten Rechner
         * auftrat: Seeder laufen nur bei leerer Datenbank, auf dem Server gar
         * nicht. Wer die 24 Seiten schon hatte, bekam die Startseite nie — und
         * damit ein 404 auf „/“.
         *
         * Die Migration trägt sie nach. Hier der Zustand von vorher, echt
         * nachgestellt: Seiten da, Startseite weg.
         */
        Page::where('slug', Page::STARTSEITE_SLUG)->delete();
        $this->get('/')->assertNotFound();

        // Direkt und nicht über `artisan migrate`: In der Testdatenbank sind
        // alle Migrationen schon gelaufen, der Befehl hätte nichts zu tun.
        $this->migration()->up();

        $this->get('/')->assertOk()->assertSee('Keiner soll mehr sagen müssen', false);
    }

    public function test_das_nachtragen_laeuft_zweimal_ohne_schaden(): void
    {
        // Auf einer Datenbank, die die Startseite schon hat, darf die Migration
        // keine zweite anlegen — sonst stünden zwei Seiten auf demselben Slug.
        $this->migration()->up();

        $this->assertSame(1, Page::where('slug', Page::STARTSEITE_SLUG)->count());
    }

    public function test_das_nachtragen_ueberschreibt_keine_gepflegten_texte(): void
    {
        // Ein zweiter Lauf darf nicht zurücksetzen, was der Verein im Panel
        // geändert hat. Nach dem ersten Mal gehört die Seite der Redaktion.
        $aufmacher = $this->startseite()->blocks()->where('typ', 'hero')->firstOrFail();
        $aufmacher->update(['data' => [...$aufmacher->data, 'titel' => 'Vom Verein geändert']]);

        $this->seed(StartseiteSeeder::class);

        $this->get('/')->assertSee('Vom Verein geändert');
    }

    public function test_ohne_datensatz_faellt_die_startseite_nicht_auf_alte_texte_zurueck(): void
    {
        // Ein stiller Rückfall auf fest verdrahtete Texte hiesse: Die Startseite
        // hat wieder zwei Quellen, und niemand merkt, welche gerade gilt.
        Page::query()->delete();

        $this->get('/')->assertNotFound();
    }
}
