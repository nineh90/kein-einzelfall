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

    /**
     * Der eigentliche Prüfstein für die nachgerüsteten Baustein-Felder.
     *
     * Filament füllt das Formular aus dem `data`-JSON und schreibt beim
     * Speichern nur zurück, was ein Feld hat. Ein Schlüssel ohne Feld geht
     * dabei still verloren — genau das war der Grund, warum diese Bausteine
     * vorher „nicht pflegbar“ waren. Der Test legt je Typ einen Baustein an,
     * speichert einmal über das echte Panel und prüft, dass die Werte noch da
     * sind. Fehlt für einen Schlüssel ein Feld, fällt er hier heraus.
     */
    public function test_alle_baustein_typen_ueberstehen_das_speichern_im_panel(): void
    {
        $seite = Page::create(['slug' => 'feldprobe', 'titel' => 'Feldprobe', 'published_at' => now()]);

        $bausteine = [
            ['typ' => 'topic_list', 'data' => [
                'titel' => 'Wissen', 'sub' => 'Untertitel',
                'alleUrl' => '/wissen', 'alleLabel' => 'Zum Wissensbereich',
                'themen' => [['label' => 'Rente', 'url' => '/rente', 'icon' => 'shield']],
            ]],
            ['typ' => 'stat_strip', 'data' => [
                'stats' => [['wert' => '2024', 'label' => 'gegründet']],
            ]],
            ['typ' => 'inhalts_hinweis', 'data' => ['thema' => 'Gewaltdarstellung', 'offen' => true]],
            ['typ' => 'embed', 'data' => [
                'titel' => 'Spendenprojekt', 'anbieter' => 'betterplace.org',
                'src' => 'https://project-widget.betterplace.org/w', 'hoehe' => 320,
            ]],
            ['typ' => 'donation_options', 'data' => [
                'titel' => 'Spenden',
                'bank' => ['institut' => 'GLS Bank', 'iban' => 'DE00', 'bic' => 'GENODEM1GLS'],
                'projekte' => [['titel' => 'Beratungsstelle', 'widget' => 'https://betterplace/w']],
                'bescheinigung' => ['email' => 'spenden@kein-einzelfall.de'],
            ]],
            ['typ' => 'group_list', 'data' => ['typ' => 'selbsthilfe', 'titel' => 'Gruppen']],
            ['typ' => 'team_grid', 'data' => ['bereich' => 'Vorstand', 'titel' => 'Vorstand']],
            ['typ' => 'speicher_uebersicht', 'data' => [
                'titel' => 'Gespeicherte Einstellungen', 'einleitung' => 'Was im Browser liegt.',
            ]],
            ['typ' => 'partner_logos', 'data' => [
                'titel' => 'Kooperationen', 'einleitung' => 'Mit wem wir zusammenarbeiten.',
                'partner' => [[
                    'name' => 'Aktion Mensch', 'rolle' => 'Förderer',
                    'url' => 'https://www.aktion-mensch.de',
                    'logo' => '/img/partner/aktion-mensch.svg', 'logo_alt' => '',
                ]],
            ]],
            // KEV-98: Beschwerdemanagement
            ['typ' => 'formular_spalten', 'data' => [
                'spalten' => [
                    ['titel' => 'Externe Kritik', 'absaetze' => ['Kritikabsatz'], 'hand' => 'Handsatz', 'knopf' => 'Knopfsatz', 'art' => 'anfrage'],
                    ['titel' => 'Interne Beschwerde', 'absaetze' => ['Beschwerdeabsatz'], 'art' => 'ombudsstelle'],
                ],
            ]],
            // KEV-134: Abschnitt der Öffentlichkeitsarbeit
            ['typ' => 'oeffentlichkeitsarbeit', 'data' => [
                'titel' => 'Presse', 'einleitung' => 'Wo wir waren.', 'arten' => ['presse', 'interview'],
                'bild' => '/img/oeffentlichkeitsarbeit/petitionen.webp',
                'gruppen' => [['titel' => 'Untergruppe A', 'einleitung' => 'Einleitung A', 'arten' => ['petition_unterstuetzt']]],
            ]],
            // KEV-157: Sätze als Liste, im Panel ein Textfeld mit einer Zeile je Satz
            ['typ' => 'wall_of_shame', 'data' => [
                'saetze' => ['Erster Wandsatz.', 'Zweiter Wandsatz.'],
                'bild' => '/img/wall-of-shame/wand.webp', 'schluss' => 'Schlusswandsatz',
            ]],
            // KEV-156: Kacheln der Unterseiten, nur Überschrift und Schalter
            ['typ' => 'unterseiten', 'data' => ['titel' => 'Kachelprobe', 'aufrufe' => true]],
            // KEV-155: Glossar-Liste, ohne eigene Felder
            ['typ' => 'glossar', 'data' => []],
            // KEV-152: Fristenübersicht, Textabschnitt mit festem Sprungziel
            // und Aufzählung, Schritte mit Sprungziel
            ['typ' => 'fristen', 'data' => []],
            ['typ' => 'text', 'data' => ['titel' => 'Ankerprobe', 'anker' => 'ankerprobe', 'absaetze' => ["Liste:\n- Listenpunkt"]]],
            ['typ' => 'schritte', 'data' => ['titel' => 'Schrittprobe', 'anker' => 'schrittprobe', 'schritte' => [['titel' => 'Erster Schritt']]]],
        ];

        foreach ($bausteine as $i => $baustein) {
            $seite->blocks()->create(['typ' => $baustein['typ'], 'position' => $i, 'data' => $baustein['data']]);
        }

        Livewire::actingAs($this->redaktion)
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->call('save')
            ->assertHasNoErrors();

        // JSON_UNESCAPED_UNICODE, sonst schreibt json die Umlaute als \uXXXX
        // und die Suche nach dem Klartext („gegründet“) schlägt fehl, obwohl
        // der Wert erhalten ist.
        $json = json_encode($seite->fresh()->blocks()->pluck('data')->all(), JSON_UNESCAPED_UNICODE);

        foreach ([
            'Zum Wissensbereich', 'alleUrl', 'Rente',       // Themenliste, camelCase-Schlüssel
            '2024', 'gegründet',                             // Kennzahlen
            'Gewaltdarstellung',                             // Inhaltshinweis
            'betterplace.org', 'project-widget',             // Einbettung
            'GLS Bank', 'GENODEM1GLS', 'Beratungsstelle',    // Spenden (verschachtelt)
            'spenden@kein-einzelfall.de',
            'selbsthilfe', 'Vorstand',                       // Gruppen, Team
            'Aktion Mensch', 'Förderer', 'aktion-mensch.svg', // Partner
            'Was im Browser liegt.',                         // Gespeicherte Einstellungen
            'Kritikabsatz', 'Handsatz', 'Knopfsatz',         // Zwei Wege mit Formular
            'Beschwerdeabsatz',
            'ombudsstelle',
            'Wo wir waren.', 'interview',                    // Öffentlichkeitsarbeit
            'petitionen.webp', 'Untergruppe A', 'Einleitung A', 'petition_unterstuetzt',
            'Erster Wandsatz.', 'Schlusswandsatz', 'wand.webp', // Wall of Shame
            'Kachelprobe',                                   // Unterseiten als Kacheln
            'ankerprobe', 'Listenpunkt', 'schrittprobe',     // Sprungziele, Aufzählung
        ] as $erwartet) {
            $this->assertStringContainsString($erwartet, $json, "Nach dem Speichern fehlt: {$erwartet}");
        }

        // Die Sätze bleiben eine Liste, kein Textblock mit Zeilenumbrüchen.
        $wand = $seite->fresh()->blocks()->where('typ', 'wall_of_shame')->first()->data;
        $this->assertSame(['Erster Wandsatz.', 'Zweiter Wandsatz.'], $wand['saetze']);

        $kacheln = $seite->fresh()->blocks()->where('typ', 'unterseiten')->first()->data;
        $this->assertTrue($kacheln['aufrufe']);
        $this->assertSame(1, $seite->fresh()->blocks()->where('typ', 'glossar')->count());
        $this->assertSame(1, $seite->fresh()->blocks()->where('typ', 'fristen')->count());
    }

    /** KEV-156: Die Triggerwarnung vor einem Bereich pflegt der Verein an der Seite selbst. */
    public function test_verein_pflegt_die_triggerwarnung_eines_bereichs(): void
    {
        $seite = Page::where('slug', 'raum-fuer-ungesagtes')->where('locale', 'de')->firstOrFail();

        Livewire::actingAs($this->redaktion)
            ->test(EditPage::class, ['record' => $seite->getKey()])
            // Ein Absatz je Eintrag; so liegt ein einfacher Repeater im Formular.
            ->fillForm(['triggerwarnung' => [['absatz' => 'Neuer erster Satz.'], ['absatz' => 'Und ein *zweiter*.']]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['Neuer erster Satz.', 'Und ein *zweiter*.'], $seite->fresh()->triggerwarnung);

        // Leer gespeichert: keine eigene Warnung mehr.
        Livewire::actingAs($this->redaktion)
            ->test(EditPage::class, ['record' => $seite->getKey()])
            ->fillForm(['triggerwarnung' => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertStringNotContainsString('id="bereichswarnung"', $this->get('/wall-of-shame')->getContent());
    }

    /**
     * Filament 4 reicht in die Spalten-Funktionen nur Werte mit den Namen
     * `$state` und `$record` herein. Mit `fn ($s)` und `fn ($r)` blieben die
     * Schilder „Art“ und „Status“ leer, und „Sichtbar“ zeigte bei jeder
     * Gruppe, Person und jedem Glossarbegriff ein Kreuz (gefunden am
     * 22.10.2026 bei KEV-134).
     */
    public function test_listen_zeigen_art_status_und_sichtbar(): void
    {
        $gruppe = Group::where('typ', 'selbsthilfe')->whereNotNull('published_at')->firstOrFail();

        Livewire::actingAs($this->redaktion)
            ->test(\App\Filament\Resources\Groups\Pages\ListGroups::class)
            ->assertTableColumnFormattedStateSet('typ', 'Selbsthilfegruppe', $gruppe)
            ->assertTableColumnFormattedStateSet('status', Group::STATUS[$gruppe->status], $gruppe)
            ->assertTableColumnStateSet('published_at', true, $gruppe);

        $person = TeamMember::whereNotNull('published_at')->firstOrFail();

        Livewire::actingAs($this->redaktion)
            ->test(\App\Filament\Resources\TeamMembers\Pages\ListTeamMembers::class)
            ->assertTableColumnStateSet('published_at', true, $person);

        // Und keine Spalten-Funktion mehr mit Namen, die Filament nicht kennt
        foreach (glob(app_path('Filament/Resources/*/Tables/*.php')) as $datei) {
            $this->assertDoesNotMatchRegularExpression('/fn \(\$(s|r)\)/', file_get_contents($datei), basename($datei));
        }
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
