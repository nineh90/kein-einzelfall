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
 * Messen, Petitionen, Presse (KEV-134): als kleine Karten je Art, jede mit
 * eigener Seite. Seit KEV-238 je Thema auf einer eigenen Seite
 * (/ehrenamtsmessen, /petitionen, /presse), die Übersicht zeigt Kacheln.
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
        $html = $this->get('/ehrenamtsmessen')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h1[^>]*>\s*Ehrenamtsmessen\s*</h1>#', $html);
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

        // Damit die Seite beim Laden nicht springt: Maße am großen Bild (das
        // Original aus dem Drive), festes Seitenverhältnis im Raster (KEV-132)
        $this->assertStringContainsString('width="1600" height="1200"', $html);
        $this->assertStringContainsString('aspect-[3/4] w-full object-cover', $html);
        $this->assertStringContainsString('Weitere Fotos', $html);

        // Brotkrumen zurück zur Übersicht
        $this->assertMatchesRegularExpression('#href="[^"]*/oeffentlichkeitsarbeit"[^>]*>\s*Öffentlichkeitsarbeit#', $html);
    }

    public function test_unveroeffentlicht_oder_unbekannt_gibt_404(): void
    {
        OutreachItem::where('slug', 'ehrenamtsmesse-bayreuth-2026')->update(['published_at' => null]);
        // Ohne veröffentlichte Messe überhaupt (seit KEV-133 auch Lübeck)
        OutreachItem::where('art', 'messe')->where('slug', '!=', 'ehrenamtsmesse-bayreuth-2026')->update(['published_at' => null]);

        $this->get(self::BAYREUTH)->assertNotFound();
        $this->get('/oeffentlichkeitsarbeit/gibt-es-nicht')->assertNotFound();

        // Ohne veröffentlichte Messe keine Karten
        $this->get('/ehrenamtsmessen')->assertOk()->assertDontSee('ehrenamtsmesse-bayreuth-2026', false);
    }

    /** Neueste zuerst, ohne Datum ans Ende; nur die Art des Abschnitts. */
    public function test_reihenfolge_und_art(): void
    {
        $neu = fn (array $d) => OutreachItem::create($d + ['art' => 'messe', 'published_at' => now()]);
        $neu(['slug' => 'messe-berlin-2026', 'titel' => 'Messe Berlin', 'datum' => '2026-04-25']);
        $neu(['slug' => 'messe-ohne-datum', 'titel' => 'Messe ohne Datum']);
        $neu(['slug' => 'messe-luebeck-2026', 'titel' => 'Messe Lübeck', 'datum' => '2026-09-06']);
        $neu(['slug' => 'eine-aktion', 'titel' => 'Eine Aktion', 'art' => 'aktion']);

        $html = $this->get('/ehrenamtsmessen')->assertOk()->getContent();

        $stellen = array_map(fn ($t) => strpos($html, $t), [
            'Ehrenamtsmesse Bayreuth', 'Messe Lübeck', 'Messe Berlin', 'Messe ohne Datum',
        ]);
        $this->assertNotContains(false, $stellen);
        $this->assertSame($stellen, collect($stellen)->sort()->values()->all());

        // Aktionen stehen nicht unter „Messen“, einen Abschnitt dafür gibt es noch nicht
        $this->assertStringNotContainsString('Eine Aktion', $html);
        $this->get('/oeffentlichkeitsarbeit/eine-aktion')->assertOk();
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

        $this->get('/en/ehrenamtsmessen')->assertOk()
            ->assertSee('Volunteering fairs')
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
                'titel' => 'Ehrenamtsmesse Kiel',
                'slug' => 'ehrenamtsmesse-kiel-2027',
                'datum' => '2027-03-13',
                'ort' => 'Kiel',
                'beschreibung' => '<p>Mit viel Freude …</p>',
                'fotos' => [['bild' => [UploadedFile::fake()->createWithContent(
                    'stand.png', file_get_contents(public_path('img/logo.png')),
                )], 'alt' => 'Unser Stand']],
                'published_at' => now()->subMinute(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $messe = OutreachItem::where('slug', 'ehrenamtsmesse-kiel-2027')->firstOrFail();
        $foto = $messe->fotoListe()[0];
        $this->assertMatchesRegularExpression('#^/storage/medien/bilder/\d{4}/\d{2}/stand-[a-z0-9]{6}\.png$#', $foto['bild']);
        $this->assertSame('Unser Stand', $foto['alt']);

        $this->get('/ehrenamtsmessen')->assertSee('Ehrenamtsmesse Kiel');
        $this->get('/oeffentlichkeitsarbeit/ehrenamtsmesse-kiel-2027')->assertOk()->assertSee('Mit viel Freude');
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
        $seite = Page::where('slug', 'ehrenamtsmessen')->where('locale', 'de')->firstOrFail();
        $block = $seite->blocks()->where('typ', 'oeffentlichkeitsarbeit')->firstOrFail();

        // Ohne Überschrift und Bild: Beides steht im Titelbild der Seite (KEV-238).
        $this->assertSame(array_diff_key(OeffentlichkeitsarbeitSeeder::MESSEN, array_flip(['titel', 'bild'])), $block->data);
        $this->assertFalse($block->zeigtNichts());

        $block->update(['data' => ['titel' => 'Aktionen', 'arten' => ['aktion']]]);
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

    /**
     * KEV-136: „Presse“ mit Taddis Einleitung, seit KEV-238 auf /presse.
     * Zeigt Presseartikel und Interviews, deshalb mit Schild je Karte.
     */
    public function test_presse_abschnitt(): void
    {
        $presse = $this->get('/presse')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h1[^>]*>\s*Presse\s*</h1>#', $presse);
        $this->assertStringContainsString('<p>Hier findest du eine Auswahl unserer Presseartikel, Interviews und '
            .'Berichte über KE!N EINZELFALL e.V. und unsere Arbeit.</p>', $presse);
        $this->assertStringContainsString('<a href="mailto:presse@kein-einzelfall.de"', $presse);

        // Neueste zuerst: Trude Kuh (12/2025) vor dem stern (06/2025), je mit Schild und Medium
        $this->assertLessThan(strpos($presse, 'Staat streicht Hilfen'), strpos($presse, 'Unser Besuch bei Trude Kuh'));
        $this->assertStringContainsString('>Interview</span>', $presse);
        $this->assertStringContainsString('>Presseartikel</span>', $presse);
        $this->assertStringContainsString('<time datetime="2025-06-25">25.06.2025</time>', $presse);
        $this->assertStringContainsString('<span>stern</span>', $presse);

        // Die Messen bleiben ohne Schild und ohne Presse
        $messen = $this->get('/ehrenamtsmessen')->getContent();
        $this->assertStringNotContainsString('Staat streicht Hilfen', $messen);
        $this->assertStringNotContainsString('>Messe</span>', $messen);
    }

    public function test_seiten_der_presse(): void
    {
        $this->get('/oeffentlichkeitsarbeit/stern-fonds-sexueller-missbrauch-2025')->assertOk()
            ->assertSee('Auch KE!N EINZELFALL kommt darin zu Wort')
            ->assertSee('25. Juni 2025')
            ->assertSee('href="https://www.stern.de/panorama/wissen/fonds--sexueller-missbrauch--wird-gestrichen--die-opfer-sind-enttaeuscht-35920302.html"', false)
            ->assertSee('rel="noreferrer noopener"', false)
            ->assertSee('Zum Artikel im stern')
            // Kein Foto: Das aus dem Artikel gehört dem Fotografen (Checkliste)
            ->assertDontSee('<img src="/img/oeffentlichkeitsarbeit/', false);

        // Ohne Video kein Satz, der auf eines zeigt
        $this->get('/oeffentlichkeitsarbeit/besuch-bei-trude-kuh-2025')->assertOk()
            ->assertSee('ein professionelles Studio')
            ->assertSee('Zum Beitrag bei Trude Kuh')
            ->assertDontSee('im Video');
    }

    public function test_migration_presse_nur_einmal(): void
    {
        $migration = require database_path('migrations/2026_10_22_120000_oeffentlichkeitsarbeit_presse.php');
        $migration->up();
        $migration->up();

        // Der Abschnitt steht auf /presse und kommt nicht noch einmal auf die Übersicht.
        $this->assertSame(0, Page::where('slug', 'oeffentlichkeitsarbeit')->where('locale', 'de')->firstOrFail()
            ->blocks()->where('typ', 'oeffentlichkeitsarbeit')->count());
        $this->assertSame(1, Page::where('slug', 'presse')->where('locale', 'de')->firstOrFail()
            ->blocks()->where('typ', 'oeffentlichkeitsarbeit')->count());
        $this->assertSame(1, OutreachItem::where('slug', 'stern-fonds-sexueller-missbrauch-2025')->count());
        $this->assertSame(1, OutreachItem::where('slug', 'besuch-bei-trude-kuh-2025')->count());
    }

    public function test_presse_auf_englisch(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $this->get('/en/presse')->assertOk()
            ->assertSee('Press')
            ->assertSee('Please send press enquiries to:')
            ->assertSee('mailto:presse@kein-einzelfall.de', false)
            ->assertSee('Our visit to Trude Kuh')
            ->assertSee('State cuts support for those affected by sexualised violence');
    }

    /**
     * Ein Abschnitt kann mehrere Arten zeigen (Mehrfachauswahl unter
     * `arten`). Beide Abschnitte überstehen das Speichern im Panel; unter
     * `data.art` lag es mit dem Hinweis-Baustein im Streit.
     */
    public function test_abschnitte_ueberstehen_speichern_im_panel(): void
    {
        $arten = [];

        foreach (['ehrenamtsmessen', 'petitionen', 'presse'] as $slug) {
            $seite = Page::where('slug', $slug)->where('locale', 'de')->firstOrFail();

            Livewire::actingAs(User::factory()->redaktion()->create())
                ->test(\App\Filament\Resources\Pages\Pages\EditPage::class, ['record' => $seite->getKey()])
                ->call('save')
                ->assertHasNoFormErrors();

            $block = $seite->blocks()->where('typ', 'oeffentlichkeitsarbeit')->firstOrFail();
            $arten[$slug] = array_column(\App\Models\PageBlock::oeffentlichkeitsarbeitGruppen($block->data), 'arten');
        }

        $this->assertSame([['messe']], $arten['ehrenamtsmessen']);
        $this->assertSame([['petition'], ['petition_unterstuetzt']], $arten['petitionen']);
        $this->assertSame([['presse', 'interview']], $arten['presse']);

        // Die Untergruppen der Petitionen überstehen das Speichern
        $petitionen = Page::where('slug', 'petitionen')->where('locale', 'de')->firstOrFail()
            ->blocks()->where('typ', 'oeffentlichkeitsarbeit')->firstOrFail();
        $this->assertSame(['Initiierte Petitionen', 'Unterstützte Petitionen'], array_column($petitionen->data['gruppen'], 'titel'));

        $this->get('/petitionen')->assertSee('id="oa-initiierte-petitionen"', false);
        $this->get('/presse')->assertSee('Unser Besuch bei Trude Kuh');
    }

    /** Auf dem Server steht „Messen“ noch mit `art: messe` (KEV-134). */
    public function test_migration_stellt_art_auf_arten_um(): void
    {
        $block = Page::where('slug', 'ehrenamtsmessen')->where('locale', 'de')->firstOrFail()
            ->blocks()->where('typ', 'oeffentlichkeitsarbeit')->firstOrFail();
        $block->update(['data' => ['titel' => 'Messen', 'art' => 'messe']]);

        // Das alte Format wird auch vor der Umstellung gelesen
        $this->get('/ehrenamtsmessen')->assertSee('id="oa-messen"', false);

        (require database_path('migrations/2026_10_22_120000_oeffentlichkeitsarbeit_presse.php'))->up();

        $this->assertSame(['titel' => 'Messen', 'arten' => ['messe']], $block->fresh()->data);
        $this->get('/ehrenamtsmessen')->assertSee('id="oa-messen"', false);
        $this->get('/presse')->assertSee('Staat streicht Hilfen');
    }

    /**
     * KEV-135: „Petitionen“ mit zwei Untergruppen. Seit KEV-238 auf
     * /petitionen, Taddis Bild ist dort das Titelbild; Gruppen h2, Karten h3.
     */
    public function test_petitionen_abschnitt(): void
    {
        $abschnitt = $this->get('/petitionen')->assertOk()->getContent();

        $this->assertStringContainsString('<p>Deine Stimme zählt – gemeinsam KE!N EINZELFALL.</p>', $abschnitt);
        $this->assertStringContainsString('src="/img/titelbilder/petitionen.webp', $abschnitt);
        $this->assertStringNotContainsString('/img/oeffentlichkeitsarbeit/petitionen.webp', $abschnitt);

        $initiiert = strpos($abschnitt, 'id="oa-initiierte-petitionen"');
        $unterstuetzt = strpos($abschnitt, 'id="oa-unterstutzte-petitionen"');
        $this->assertNotFalse($initiiert);
        $this->assertNotFalse($unterstuetzt);
        $this->assertMatchesRegularExpression('#<h2 id="oa-initiierte-petitionen"[^>]*>Initiierte Petitionen</h2>#', $abschnitt);

        // Jede Petition in ihrer Gruppe, als h3
        $this->assertGreaterThan($initiiert, strpos($abschnitt, 'Erst Versorgung sichern – dann Pflegeleistungen verändern!'));
        $this->assertLessThan($unterstuetzt, strpos($abschnitt, 'Erst Versorgung sichern – dann Pflegeleistungen verändern!'));
        $this->assertGreaterThan($unterstuetzt, strpos($abschnitt, 'Tour41 e. V.'));
        $this->assertStringContainsString('<h3 class="mt-1 font-display', $abschnitt);

        // Eine Art je Gruppe: kein Schild
        $this->assertStringNotContainsString('>Eigene Petition</span>', $abschnitt);
    }

    public function test_seiten_der_petitionen(): void
    {
        $this->get('/oeffentlichkeitsarbeit/petition-erst-versorgung-sichern')->assertOk()
            ->assertSee('Eigene Petition')
            ->assertSee('5. Oktober 2026')
            ->assertSee('href="https://www.openpetition.de/petition/online/erst-versorgung-sichern-dann-pflegeleistungen-veraendern"', false)
            ->assertSee('Petition unterschreiben');

        $this->get('/oeffentlichkeitsarbeit/petition-tour41-fonds-sexueller-missbrauch')->assertOk()
            ->assertSee('Unterstützte Petition')
            ->assertSee('Die Petition des Tour41 e. V. setzt sich für den Erhalt')
            ->assertSee('href="https://tour41.net/aktiv/aktuelle-petition-updates/"', false)
            ->assertSee('Zur Petition bei Tour41');
    }

    public function test_migration_petitionen_nur_einmal_und_hinter_messen(): void
    {
        $migration = require database_path('migrations/2026_10_22_140000_oeffentlichkeitsarbeit_petitionen.php');
        $migration->up();
        $migration->up();

        // Steht schon auf /petitionen, kommt nicht noch einmal auf die Übersicht.
        $this->assertSame(0, Page::where('slug', 'oeffentlichkeitsarbeit')->where('locale', 'de')->firstOrFail()
            ->blocks()->where('typ', 'oeffentlichkeitsarbeit')->count());
        $this->assertSame(1, Page::where('slug', 'petitionen')->where('locale', 'de')->firstOrFail()
            ->blocks()->where('typ', 'oeffentlichkeitsarbeit')->count());
        $this->assertSame(1, OutreachItem::where('slug', 'petition-erst-versorgung-sichern')->count());
    }

    public function test_petitionen_auf_englisch(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $this->get('/en/petitionen')->assertOk()
            ->assertSee('Petitions')
            ->assertSee('Your voice counts – together KE!N EINZELFALL.')
            ->assertSee('Petitions we started')
            ->assertSee('Petitions we support')
            ->assertSee('Secure care first – then change care benefits!');

        $this->get('/en/oeffentlichkeitsarbeit/petition-tour41-fonds-sexueller-missbrauch')->assertOk()
            ->assertSee('Supported petition')
            ->assertSee('must be preserved');
    }

    public function test_gruppen_ohne_eintraege_zeigen_nichts(): void
    {
        $leer = new \App\Models\PageBlock(['typ' => 'oeffentlichkeitsarbeit', 'data' => [
            'titel' => 'Leer', 'gruppen' => [['titel' => 'A', 'arten' => ['aktion']], ['titel' => 'B', 'arten' => ['veranstaltung']]],
        ]]);
        $this->assertTrue($leer->zeigtNichts());

        $halb = new \App\Models\PageBlock(['typ' => 'oeffentlichkeitsarbeit', 'data' => [
            'titel' => 'Halb', 'gruppen' => [['titel' => 'A', 'arten' => ['aktion']], ['titel' => 'B', 'arten' => ['petition']]],
        ]]);
        $this->assertFalse($halb->zeigtNichts());
    }

    /** KEV-133: Ehrenamtsmesse Lübeck, neben Bayreuth unter „Messen“ (neueste zuerst). */
    public function test_ehrenamtsmesse_luebeck(): void
    {
        $messen = $this->get('/ehrenamtsmessen')->assertOk()->getContent();
        $this->assertLessThan(strpos($messen, 'Ehrenamtsmesse Lübeck'), strpos($messen, 'Ehrenamtsmesse Bayreuth'));
        $this->assertStringContainsString('<time datetime="2026-09-06">06.09.2026</time>', $messen);

        $seite = $this->get('/oeffentlichkeitsarbeit/ehrenamtsmesse-luebeck-2026')->assertOk()->getContent();
        $this->assertStringContainsString('auf der Ehrenamtsmesse in Lübeck präsentiert.', $seite);
        $this->assertStringNotContainsString('in Berlin', $seite);
        $this->assertStringContainsString('<p><strong>Wir haben viele gute Begegnungen mitgenommen und freuen uns schon '
            .'jetzt auf das nächste Jahr.</strong></p>', $seite);
        foreach ([1, 2, 3, 4] as $nr) {
            $this->assertMatchesRegularExpression('#src="/img/oeffentlichkeitsarbeit/ehrenamtsmesse-luebeck-2026-'.$nr.'\.webp\?v=[^"]+" alt="[^"]{20,}"#', $seite);
        }
    }

    public function test_migration_luebeck_einmal_und_englisch(): void
    {
        $migration = require database_path('migrations/2026_10_22_160000_oeffentlichkeitsarbeit_messe_luebeck.php');
        $migration->up();
        $migration->up();
        $this->assertSame(1, OutreachItem::where('slug', 'ehrenamtsmesse-luebeck-2026')->count());

        foreach (OeffentlichkeitsarbeitSeeder::MESSE_LUEBECK['fotos'] as $foto) {
            $this->assertFileExists(public_path(ltrim($foto['bild'], '/')));
        }

        $this->seed(UebersetzungenSeeder::class);
        $this->get('/en/oeffentlichkeitsarbeit/ehrenamtsmesse-luebeck-2026')->assertOk()
            ->assertSee('Volunteering fair Lübeck')
            ->assertSee('6 September 2026')
            ->assertSee('We took away many good encounters')
            ->assertSee('View from above into the fair hall');
    }

    /**
     * KEV-132: Ehrenamtsmesse Berlin mit sechs Fotos, die Seite als Galerie:
     * jedes Foto ein Link auf die große Fassung, die übrigen als Raster.
     */
    public function test_ehrenamtsmesse_berlin_mit_galerie(): void
    {
        $messen = $this->get('/ehrenamtsmessen')->assertOk()->getContent();
        // Neueste zuerst: Bayreuth, Lübeck, Berlin
        $this->assertTrue(strpos($messen, 'Ehrenamtsmesse Lübeck') < strpos($messen, 'Ehrenamtsmesse Berlin'));
        $this->assertStringContainsString('<time datetime="2026-04-25">25.04.2026</time>', $messen);

        $seite = $this->get('/oeffentlichkeitsarbeit/ehrenamtsmesse-berlin-2026')->assertOk()->getContent();
        $this->assertStringContainsString('<p>Ein schöner Tag auf der Ehrenamtsmesse in Berlin!</p>', $seite);
        $this->assertStringContainsString('im Berliner Landesbeirat für Menschen mit Behinderungen.', $seite);
        $this->assertStringNotContainsString('<strong>', substr($seite, strpos($seite, '<h1'), strpos($seite, 'weitere-fotos') - strpos($seite, '<h1')));

        // Galerie: Beschriftungen für das Skript, sechs Links auf die Bilder
        $this->assertStringContainsString('data-galerie', $seite);
        $this->assertStringContainsString('data-zaehler=":nr von :gesamt"', $seite);
        $this->assertSame(6, preg_match_all('#<a href="/img/oeffentlichkeitsarbeit/ehrenamtsmesse-berlin-2026-\d\.webp\?v=[^"]+" data-galerie-bild#', $seite));
        $this->assertSame(6, substr_count($seite, '<span class="sr-only">Foto vergrößern</span>'));
        $this->assertStringContainsString('<ul class="grid grid-cols-2 gap-3 sm:grid-cols-3">', $seite);
    }

    public function test_migration_berlin_einmal_und_englisch(): void
    {
        $migration = require database_path('migrations/2026_10_22_180000_oeffentlichkeitsarbeit_messe_berlin.php');
        $migration->up();
        $migration->up();
        $this->assertSame(1, OutreachItem::where('slug', 'ehrenamtsmesse-berlin-2026')->count());

        foreach (OeffentlichkeitsarbeitSeeder::MESSE_BERLIN['fotos'] as $foto) {
            $this->assertFileExists(public_path(ltrim($foto['bild'], '/')));
        }

        $this->seed(UebersetzungenSeeder::class);
        $this->get('/en/oeffentlichkeitsarbeit/ehrenamtsmesse-berlin-2026')->assertOk()
            ->assertSee('Volunteering fair Berlin')
            ->assertSee('25 April 2026')
            ->assertSee('Berlin State Advisory Council for People with Disabilities')
            ->assertSee('data-zaehler=":nr of :gesamt"', false)
            ->assertSee('Enlarge photo');
    }

    /**
     * KEV-131: „Ehrenamtsmessen“ mit Taddis Einleitung und ihrem Bild, seit
     * KEV-238 als eigene Seite mit dem Bild als Titelbild.
     */
    public function test_abschnitt_ehrenamtsmessen(): void
    {
        $abschnitt = $this->get('/ehrenamtsmessen')->assertOk()->getContent();

        $this->assertStringContainsString('<p>Wir zeigen gern, wer wir sind, wofür wir stehen und was wir bewegen möchten.', $abschnitt);
        $this->assertStringContainsString('<p>Wir freuen uns auf jede Begegnung, aus der etwas Neues entstehen kann.</p>', $abschnitt);
        $this->assertStringContainsString('src="/img/titelbilder/ehrenamtsmessen.webp', $abschnitt);
        $this->assertStringNotContainsString('>Messen<', $abschnitt);
    }

    /** Die Migration ergänzt nur den unveränderten Abschnitt „Messen“. */
    public function test_migration_ehrenamtsmessen_nur_wenn_unveraendert(): void
    {
        // Stand vor KEV-131 (und vor KEV-238): der Abschnitt auf der Übersicht
        $seite = Page::where('slug', 'oeffentlichkeitsarbeit')->where('locale', 'de')->firstOrFail();
        $block = $seite->blocks()->create(['typ' => 'oeffentlichkeitsarbeit', 'position' => 9, 'data' => ['titel' => 'Messen', 'arten' => ['messe']]]);
        $migration = require database_path('migrations/2026_10_22_200000_oeffentlichkeitsarbeit_ehrenamtsmessen.php');

        $migration->up();
        $this->assertEquals(OeffentlichkeitsarbeitSeeder::MESSEN, $block->fresh()->data);

        // Vom Verein umbenannt: bleibt
        $block->update(['data' => ['titel' => 'Unsere Messen', 'arten' => ['messe']]]);
        $migration->up();
        $this->assertSame(['titel' => 'Unsere Messen', 'arten' => ['messe']], $block->fresh()->data);
    }

    /** Fehlt der Abschnitt Petitionen ganz, legt die alte Migration ihn wieder an (auf der Übersicht, hinter den Messen gibt es dort keine mehr). */
    public function test_petitionen_hinter_messen_auch_mit_neuem_namen(): void
    {
        Page::where('slug', 'petitionen')->where('locale', 'de')->firstOrFail()
            ->blocks()->where('typ', 'oeffentlichkeitsarbeit')->delete();

        (require database_path('migrations/2026_10_22_140000_oeffentlichkeitsarbeit_petitionen.php'))->up();

        $titel = Page::where('slug', 'oeffentlichkeitsarbeit')->where('locale', 'de')->firstOrFail()
            ->blocks()->where('typ', 'oeffentlichkeitsarbeit')->get()->map(fn ($b) => $b->data['titel'])->all();
        $this->assertSame(['Petitionen'], $titel);
    }

    public function test_ehrenamtsmessen_auf_englisch(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        // Die Kachel auf der Übersicht, mit Titelbild
        $this->get('/en/oeffentlichkeitsarbeit')->assertOk()
            ->assertSee('Volunteering fairs')
            ->assertSee('/en/ehrenamtsmessen', false)
            ->assertSee('ehrenamtsmessen', false);

        $this->get('/en/ehrenamtsmessen')->assertOk()
            ->assertSee('We like to show who we are, what we stand for')
            ->assertSee('ehrenamtsmessen.webp', false);
    }

    /**
     * KEV-238: Taddi wollte jedes Thema auf einer eigenen Seite, mit ihren
     * Bildern als Titelbild. Die Übersicht zeigt Einleitung und Kacheln,
     * Brotkrumen und „Mehr zu“ kennen die neue Ebene.
     */
    public function test_themen_auf_eigenen_seiten(): void
    {
        $uebersicht = Page::where('slug', 'oeffentlichkeitsarbeit')->where('locale', 'de')->firstOrFail();
        $this->assertSame(['text', 'unterseiten'], $uebersicht->blocks()->orderBy('position')->pluck('typ')->all());

        $html = $this->get('/oeffentlichkeitsarbeit')->assertOk()->getContent();
        foreach (['ehrenamtsmessen', 'petitionen', 'presse'] as $slug) {
            $this->assertStringContainsString('href="/'.$slug.'"', $html);
            $this->assertFileExists(public_path("img/titelbilder/{$slug}-1000.webp"));

            $seite = $this->get('/'.$slug)->assertOk()->getContent();
            $this->assertStringContainsString("src=\"/img/titelbilder/{$slug}.webp", $seite);
            // Brotkrumen über die Übersicht, Überzeile „Öffentlichkeitsarbeit“
            $this->assertMatchesRegularExpression('#href="/oeffentlichkeitsarbeit"[^>]*>\s*Öffentlichkeitsarbeit#u', $seite);
            $this->assertStringContainsString('Mehr zu „Öffentlichkeitsarbeit“', $seite);
            // Im Menü ist „Gruppen & Veranstaltungen“ markiert
            // (Mega-Menü: der Bereich ist ein <summary> mit aria-current="true")
            $this->assertMatchesRegularExpression('/<summary\s+aria-current="true"[^>]*>\s*Gruppen &amp; Veranstaltungen/u', $seite);
        }
        $this->assertStringNotContainsString('id="oa-', $html);

        // Ein Eintrag führt über seine Themenseite zurück
        $bayreuth = $this->get(self::BAYREUTH)->getContent();
        $this->assertMatchesRegularExpression('#href="[^"]*/ehrenamtsmessen"[^>]*>\s*Ehrenamtsmessen#u', $bayreuth);
    }
}
