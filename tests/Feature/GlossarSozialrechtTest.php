<?php

namespace Tests\Feature;

use App\Filament\Resources\GlossaryTerms\Pages\CreateGlossaryTerm;
use App\Filament\Resources\GlossaryTerms\Pages\ListGlossaryTerms;
use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Models\User;
use App\Support\Suche;
use Database\Seeders\GlossarSozialrechtSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * KEV-153: Glossar mit Taddis Abkürzungsverzeichnis, pflegbarer Einleitung,
 * Suche auf der Seite und Anbindung an die Suche der Website.
 */
class GlossarSozialrechtTest extends TestCase
{
    use RefreshDatabase;

    public function test_einleitung_von_taddi_mit_hervorhebungen(): void
    {
        $html = $this->get('/glossar')->assertOk()->getContent();

        $this->assertStringContainsString('Abkürzungen im Sozialen Entschädigungsrecht (SER)', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Was soll das eigentlich alles heißen\?<\/strong>/u', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Denn verstehen sollte nicht schon an drei Buchstaben scheitern\.<\/strong>/u', $html);
        $this->assertStringContainsString('href="mailto:kontakt@kein-einzelfall.de"', $html);
        // Alles offen, kein „Weiterlesen“ vor Taddis Gruß.
        $this->assertStringNotContainsString('Weiterlesen', $html);
        $this->assertStringContainsString('src="/img/titelbilder/glossar.webp?v=', $html);
        $this->assertStringContainsString('object-position: 0% 90%', $html);
    }

    public function test_einleitung_und_titel_sind_im_panel_pflegbar(): void
    {
        $seite = Page::where('slug', 'glossar')->where('locale', 'de')->firstOrFail();
        $seite->update(['titel' => 'Abkürzungen', 'meta_description' => 'Vom Verein geändert.']);
        $seite->blocks()->first()->update(['data' => ['absaetze' => ['Neuer Text vom Verein.']]]);

        $html = $this->get('/glossar')->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Abkürzungen\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Neuer Text vom Verein.', $html);
        $this->assertStringContainsString('content="Vom Verein geändert."', $html);
    }

    public function test_alle_abkuerzungen_aus_dem_dokument(): void
    {
        $html = $this->get('/glossar')->getContent();
        $daten = GlossarSozialrechtSeeder::eintraege();

        $this->assertCount(197, $daten);
        // Nur dieses Glossar: Seit KEV-155 gibt es auch „Behörden & Bescheide“.
        $glossar = Page::where('slug', 'glossar')->where('locale', 'de')->firstOrFail();
        $this->assertSame(195, GlossaryTerm::aufSeite($glossar)->where('locale', 'de')->whereNotNull('published_at')->count());
        $this->assertSame(195, substr_count($html, 'data-glossar-eintrag'));

        // Gleiche Abkürzung, zwei Bedeutungen: zwei Einträge mit eigenem Sprungziel.
        $this->assertStringContainsString('id="kkh-krankenhaus"', $html);
        $this->assertStringContainsString('id="kkh-kaufmaennische-krankenkasse"', $html);
        $this->assertStringContainsString('id="va-verwaltungsakt"', $html);

        // Tippfehler und falsche Auflösungen korrigiert.
        foreach (['BARMEER', 'AuslG', 'Bundesamt für soziale Dienste', 'Sondereinsatzkomando', 'Berufsausübungssgemeinschaft'] as $falsch) {
            $this->assertStringNotContainsString($falsch, $html);
        }
        $this->assertStringContainsString('Bundesamt für Soziale Sicherung', $html);
        $this->assertStringContainsString('>AusglV</abbr>', $html);
    }

    public function test_unklare_abkuerzungen_bleiben_entwurf(): void
    {
        foreach (['vi', 'vvv'] as $slug) {
            $eintrag = GlossaryTerm::where('locale', 'de')->where('slug', $slug)->firstOrFail();
            $this->assertNull($eintrag->published_at, $slug);
        }

        $this->assertStringNotContainsString('id="vi"', $this->get('/glossar')->getContent());
    }

