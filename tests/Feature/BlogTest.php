<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function beitrag(array $daten = []): Post
    {
        static $nr = 0;
        $nr++;

        return Post::create(array_merge([
            'slug' => 'beitrag-'.$nr,
            'titel' => 'Beitrag '.$nr,
            'inhalt' => '<p>Ein Text.</p>',
            'published_at' => now()->subDay(),
        ], $daten));
    }

    public function test_uebersicht_zeigt_nur_veroeffentlichte_beitraege(): void
    {
        $sichtbar = $this->beitrag(['titel' => 'Sichtbarer Beitrag']);
        $entwurf = $this->beitrag(['titel' => 'Noch ein Entwurf', 'published_at' => null]);
        $geplant = $this->beitrag(['titel' => 'Erscheint spaeter', 'published_at' => now()->addWeek()]);

        $antwort = $this->get('/aktuelles');

        $antwort->assertOk()->assertSee($sichtbar->titel);
        $antwort->assertDontSee($entwurf->titel);
        $antwort->assertDontSee($geplant->titel);
    }

    public function test_entwuerfe_sind_auch_direkt_nicht_erreichbar(): void
    {
        $entwurf = $this->beitrag(['published_at' => null]);

        $this->get('/aktuelles/'.$entwurf->slug)->assertNotFound();
    }

    /**
     * Suche und Filter laufen über GET-Parameter, damit jeder Stand eine
     * eigene teilbare Adresse hat und ohne JavaScript funktioniert — anders
     * als die per JavaScript erzeugte Navigation der Altseite.
     */
    public function test_suchformular_ist_ohne_javascript_bedienbar(): void
    {
        $html = $this->get('/aktuelles')->getContent();

        $this->assertStringContainsString('<form method="GET"', $html);
        $this->assertStringContainsString('name="suche"', $html);
        $this->assertStringContainsString('role="search"', $html);
    }

    public function test_suche_findet_beitraege_ueber_titel_und_fliesstext(): void
    {
        $this->beitrag(['titel' => 'Alles zur Erwerbsminderungsrente']);
        $this->beitrag(['titel' => 'Unauffaelliger Titel',
            'inhalt' => '<p>Im Text steht etwas zum Schwerbehindertenausweis.</p>']);
        $this->beitrag(['titel' => 'Ganz anderes Thema']);

        $this->get('/aktuelles?suche=Erwerbsminderungsrente')
            ->assertSee('Alles zur Erwerbsminderungsrente')
            ->assertDontSee('Ganz anderes Thema');

        // Auch der Fliesstext wird durchsucht, nicht nur die Überschrift.
        $this->get('/aktuelles?suche=Schwerbehindertenausweis')
            ->assertSee('Unauffaelliger Titel')
            ->assertDontSee('Ganz anderes Thema');
    }

    public function test_platzhalter_in_der_suche_werden_maskiert(): void
    {
        // Ohne Maskierung waere "%" eine Suche nach allem.
        $this->beitrag(['titel' => 'Regulaerer Beitrag']);

        $this->get('/aktuelles?suche=%')
            ->assertOk()
            ->assertSee('Keine Beiträge');
    }

    public function test_suche_findet_auch_kurze_begriffe(): void
    {
        // Der MySQL-Volltextindex greift erst ab vier Zeichen. Kürzere Begriffe
        // würden sonst wortlos nichts finden.
        $this->beitrag(['titel' => 'OEG und was dahintersteckt']);

        $this->get('/aktuelles?suche=OEG')->assertOk()->assertSee('OEG und was dahintersteckt');
    }

    public function test_suche_ohne_treffer_sagt_das_deutlich(): void
    {
        $this->beitrag();

        $this->get('/aktuelles?suche=gibtesnichtimbestand')
            ->assertOk()
            ->assertSee('Keine Beiträge')
            ->assertSee('Hier ist noch nichts zu finden.');
    }

    public function test_kategoriefilter_funktioniert(): void
    {
        $wissen = Category::create(['slug' => 'wissen', 'name' => 'Wissen']);
        $news = Category::create(['slug' => 'news', 'name' => 'News']);

        $this->beitrag(['titel' => 'Wissensbeitrag', 'category_id' => $wissen->id]);
        $this->beitrag(['titel' => 'Newsbeitrag', 'category_id' => $news->id]);

        $this->get('/aktuelles?kategorie=wissen')
            ->assertOk()
            ->assertSee('Wissensbeitrag')
            ->assertDontSee('Newsbeitrag');
    }

    public function test_suche_und_filter_bleiben_beim_blaettern_erhalten(): void
    {
        $kategorie = Category::create(['slug' => 'wissen', 'name' => 'Wissen']);

        for ($i = 0; $i < 12; $i++) {
            $this->beitrag(['titel' => "Wissensbeitrag {$i} zur Rente", 'category_id' => $kategorie->id]);
        }

        $html = $this->get('/aktuelles?kategorie=wissen&suche=Rente')->getContent();

        // Ohne withQueryString() gingen Filter und Suchbegriff auf Seite 2 verloren.
        $this->assertStringContainsString('kategorie=wissen', $html);
        $this->assertStringContainsString('suche=Rente', $html);
    }

    public function test_beitrag_bringt_strukturierte_daten_mit(): void
    {
        $beitrag = $this->beitrag(['titel' => 'Mit Auszeichnung']);

        $html = $this->get('/aktuelles/'.$beitrag->slug)->getContent();

        $this->assertStringContainsString('application/ld+json', $html);
        $this->assertStringContainsString('"@type":"Article"', $html);
    }

    public function test_beitragsseite_hat_genau_eine_h1(): void
    {
        $beitrag = $this->beitrag();

        $html = $this->get('/aktuelles/'.$beitrag->slug)->getContent();

        $this->assertSame(1, preg_match_all('/<h1[^>]*>/', $html));
    }
}
