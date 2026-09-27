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

    /**
     * KEV-10: Die Spendenmöglichkeit steht auf der Startseite selbst.
     *
     * Vorher gab es dort drei Links auf /spenden, aber nirgends Konto oder
     * PayPal — wer spenden wollte, musste erst die Unterseite finden.
     */
    public function test_startseite_zeigt_die_spendenmoeglichkeit_selbst(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('DE79 8306 5408 0006 8893 10', $html, 'IBAN fehlt');
        $this->assertStringContainsString('paypal.com/donate', $html, 'PayPal-Link fehlt');
        // Der QR-Code für die Banking-App — Wunsch aus der Besprechung vom 02.08.2026.
        $this->assertMatchesRegularExpression('/<div class="girocode-flaeche[^>]*role="img"/', $html);
        // Und der Weg zur vollständigen Seite (betterplace, Spendenbescheinigung).
        $this->assertStringContainsString('Alle Spendenmöglichkeiten', $html);
    }

    public function test_spendenmoeglichkeit_steht_vor_dem_hinweisband(): void
    {
        // Das Band fasst danach beide Wege zusammen — Spenden und Mitgliedschaft.
        $typen = $this->startseite()->blocks()->pluck('typ')->all();

        $this->assertLessThan(
            array_search('cta_band', $typen, true),
            array_search('donation_options', $typen, true),
        );
    }

    public function test_eine_bestehende_startseite_bekommt_die_spendenmoeglichkeit_nachgetragen(): void
    {
        /*
         * Der Zustand jeder eingerichteten Installation vor KEV-10: Startseite
         * da, Spendenbaustein nicht. Der Seeder rührt sie nicht mehr an — die
         * Migration muss es tun, und zwar an derselben Stelle wie der Seeder.
         */
        $seite = $this->startseite();
        $seite->blocks()->where('typ', 'donation_options')->delete();
        $this->get('/')->assertDontSee('DE79 8306', false);

        $this->spendenMigration()->up();

        $this->get('/')->assertSee('DE79 8306', false);

        $typen = $seite->fresh()->blocks()->pluck('typ')->all();
        $this->assertSame(
            ['hero', 'hilfe_box', 'quick_access', 'text', 'text', 'donation_options', 'cta_band', 'contact_close'],
            $typen,
        );
        // Keine zwei Bausteine auf derselben Position — sonst wäre die
        // Reihenfolge Zufall. Lückenlos müssen sie nicht sein.
        $positionen = $seite->fresh()->blocks()->pluck('position')->all();
        $this->assertSame($positionen, array_values(array_unique($positionen)));
    }

    public function test_das_nachtragen_erreicht_auch_die_zweite_sprachfassung(): void
    {
        // Die deutsche Startseite hat den Baustein schon; die englische nicht.
        // Ein ->each() in der Migration brach hier nach der ersten Seite ab.
        $de = $this->startseite();
        $en = $de->replicate()->fill(['locale' => 'en', 'uebersetzungs_gruppe' => $de->uebersetzungs_gruppe]);
        $en->save();
        $en->blocks()->create(['typ' => 'cta_band', 'position' => 0, 'data' => ['zitat' => 'Band']]);

        $this->spendenMigration()->up();

        $this->assertSame(['donation_options', 'cta_band'], $en->fresh()->blocks()->pluck('typ')->all());
    }

    public function test_das_nachtragen_der_spendenmoeglichkeit_laeuft_zweimal_ohne_schaden(): void
    {
        $this->spendenMigration()->up();
        $this->spendenMigration()->up();

        $this->assertSame(1, $this->startseite()->blocks()->where('typ', 'donation_options')->count());
    }

    public function test_das_nachtragen_landet_ohne_hinweisband_vor_dem_kontaktabschluss(): void
    {
        // Der Verein kann das Band im Panel löschen. Dann darf der Baustein
        // trotzdem nicht ans Ende hinter den Kontaktabschluss rutschen.
        $seite = $this->startseite();
        $seite->blocks()->whereIn('typ', ['donation_options', 'cta_band'])->delete();

        $this->spendenMigration()->up();

        $typen = $seite->fresh()->blocks()->pluck('typ')->all();
        $this->assertSame('contact_close', end($typen));
        $this->assertSame('donation_options', prev($typen));
    }

    /**
     * KEV-56: Text von Taddi mit Leitsatz in Handschrift, nur ein grüner
     * Strich über „Jetzt spenden“, und nicht mehr derselbe Satz wie auf der
     * Einstiegskarte „Spenden“ darüber.
     */
    public function test_spendenabschnitt_hat_eigenen_text_und_leitsatz(): void
    {
        $html = $this->get('/')->getContent();
        $abschnitt = substr($html, strpos($html, 'id="spenden"'));
        $abschnitt = substr($abschnitt, 0, strpos($abschnitt, '</section>'));

        $this->assertStringContainsString('Gute Ideen brauchen Rückenwind.', $abschnitt);
        $this->assertStringContainsString('Deine Spende hilft uns, Projekte umzusetzen', $abschnitt);
        $this->assertStringNotContainsString('Mit Deiner Spende hilfst Du uns', $abschnitt);
        $this->assertSame(1, substr_count($abschnitt, 'bg-green-brand'), 'zwei Striche über der Überschrift');
    }

    public function test_bestehende_startseite_bekommt_den_neuen_spendentext(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'donation_options')->first();
        // Stand vor KEV-56: Dachzeile, alter Text, kein Leitsatz.
        $block->update(['data' => [
            'eyebrow' => 'Spenden',
            'text' => 'Mit Deiner Spende hilfst Du uns, kostenfreies Wissen und Aufklärung zu leisten, '
                .'Sichtbarkeit und Gehör zu schaffen, sowie eine Informationsplattform aufzustellen '
                .'und ein Netzwerk zu bilden.',
        ] + array_diff_key($block->data, ['hand' => 1])]);

        $migration = require database_path('migrations/2026_09_27_130000_spendentext_startseite_erneuern.php');
        $migration->up();

        $data = $block->fresh()->data;
        $this->assertArrayNotHasKey('eyebrow', $data);
        $this->assertSame('Gute Ideen brauchen Rückenwind.', $data['hand']);
        $this->assertStringStartsWith('Deine Spende hilft uns', $data['text']);
    }

    public function test_gepflegter_spendentext_bleibt_stehen(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'donation_options')->first();
        $block->update(['data' => ['eyebrow' => 'Spenden', 'text' => 'Vom Verein geändert'] + $block->data]);

        $migration = require database_path('migrations/2026_09_27_130000_spendentext_startseite_erneuern.php');
        $migration->up();

        $this->assertSame('Vom Verein geändert', $block->fresh()->data['text']);
        $this->assertSame('Spenden', $block->fresh()->data['eyebrow']);
    }

    /** KEV-55: neuer Mitglieder-Text von Taddi, Leitsatz und Knopf bleiben. */
    public function test_bestehende_startseite_bekommt_den_neuen_mitgliedertext(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'text')->get()
            ->first(fn ($block) => ($block->data['cta']['url'] ?? null) === '/mitgliedschaft');
        $block->update(['data' => array_replace($block->data, ['absaetze' => [
            'Jede Mitgliedschaft stärkt unsere Arbeit. Mit jeder Mitgliedschaft wächst unsere Chance auf Veränderung.',
        ]])]);
        $schluessel = array_keys($block->fresh()->data);

        $migration = require database_path('migrations/2026_09_27_140000_mitglieder_text_erneuern.php');
        $migration->up();

        $data = $block->fresh()->data;
        $this->assertStringStartsWith('Du fühlst Dich mit unserer Vision verbunden?', $data['absaetze'][0]);
        $this->assertSame('Werde Teil unseres Netzwerks!', $data['hand']);
        // Reihenfolge bleibt, sonst meldete das Panel beim Speichern eine Änderung.
        $this->assertSame($schluessel, array_keys($data));
    }

    /** KEV-54: „Verein“ statt „Vereinsarbeit“, erster Satz fett, neuer Leitsatz. */
    public function test_vereinsabschnitt_zeigt_taddis_text(): void
    {
        $this->get('/')
            ->assertSee('<strong class="font-semibold text-ink">KE!N EINZELFALL e.V. wurde 2024 aus persönlicher Betroffenheit heraus gegründet.</strong>', false)
            ->assertSee('Für Sichtbarkeit. Für eine Stimme. Für Unterstützung.')
            ->assertDontSee('Vereinsarbeit');
    }

    public function test_bestehende_startseite_bekommt_den_neuen_vereinsabschnitt(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'text')->get()
            ->first(fn ($block) => ($block->data['cta']['url'] ?? null) === '/verein');
        $block->update(['data' => array_replace($block->data, [
            'titel' => 'Vereinsarbeit',
            'absaetze' => ['Der KE!N EINZELFALL e.V. wurde 2024 gegründet – aus einer persönlichen '
                .'Betroffenheit heraus und mit dem Ziel, von schädigenden Taten betroffene '
                .'Menschen nicht länger allein zu lassen.'],
            'hand' => 'Opferhilfe für soziale Gerechtigkeit!',
        ])]);
        $schluessel = array_keys($block->fresh()->data);

        $migration = require database_path('migrations/2026_09_27_150000_verein_abschnitt_startseite_erneuern.php');
        $migration->up();

        $data = $block->fresh()->data;
        $this->assertSame('Verein', $data['titel']);
        $this->assertStringStartsWith('*KE!N EINZELFALL e.V. wurde 2024', $data['absaetze'][0]);
        $this->assertSame('Für Sichtbarkeit. Für eine Stimme. Für Unterstützung.', $data['hand']);
        $this->assertSame($schluessel, array_keys($data));
    }

    /** KEV-53: Lucide-Zeichen auf den Einstiegskarten, auch in bestehenden Datenbanken. */
    public function test_einstiegskarten_bekommen_die_neuen_zeichen(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'quick_access')->first();
        $alt = ['users', 'message', 'shield', 'heart'];
        $data = $block->data;
        foreach ($data['karten'] as $i => $karte) {
            $data['karten'][$i]['icon'] = $alt[$i];
        }
        // Eine Karte hat der Verein schon selbst umgestellt, die bleibt.
        $data['karten'][3]['icon'] = 'info';
        $block->update(['data' => $data]);

        $migration = require database_path('migrations/2026_09_27_160000_einstiegskarten_neue_zeichen.php');
        $migration->up();

        $this->assertSame(
            ['user-group', 'network', 'notebook-pen', 'info'],
            array_column($block->fresh()->data['karten'], 'icon'),
        );
    }

    /** KEV-52: Spendenkarte mit Taddis Text, die markierten Sätze fett. */
    public function test_spendenkarte_zeigt_taddis_text_mit_fetten_saetzen(): void
    {
        $this->get('/')
            ->assertSee('<strong class="font-semibold text-ink">Deine Spende macht unsere Arbeit möglich.</strong>', false)
            ->assertSee('<strong class="font-semibold text-ink">Jeder Beitrag hilft uns, unabhängig zu arbeiten und gemeinsam etwas zu bewegen.</strong>', false)
            ->assertDontSee('*Deine Spende', false);
    }

    public function test_bestehende_startseite_bekommt_den_neuen_spendenkartentext(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'quick_access')->first();
        $data = $block->data;
        $i = array_search('/spenden', array_column($data['karten'], 'url'), true);
        $data['karten'][$i]['text'] = 'Mit Deiner Spende hilfst Du uns, kostenfreies Wissen und Aufklärung zu '
            .'leisten, Sichtbarkeit und Gehör zu schaffen, sowie eine Informationsplattform aufzustellen '
            .'und ein Netzwerk zu bilden.';
        $block->update(['data' => $data]);

        $migration = require database_path('migrations/2026_09_27_170000_spendenkarte_text_erneuern.php');
        $migration->up();

        $this->assertStringStartsWith('*Deine Spende macht unsere Arbeit möglich.*',
            $block->fresh()->data['karten'][$i]['text']);
    }

    /** Die Migration, die die Spendenmöglichkeit auf bestehenden Datenbanken nachträgt. */
    private function spendenMigration(): object
    {
        return require database_path('migrations/2026_09_19_120000_spenden_auf_der_startseite_nachtragen.php');
    }

    public function test_ohne_datensatz_faellt_die_startseite_nicht_auf_alte_texte_zurueck(): void
    {
        // Ein stiller Rückfall auf fest verdrahtete Texte hiesse: Die Startseite
        // hat wieder zwei Quellen, und niemand merkt, welche gerade gilt.
        Page::query()->delete();

        $this->get('/')->assertNotFound();
    }
}