    public function test_unsere_entwuerfe_werden_zu_taddis_abkuerzungen(): void
    {
        foreach (['gdb' => 'GdB', 'pflegegrad' => 'PG', 'persoenliches-budget' => 'PB'] as $slug => $kuerzel) {
            $eintrag = GlossaryTerm::where('locale', 'de')->where('slug', $slug)->firstOrFail();

            $this->assertSame($kuerzel, $eintrag->kuerzel);
            $this->assertNotNull($eintrag->published_at);
            // Unsere ungeprüfte Erklärung steht nicht öffentlich da.
            $this->assertNull($eintrag->erklaerung);
        }

        // Die vom Verein bestätigten bleiben, wie sie waren.
        $this->assertStringStartsWith('Das Opferentschädigungsgesetz ist zum 31.12.2023',
            GlossaryTerm::where('locale', 'de')->where('slug', 'oeg')->value('erklaerung'));
        $this->assertStringNotContainsString('ENTWURF', $this->get('/glossar')->getContent());
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $vorher = GlossaryTerm::count();
        GlossaryTerm::where('slug', 'aok')->update(['erklaerung' => 'Vom Verein ergänzt.']);

        GlossarSozialrechtSeeder::anlegen();
        (require database_path('migrations/2026_10_22_235900_glossar_abkuerzungen_sozialrecht.php'))->up();

        $this->assertSame($vorher, GlossaryTerm::count());
        $this->assertSame(1, Page::where('slug', 'glossar')->where('locale', 'de')->count());
        $this->assertSame('Vom Verein ergänzt.', GlossaryTerm::where('slug', 'aok')->value('erklaerung'));
    }

    public function test_eintrag_ohne_erklaerung_ist_gueltiges_html(): void
    {
        $html = $this->get('/glossar')->getContent();

        // Jeder Eintrag: <dt> und mindestens ein <dd>, kein leeres <dd>.
        preg_match_all('/<div id="[^"]+"\s+data-glossar-eintrag.*?<\/div>/s', $html, $eintraege);
        $this->assertCount(195, $eintraege[0]);
        foreach ($eintraege[0] as $eintrag) {
            $this->assertStringContainsString('<dt', $eintrag);
            $this->assertStringContainsString('<dd', $eintrag);
            $this->assertDoesNotMatchRegularExpression('/<dd[^>]*>\s*<\/dd>/', $eintrag);
        }
    }

    public function test_suche_im_glossar_ohne_javascript(): void
    {
        $html = $this->get('/glossar?suche=SGB')->assertOk()->getContent();

        $sichtbar = preg_match_all('/data-glossar-eintrag\s+data-kuerzel="[^"]*"\s+data-suchtext="[^"]*"\s+class=/', $html);
        // SGB, SGB I bis SGB XII und SGB XIV. Nicht „BSG Bundessozialgericht“.
        $this->assertSame(13, $sichtbar);
        $this->assertStringContainsString('Treffer: 13 von 195', $html);
        $this->assertMatchesRegularExpression('/<a href="\/glossar" data-glossar-zuruecksetzen\s+class=/', $html);

        // „idr“ findet „i. d. R.“, „gemaess“ findet „gemäß“.
        $this->assertStringContainsString('Treffer: 1 von 195', $this->get('/glossar?suche=idr')->getContent());
        $this->assertStringContainsString('Treffer: 1 von 195', $this->get('/glossar?suche=gemaess')->getContent());

        // Nichts gefunden: Weg in die Websuche.
        $leer = $this->get('/glossar?suche=xqzv')->getContent();
        $this->assertStringContainsString('Dazu steht noch nichts im Glossar.', $leer);
        $this->assertStringContainsString('href="/suche?q=xqzv"', $leer);
    }

