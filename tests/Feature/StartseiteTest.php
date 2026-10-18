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

    /**
     * KEV-120: Das Band „Unterstützung“ ist von der Startseite weg. Auch unter
     * „Mitglieder“ (KEV-106) rief sie damit zweimal kurz hintereinander zum
     * Spenden auf. Es steht jetzt auf Unterseiten (UnterstuetzungsbandTest).
     */
    public function test_startseite_ruft_nur_einmal_zum_spenden_auf(): void
    {
        $typen = $this->startseite()->blocks()->orderBy('position')->pluck('typ')->all();

        $this->assertSame(['hero', 'quick_access', 'text', 'text', 'hilfe_box', 'donation_options'], $typen);
        $this->get('/')->assertDontSee('Sei Du dabei, jede Unterstützung zählt');
    }

    /** KEV-121: Der Abschnitt „Anfragen & Austausch“ am Ende ist weg, der Weg zur Anfrage bleibt. */
    public function test_startseite_endet_ohne_kontaktabschluss(): void
    {
        $this->assertFalse($this->startseite()->blocks()->where('typ', 'contact_close')->exists());

        // Der Wortlaut des Abschlusses. Die Karte beginnt seit KEV-115 ähnlich
        // („… zum Sozialen Entschädigungsrecht“).
        $html = $this->get('/')->getContent();
        $this->assertStringNotContainsString('Du wünschst einen persönlichen Austausch in Bezug auf', $html);
        // Knopf im Aufmacher und Karte unter „Was wir gemeinsam bewegen“.
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'href="/anfragen"'));
    }

    public function test_kontaktabschluss_verschwindet_von_bestehenden_startseiten(): void
    {
        // Stand vor KEV-121, in beiden Sprachfassungen.
        $de = $this->startseite();
        $de->blocks()->create(['typ' => 'contact_close', 'position' => 99, 'data' => ['titel' => 'Anfragen & Austausch']]);
        $en = $de->replicate()->fill(['locale' => 'en', 'uebersetzungs_gruppe' => $de->uebersetzungs_gruppe]);
        $en->save();
        $en->blocks()->create(['typ' => 'hero', 'position' => 0, 'data' => ['titel' => 'Hero']]);
        $en->blocks()->create(['typ' => 'contact_close', 'position' => 1, 'data' => ['titel' => 'Enquiries & exchange']]);
        $vorher = $de->blocks()->count();

        $migration = require database_path('migrations/2026_10_18_100000_startseite_ohne_kontaktabschluss.php');
        $migration->up();
        $migration->up();

        $this->assertSame(['hero', 'quick_access', 'text', 'text', 'hilfe_box', 'donation_options'],
            $de->fresh()->blocks()->orderBy('position')->pluck('typ')->all());
        $this->assertSame($vorher - 1, $de->fresh()->blocks()->count());
        $this->assertSame(['hero'], $en->fresh()->blocks()->pluck('typ')->all());
    }

    public function test_band_rueckt_auf_bestehenden_startseiten_unter_verein(): void
    {
        // Stand vor KEV-106: das Band direkt hinter der Spendenmöglichkeit.
        // Seit KEV-120 gibt es das Band auf der Startseite nicht mehr, darum
        // legt der Test es selbst an.
        $seite = $this->startseite();
        $reihenfolge = ['hero', 'quick_access', 'text', 'text', 'hilfe_box', 'donation_options', 'cta_band'];
        $seite->blocks()->create(['typ' => 'cta_band', 'position' => 99, 'data' => ['zitat' => 'Band']]);

        $migration = require database_path('migrations/2026_10_07_190000_unterstuetzung_unter_verein.php');
        $migration->up();

        $neu = ['hero', 'quick_access', 'text', 'text', 'cta_band', 'hilfe_box', 'donation_options'];
        $this->assertSame($neu, $seite->fresh()->blocks()->orderBy('position')->pluck('typ')->all());

        // Ein zweiter Lauf ändert nichts mehr.
        $migration->up();
        $this->assertSame($neu, $seite->fresh()->blocks()->orderBy('position')->pluck('typ')->all());

        $migration->down();
        $this->assertSame($reihenfolge, $seite->fresh()->blocks()->orderBy('position')->pluck('typ')->all());
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

        // Ans Ende: Den Kontaktabschluss gibt es seit KEV-121 nicht mehr, das
        // Band seit KEV-120 nicht mehr auf der Startseite.
        $typen = $seite->fresh()->blocks()->orderBy('position')->pluck('typ')->all();
        $this->assertSame(['hero', 'quick_access', 'text', 'text', 'hilfe_box', 'donation_options'], $typen);
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

        // Worum es geht: Die englische Fassung bekommt den Baustein auch. Wo er
        // landet, prüfen die Tests darüber (ohne Kontaktabschluss: ans Ende).
        $this->assertSame(1, $en->fresh()->blocks()->where('typ', 'donation_options')->count());
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
        // Stand vor KEV-121: Da stand der Abschluss noch am Ende.
        $seite = $this->startseite();
        $seite->blocks()->whereIn('typ', ['donation_options', 'cta_band'])->delete();
        $seite->blocks()->create(['typ' => 'contact_close', 'position' => 99, 'data' => ['titel' => 'Anfragen & Austausch']]);

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

        $this->assertStringContainsString('Deine Spende hilft uns, Projekte umzusetzen', $abschnitt);
        $this->assertStringNotContainsString('Mit Deiner Spende hilfst Du uns', $abschnitt);
        $this->assertSame(1, substr_count($abschnitt, 'bg-green-brand'), 'zwei Striche über der Überschrift');

        // KEV-119: „Deinen Rückenwind“, und der Leitsatz schliesst den Absatz
        // ab, statt unter der Überschrift zu stehen.
        $this->assertMatchesRegularExpression(
            '/Jetzt spenden\s*<\/h2>\s*<p[^>]*>\s*Deine Spende hilft uns.*?weiter wachsen zu lassen\.<\/p>\s*'
            .'<p class="[^"]*font-hand[^"]*">Gute Ideen brauchen Deinen Rückenwind\.<\/p>/s', $abschnitt);
        $this->assertStringNotContainsString('Gute Ideen brauchen Rückenwind.', $abschnitt);

        // KEV-118: Der Verweis auf alle Spendenmöglichkeiten ohne Unterstrich,
        // erst beim Darüberfahren.
        $this->assertMatchesRegularExpression(
            '/<a href="\/spenden" class="text-green-deep no-underline hover:underline">Alle Spendenmöglichkeiten/', $abschnitt);
    }

    /**
     * KEV-119: Der neue Leitsatz kommt auch auf bestehende Startseiten. Die
     * englische baut der UebersetzungenSeeder aus dem Wörterbuch neu.
     */
    public function test_bestehende_startseite_bekommt_den_neuen_spendenleitsatz(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'donation_options')->first();
        $block->update(['data' => array_replace($block->data, ['hand' => 'Gute Ideen brauchen Rückenwind.'])]);

        $migration = require database_path('migrations/2026_10_18_120000_spenden_leitsatz_deinen_rueckenwind.php');
        $migration->up();

        $this->assertSame('Gute Ideen brauchen Deinen Rückenwind.', $block->fresh()->data['hand']);
        $this->assertSame('Good ideas need your tailwind.',
            json_decode(file_get_contents(database_path('seeders/data/uebersetzungen.json')), true)
                ['Gute Ideen brauchen Deinen Rückenwind.']['en']);

        // Ein im Panel gepflegter Leitsatz bleibt stehen.
        $block->update(['data' => array_replace($block->data, ['hand' => 'Eigener Satz.'])]);
        $migration->up();
        $this->assertSame('Eigener Satz.', $block->fresh()->data['hand']);
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

    /** KEV-117: zweiter Absatz am Ende des Abschnitts „Verein“. */
    public function test_vereinsabschnitt_hat_den_zweiten_absatz(): void
    {
        $this->get('/')->assertSeeInOrder([
            'was Betroffene bewegt.</p>',
            '<p>Wir schaffen Verbindungen zwischen Betroffenen, Angehörigen und Interessierten und fördern '
                .'den Austausch auf Augenhöhe mit Fachpersonen.</p>',
            'Für Sichtbarkeit. Für eine Stimme. Für Unterstützung.',
        ], false);
    }

    public function test_bestehende_startseite_bekommt_den_zweiten_vereinsabsatz(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'text')->get()
            ->first(fn ($block) => ($block->data['cta']['url'] ?? null) === '/verein');
        $erster = $block->data['absaetze'][0];
        $block->update(['data' => array_replace($block->data, ['absaetze' => [$erster]])]);
        $schluessel = array_keys($block->fresh()->data);

        $migration = require database_path('migrations/2026_10_18_130000_verein_zweiter_absatz.php');
        $migration->up();
        $migration->up();

        $data = $block->fresh()->data;
        $this->assertSame([$erster, StartseiteSeeder::VEREIN_ZWEITER_ABSATZ], $data['absaetze']);
        $this->assertSame($schluessel, array_keys($data));

        // Ein im Panel umgeschriebener Abschnitt bleibt, wie er ist.
        $block->update(['data' => array_replace($block->data, ['absaetze' => ['Eigener Text.']])]);
        $migration->up();
        $this->assertSame(['Eigener Text.'], $block->fresh()->data['absaetze']);
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

    /**
     * KEV-52: Spendenkarte mit Taddis Text, der erste Satz fett. Seit KEV-116
     * der letzte nicht mehr.
     */
    public function test_spendenkarte_zeigt_taddis_text_mit_fettem_ersten_satz(): void
    {
        $this->get('/')
            ->assertSee('<strong class="font-semibold text-ink">Deine Spende macht unsere Arbeit möglich.</strong>', false)
            ->assertSee('Danke, dass Du dabei bist. Jeder Beitrag hilft uns, unabhängig zu arbeiten und gemeinsam etwas zu bewegen.', false)
            ->assertDontSee('<strong class="font-semibold text-ink">Jeder Beitrag', false)
            ->assertDontSee('*Deine Spende', false)
            ->assertDontSee('*Jeder Beitrag', false);
    }

    /** KEV-116: Auch bestehende Startseiten verlieren den fetten letzten Satz, Englisch mit. */
    public function test_bestehende_spendenkarte_verliert_den_fetten_letzten_satz(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'quick_access')->first();
        $data = $block->data;
        $i = array_search('/spenden', array_column($data['karten'], 'url'), true);
        $data['karten'][$i]['text'] = str_replace('Jeder Beitrag hilft uns, unabhängig zu arbeiten und gemeinsam etwas zu bewegen.',
            '*Jeder Beitrag hilft uns, unabhängig zu arbeiten und gemeinsam etwas zu bewegen.*', StartseiteSeeder::SPENDEN_KARTE_TEXT);
        $block->update(['data' => $data]);

        $migration = require database_path('migrations/2026_10_18_140000_spendenkarte_letzter_satz_normal.php');
        $migration->up();

        $this->assertSame(StartseiteSeeder::SPENDEN_KARTE_TEXT, $block->fresh()->data['karten'][$i]['text']);
        $this->assertStringEndsWith('to make a difference together.',
            json_decode(file_get_contents(database_path('seeders/data/uebersetzungen.json')), true)
                [StartseiteSeeder::SPENDEN_KARTE_TEXT]['en']);
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

    /** „Was wir gemeinsam bewegen“ statt „Unsere Aufgabe“, auch in bestehenden Datenbanken. */
    public function test_einstiegskarten_heissen_was_wir_gemeinsam_bewegen(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'quick_access')->first();
        $block->update(['data' => array_replace($block->data, ['titel' => 'Unsere Aufgabe'])]);

        $migration = require database_path('migrations/2026_09_27_180000_einstiegskarten_ueberschrift.php');
        $migration->up();

        $this->get('/')->assertSee('Was wir gemeinsam bewegen')->assertDontSee('Unsere Aufgabe');
    }

    /** KEV-48: Selbsthilfe-Karte mit Taddis Text, der markierte Satz fett. */
    public function test_bestehende_startseite_bekommt_den_neuen_selbsthilfekartentext(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'quick_access')->first();
        $data = $block->data;
        $i = array_search('/selbsthilfegruppen', array_column($data['karten'], 'url'), true);
        $data['karten'][$i]['text'] = 'Der Austausch in unseren Selbsthilfegruppen soll Dir genau da '
            .'helfen, wo Du Hilfe benötigst, und er soll Dir aufzeigen, dass Du endlich nicht mehr '
            .'alleine bist, denn wir sind KE!N EINZELFALL! Die Selbsthilfegruppen sind kostenfrei und '
            .'nicht an eine Mitgliedschaft gebunden.';
        $block->update(['data' => $data]);

        $migration = require database_path('migrations/2026_09_27_190000_selbsthilfekarte_text_erneuern.php');
        $migration->up();

        $this->get('/')->assertSee('<strong class="font-semibold text-ink">Manchmal tut es gut, '
            .'Menschen zu treffen, die verstehen, ohne dass Du viel erklären musst.</strong>', false);
    }

    /** KEV-45: Hilfe-Nummern unter „Verein“ und „Mitglieder“, vor der Spendenmöglichkeit. */
    public function test_hilfe_box_rueckt_vor_die_spendenmoeglichkeit(): void
    {
        // Stand vor KEV-45: der Kasten direkt unter dem Aufmacher.
        $seite = $this->startseite();
        $bloecke = $seite->blocks()->orderBy('position')->get()->all();
        $kasten = array_values(array_filter($bloecke, fn ($b) => $b->typ === 'hilfe_box'))[0];
        $bloecke = array_values(array_filter($bloecke, fn ($b) => $b->typ !== 'hilfe_box'));
        array_splice($bloecke, 1, 0, [$kasten]);
        foreach ($bloecke as $position => $block) {
            $block->update(['position' => $position]);
        }

        $migration = require database_path('migrations/2026_09_27_200000_hilfe_box_unter_mitglieder.php');
        $migration->up();

        // Das Band gibt es seit KEV-120 auf der Startseite nicht mehr.
        $this->assertSame(
            ['hero', 'quick_access', 'text', 'text', 'hilfe_box', 'donation_options'],
            $seite->fresh()->blocks()->orderBy('position')->pluck('typ')->all(),
        );

        // Ein zweiter Lauf ändert nichts mehr.
        $migration->up();
        $this->assertSame('hilfe_box', $seite->fresh()->blocks()->orderBy('position')->pluck('typ')->all()[4]);
    }

    /** KEV-43: neuer Aufmacher-Text von Taddi, auch in bestehenden Datenbanken. */
    public function test_bestehende_startseite_bekommt_den_neuen_aufmachertext(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'hero')->first();
        $block->update(['data' => array_replace($block->data, ['text' => 'Wir schaffen eine Austausch – '
            .'und Informationsplattform für Opfer und Mit-Opfer, Angehörige, Interessierte und '
            .'Fachpersonen. Ein zentrales Netzwerk aus Expertise im Betroffenenkontext, Austausch auf '
            .'Augenhöhe. Wir leisten Aufklärung und geben Betroffenen eine Stimme. Für mehr '
            .'Sichtbarkeit und Gehör.'])]);

        $migration = require database_path('migrations/2026_09_27_210000_aufmacher_text_erneuern.php');
        $migration->up();

        $this->get('/')->assertSee('Du bist auf der Informations-, Austausch- und Selbstwirksamkeitsplattform');
    }

    /** KEV-86: ein Satz je Absatz, dritter Knopf „Arbeitsgruppen“. */
    public function test_aufmacher_mit_absaetzen_und_arbeitsgruppen(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('<p>Du bist auf der Informations-, Austausch- und Selbstwirksamkeitsplattform von KE!N EINZELFALL e.V.</p>', $html);
        $this->assertStringContainsString('<p>Für mehr Sichtbarkeit, Gehör und Unterstützung!</p>', $html);
        $this->assertMatchesRegularExpression('#href="/arbeitsgruppen"[^>]*>\s*Arbeitsgruppen\s*</a>#', $html);
    }

    /** KEV-86: Die Migration bricht den Text nur um, wo er noch in einem Stück steht. */
    public function test_migration_setzt_absaetze_und_knopf(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'hero')->first();
        $block->update(['data' => array_replace($block->data, [
            'text' => str_replace("\n", ' ', \Database\Seeders\StartseiteSeeder::AUFMACHER_TEXT),
            'ctas' => array_slice(\Database\Seeders\StartseiteSeeder::AUFMACHER_KNOEPFE, 0, 2),
        ])]);

        $migration = require database_path('migrations/2026_10_08_220000_aufmacher_absaetze_und_arbeitsgruppen.php');
        $migration->up();

        $data = $block->fresh()->data;
        $this->assertSame(\Database\Seeders\StartseiteSeeder::AUFMACHER_TEXT, $data['text']);
        $this->assertCount(3, $data['ctas']);
    }

    /** KEV-51: Karte „Anfragen & Austausch“ mit Taddis Text, der markierte Satz fett. */
    public function test_anfragenkarte_hat_den_neuen_text(): void
    {
        $alt = 'Du wünschst einen persönlichen Austausch in Bezug auf das Soziale Entschädigungsrecht '
            .'(OEG/SGB XIV), den Schwerbehindertenausweis und/oder den Pflegegrad, oder hast Fragen zu '
            .'anderen Hilfesystemen, oder möchtest uns etwas mitteilen?';

        $block = $this->startseite()->blocks()->where('typ', 'quick_access')->first();
        $data = $block->data;
        $i = array_search('/anfragen', array_column($data['karten'], 'url'), true);
        $data['karten'][$i]['text'] = $alt;
        $block->update(['data' => $data]);

        $migration = require database_path('migrations/2026_10_13_110000_anfragenkarte_text_erneuern.php');
        $migration->up();

        // Die Migration setzt den jeweils aktuellen Text, seit KEV-115 also
        // gleich den neuesten.
        $this->assertSame(StartseiteSeeder::ANFRAGEN_KARTE_TEXT, $block->fresh()->data['karten'][$i]['text']);

        // Der alte Wortlaut steht nirgends mehr: Die Karte hat den neuen, und
        // den Abschlussblock unten mit dem alten gibt es seit KEV-121 nicht mehr.
        $this->assertStringNotContainsString('Du wünschst einen persönlichen Austausch in Bezug auf',
            $this->get('/')->getContent());
    }

    /**
     * KEV-115: Karte „Anfragen & Austausch“ mit Taddis neuem Text. Der Satz
     * aus ihren Anführungszeichen fett und als eigener Absatz.
     */
    public function test_anfragenkarte_hat_fetten_ersten_absatz(): void
    {
        $this->get('/')->assertSeeInOrder([
            '<span><strong class="font-semibold text-ink">Deine Frage, Deine Idee, Dein Anliegen – wir hören zu.</strong></span>',
            '<span>Du wünschst einen persönlichen Austausch zum Sozialen Entschädigungsrecht (OEG/SGB XIV),',
            'Dann melde Dich gern bei uns.</span>',
        ], false);
    }

    public function test_bestehende_anfragenkarte_bekommt_den_text_mit_absatz(): void
    {
        $kev51 = '*Nur wenn wir miteinander reden, können wir gemeinsam etwas bewegen.* Also trau Dich und '
            .'nimm Kontakt mit uns auf – egal, ob Du eine Projektidee mitbringst, Unterstützung suchst oder '
            .'Dich einfach mal mit uns austauschen möchtest. Dein Anliegen muss noch nicht fertig gedacht '
            .'sein. Wir hören zu und schauen gemeinsam, was wir machen können.';

        $block = $this->startseite()->blocks()->where('typ', 'quick_access')->first();
        $data = $block->data;
        $i = array_search('/anfragen', array_column($data['karten'], 'url'), true);
        $data['karten'][$i]['text'] = $kev51;
        $block->update(['data' => $data]);

        $migration = require database_path('migrations/2026_10_18_150000_anfragenkarte_text_mit_absatz.php');
        $migration->up();

        $this->assertSame(StartseiteSeeder::ANFRAGEN_KARTE_TEXT, $block->fresh()->data['karten'][$i]['text']);
        $this->assertStringStartsWith("*Your question, your idea, your concern – we are listening.*\n",
            json_decode(file_get_contents(database_path('seeders/data/uebersetzungen.json')), true)
                [StartseiteSeeder::ANFRAGEN_KARTE_TEXT]['en']);
    }

    /** Eine Zeile im Kartentext ist ein Absatz, auch ohne fetten Satz. */
    public function test_kartentext_mit_zeilenumbruch_wird_zu_absaetzen(): void
    {
        $html = view('components.blocks.quick-access', ['karten' => [
            ['icon' => 'info', 'titel' => 'Karte', 'text' => "Erste Zeile.\r\nZweite Zeile.", 'url' => '/x'],
        ]])->render();

        $this->assertMatchesRegularExpression('#<span>Erste Zeile\.</span>\s*<span>Zweite Zeile\.</span>#', $html);
    }

    /** KEV-49: Karte „Arbeitsgruppen“ mit Taddis Text, der markierte Satz fett. */
    public function test_arbeitsgruppenkarte_hat_den_neuen_text(): void
    {
        $block = $this->startseite()->blocks()->where('typ', 'quick_access')->first();
        $data = $block->data;
        $i = array_search('/arbeitsgruppen', array_column($data['karten'], 'url'), true);
        $data['karten'][$i]['text'] = 'Um unsere Arbeit weiter zu professionalisieren und gezielt Wirkung '
            .'zu entfalten, gründen wir immer wieder Arbeitsgruppen – und laden Dich herzlich ein, Teil '
            .'davon zu werden. Ein Quereinstieg ist möglich, Du kannst jederzeit dazu kommen. Ob mit '
            .'Fachwissen, Kreativität oder einfach dem Wunsch, etwas zu bewegen!';
        $block->update(['data' => $data]);

        $migration = require database_path('migrations/2026_10_13_120000_arbeitsgruppenkarte_text_erneuern.php');
        $migration->up();

        // Die Migration setzt den jeweils aktuellen Text, seit KEV-114 also
        // gleich den neuesten.
        $this->assertSame(StartseiteSeeder::ARBEITSGRUPPEN_KARTE_TEXT, $block->fresh()->data['karten'][$i]['text']);
        $this->get('/')->assertDontSee('Um unsere Arbeit weiter zu professionalisieren');
    }

    /**
     * KEV-114: Karte „Arbeitsgruppen“ mit Taddis neuem Text. Der Satz aus
     * ihren Anführungszeichen fett und als eigener Absatz.
     */
    public function test_arbeitsgruppenkarte_hat_fetten_ersten_absatz(): void
    {
        $this->get('/')->assertSeeInOrder([
            '<span><strong class="font-semibold text-ink">Gute Ideen werden sichtbar, wenn wir sie gemeinsam weiterdenken.</strong></span>',
            '<span>In unseren Arbeitsgruppen kommen Menschen zusammen,',
            'oder einfach mit Lust, etwas zu bewegen.</span>',
        ], false)->assertDontSee('Du hast Ideen, Wissen, bist kreativ');
    }

    public function test_bestehende_arbeitsgruppenkarte_bekommt_den_text_mit_absatz(): void
    {
        $kev49 = '*Du hast Ideen, Wissen, bist kreativ oder hast einfach Lust, etwas zu verändern?* In unseren '
            .'Arbeitsgruppen bringen Menschen ihre Erfahrungen, Perspektiven und ihr Wissen zusammen, '
            .'entwickeln daraus Ideen und setzen gemeinsam Projekte in Bewegung. Du kannst Dich jederzeit in '
            .'laufende Arbeitsgruppen einbringen – auch ohne Vorkenntnisse.';

        $block = $this->startseite()->blocks()->where('typ', 'quick_access')->first();
        $data = $block->data;
        $i = array_search('/arbeitsgruppen', array_column($data['karten'], 'url'), true);
        $data['karten'][$i]['text'] = $kev49;
        $block->update(['data' => $data]);

        $migration = require database_path('migrations/2026_10_18_160000_arbeitsgruppenkarte_text_mit_absatz.php');
        $migration->up();

        $this->assertSame(StartseiteSeeder::ARBEITSGRUPPEN_KARTE_TEXT, $block->fresh()->data['karten'][$i]['text']);
        $this->assertStringStartsWith("*Good ideas become visible when we develop them further together.*\n",
            json_decode(file_get_contents(database_path('seeders/data/uebersetzungen.json')), true)
                [StartseiteSeeder::ARBEITSGRUPPEN_KARTE_TEXT]['en']);
    }

    /**
     * KEV-30: „Sofort verlassen“ steht als Band unten im Aufmacher, nicht mehr
     * am Seitenende. Auf dem Handy ohne Esc, dort gibt es die Taste nicht.
     */
    public function test_hinweis_zum_verlassen_steht_im_aufmacher(): void
    {
        $html = $this->get('/')->getContent();
        $aufmacher = substr($html, 0, strpos($html, '</section>', strpos($html, '<h1')));

        $this->assertStringContainsString('oder mit dreimal Esc', $aufmacher);
        $this->assertStringContainsString('„Exit“ in der Leiste unten', $aufmacher);
        $this->assertStringContainsString('TLS-verschlüsselt', $aufmacher);

        // Nicht noch einmal weiter unten. Bis KEV-121 hiess das: nicht im
        // Kontaktabschluss am Seitenende, den es seitdem nicht mehr gibt.
        // Jedes Vorkommen liegt im Aufmacher (dort je eine Fassung für Handy und Desktop).
        $this->assertSame(substr_count($aufmacher, 'jederzeit sofort verlassen'),
            substr_count($html, 'jederzeit sofort verlassen'));
        $this->assertSame(substr_count($aufmacher, 'TLS-verschlüsselt'),
            substr_count(substr($html, 0, strpos($html, '<footer')), 'TLS-verschlüsselt'));
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