    public function test_websuche_findet_auch_kurze_abkuerzungen(): void
    {
        $suche = app(Suche::class);

        $this->assertSame('/glossar#au', $suche->suchen('AU')['treffer'][0]['url']);
        $this->assertSame('/glossar#kk', $suche->suchen('KK')['treffer'][0]['url']);
        $this->assertSame('/glossar#i-d-r', $suche->suchen('i. d. R.')['treffer'][0]['url']);
        $this->assertSame('/glossar#sgb-xiv', $suche->suchen('SGB XIV')['treffer'][0]['url']);

        // Nur die Auflösung im Eintrag: Die Seite zum Thema erklärt mehr und
        // steht vorn, der Eintrag gleich dahinter.
        $gdb = array_column($suche->suchen('gdb')['treffer'], 'url');
        $this->assertSame(['/grad-der-behinderung', '/glossar#gdb'], array_slice($gdb, 0, 2));

        // Kurze Suchwörter treffen im Glossar nur am Wortanfang.
        $this->assertNotContains('/glossar#abs', array_column($suche->suchen('BSA')['treffer'], 'url'));

        // „AG 01“ meint die Arbeitsgruppe, nicht „aG“ (außergewöhnliche Gehbehinderung).
        $treffer = array_column($suche->suchen('AG 01')['treffer'], 'url');
        $this->assertNotContains('/glossar#ag', $treffer);
    }

    public function test_glossar_draengt_die_seiten_nicht_aus_der_trefferliste(): void
    {
        $ergebnis = app(Suche::class)->suchen('Krankenkasse');
        $glossar = array_filter($ergebnis['treffer'], fn ($t) => $t['art'] === 'glossar');

        $this->assertCount(5, $glossar);
        // Je Glossar-Seite (KEV-155); „Krankenkasse“ steht nur im ersten.
        $this->assertSame([['titel' => 'Glossar', 'url' => '/glossar', 'anzahl' => 10]], $ergebnis['im_glossar']);

        $html = $this->get('/suche?q=Krankenkasse')->getContent();
        $this->assertStringContainsString('href="/glossar?suche=Krankenkasse"', $html);
        $this->assertStringContainsString('Alle 10 passenden Einträge in „Glossar“', $html);
    }

    public function test_glossar_steht_einmal_in_der_sitemap(): void
    {
        $xml = $this->get('/sitemap.xml')->getContent();

        $this->assertSame(1, substr_count($xml, '/glossar</loc>'));
        $this->assertSame(1, substr_count($xml, '/veranstaltungen</loc>'));
    }

    public function test_im_panel_reicht_abkuerzung_und_aufloesung(): void
    {
        $redaktion = User::factory()->redaktion()->create();
        $this->actingAs($redaktion)->get('/admin/glossary-terms')->assertOk();

        Livewire::actingAs($redaktion)
            ->test(CreateGlossaryTerm::class)
            ->fillForm(['kuerzel' => 'OVG', 'begriff' => 'Oberverwaltungsgericht', 'slug' => 'ovg', 'erklaerung' => null])
            ->call('create')
            ->assertHasNoFormErrors();
        $this->assertNull(GlossaryTerm::where('slug', 'ovg')->value('erklaerung'));

        // Ohne Abkürzung bliebe nur ein Wort: Dann ist die Erklärung Pflicht.
        Livewire::actingAs($redaktion)
            ->test(CreateGlossaryTerm::class)
            // Ein Begriff, den es noch nicht gibt („Widerspruchsfrist“ steht
            // seit KEV-155 in „Behörden & Bescheide“).
            ->fillForm(['kuerzel' => null, 'begriff' => 'Klagefrist', 'slug' => 'klagefrist', 'erklaerung' => null])
            ->call('create')
            ->assertHasFormErrors(['erklaerung' => 'required']);
    }

    public function test_panel_findet_die_entwuerfe(): void
    {
        Livewire::actingAs(User::factory()->redaktion()->create())
            ->test(ListGlossaryTerms::class)
            ->filterTable('published_at', false)
            ->assertCanSeeTableRecords(GlossaryTerm::whereIn('slug', ['vi', 'vvv'])->get())
            ->assertCanNotSeeTableRecords(GlossaryTerm::where('slug', 'aok')->get());
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/glossar')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Glossary\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Abbreviations in social compensation law (SER)', $html);
        $this->assertStringContainsString('General local health insurance fund (Allgemeine Ortskrankenkasse)', $html);
        $this->assertStringContainsString('Search for an abbreviation or term', $html);
        $glossar = Page::where('slug', 'glossar')->where('locale', 'en')->firstOrFail();
        $this->assertSame(195, GlossaryTerm::aufSeite($glossar)->where('locale', 'en')->whereNotNull('published_at')->count());
    }
}
