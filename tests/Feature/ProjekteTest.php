<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\ProjekteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-160: Bereich „Projekte“ mit Übersichtsseite, die Projekte folgen
 * später als Unterseiten.
 */
class ProjekteTest extends TestCase
{
    use RefreshDatabase;

    public function test_eigener_bereich_im_menue_nach_raum_fuer_ungesagtes(): void
    {
        $urls = array_column(config('navigation.main'), 'url');
        $hier = array_search('/projekte', $urls);

        // Taddis Drive: „4. Raum für Ungesagtes“, „5. Projekte“, „6. Spenden“
        $this->assertSame('/raum-fuer-ungesagtes', $urls[$hier - 1]);
        $this->assertSame('/spenden', $urls[$hier + 1]);

        $html = $this->get('/projekte')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Projekte\s*<\/h1>/u', $html);
        $this->assertMatchesRegularExpression('/<nav[^>]*>.*href="\/projekte"/s', $html);
    }

    public function test_uebersicht_mit_taddis_text(): void
    {
        $html = $this->get('/projekte')->getContent();

        $this->assertStringContainsString('<p>Aus Erfahrungen und Bedarf entstehen Ideen, gemeinsam entwickeln wir daraus Projekte', $html);
        $this->assertStringContainsString('von Ausstellungen und Beteiligungsformaten bis hin zu Vorträgen', $html);
        $this->assertStringContainsString('Für uns sind Projekte ein wichtiger Raum für Selbstwirksamkeit.', $html);
        $this->assertStringContainsString('dabei zu sein und mitzugestalten.</p>', $html);
        // Fünf Absätze: nichts eingeklappt
        $this->assertStringNotContainsString('Weiterlesen', $html);

        // Ihr Aufruf unter eigener Überschrift, Zitate korrigiert
        $this->assertMatchesRegularExpression('/<h2[^>]*>\s*Du hast eine eigene Projektidee\?\s*<\/h2>/u', $html);
        $this->assertStringContainsString('denkst du grad: „Auf dieser Seite fehlt noch ein ganz bestimmtes Projekt.“ Oder: „Boah,', $html);
        $this->assertStringContainsString('mit anderen teilen oder auf die Beine stellen.“', $html);
        $this->assertStringContainsString('Engagement und eine Idee sind genug.', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Für Sichtbarkeit, Gehör und Unterstützung\.<\/strong>/u', $html);
        $this->assertMatchesRegularExpression('/<p class="[^"]*font-hand[^"]*">Das Team von KE!N EINZELFALL<\/p>/u', $html);

        // Das Postfach der Arbeitsgruppen, wie überall auf der Website
        $this->assertStringContainsString('href="mailto:arbeitsgruppe@kein-einzelfall.de"', $html);
        $this->assertStringNotContainsString('arbeitsgruppen@', $html);

        // Die leeren Überschriften aus dem Strukturpapier sind weg.
        $this->assertStringNotContainsString('Laufende Projekte', $html);
        $this->assertStringNotContainsString('Abgeschlossene Projekte', $html);
    }

    public function test_titelbild_von_taddi(): void
    {
        $html = $this->get('/projekte')->getContent();

        $this->assertStringContainsString('src="/img/titelbilder/projekte.webp?v=', $html);
        $this->assertStringContainsString('object-position: 0% 40%', $html);
        $this->assertSame([2000, 776], array_slice(getimagesize(public_path('img/titelbilder/projekte.webp')), 0, 2));
        $this->assertSame([1000, 388], array_slice(getimagesize(public_path('img/titelbilder/projekte-1000.webp')), 0, 2));
        // Auf dem Handy der Text unter die Hand
        $this->assertTrue(Titelbilder::textUnten('/img/titelbilder/projekte.webp'));
        $this->assertStringContainsString('min-h-[60svh] md:min-h-[50svh]', $html);
    }

    public function test_veroeffentlicht_und_auffindbar(): void
    {
        $seite = Page::where('slug', 'projekte')->where('locale', 'de')->firstOrFail();

        $this->assertNotNull($seite->published_at);
        $this->assertFalse($seite->noindex);
        $this->assertSame(ProjekteSeeder::META_DESCRIPTION, $seite->meta_description);
        $this->assertStringContainsString('/projekte</loc>', $this->get('/sitemap.xml')->getContent());
    }

    /** „Übersichtsseite mit späteren Unterseiten“: Was im Menü darunter steht, wird zur Kachel. */
    public function test_unterseiten_erscheinen_als_kacheln(): void
    {
        $kachelUeberschrift = '/<h2[^>]*>\s*Unsere Projekte\s*<\/h2>/u';
        $html = $this->get('/projekte')->getContent();

        // Seit KEV-162 das erste Projekt, ANUAS. Beschriftung, Unterzeile
        // und Bild kommen von dessen Seite.
        $this->assertMatchesRegularExpression($kachelUeberschrift, $html);
        $this->assertMatchesRegularExpression('/Unsere Projekte\s*<\/h2>.*?<a href="\/anuas".*?ANUAS.*?Opferschutz aus Betroffenenperspektive weiterdenken/s', $html);
        // Die Kacheln stehen zwischen Text und Aufruf.
        preg_match($kachelUeberschrift, $html, $treffer, PREG_OFFSET_CAPTURE);
        $this->assertGreaterThan(strpos($html, 'mitzugestalten.'), $treffer[0][1]);
        $this->assertLessThan(strpos($html, 'Du hast eine eigene Projektidee?'), $treffer[0][1]);

        // Ohne Projekte im Menü keine leere Überschrift.
        $menue = collect(config('navigation.main'))->map(fn (array $punkt) => $punkt['url'] === '/projekte'
            ? array_diff_key($punkt, ['children' => true])
            : $punkt);
        config(['navigation.main' => $menue->all()]);

        $this->assertDoesNotMatchRegularExpression($kachelUeberschrift, $this->get('/projekte')->getContent());
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'projekte')->where('locale', 'de')->firstOrFail();
        $text = $seite->blocks()->where('typ', 'text')->orderBy('position')->firstOrFail();
        $text->update(['data' => ['absaetze' => ['Vom Verein geändert.']]]);

        ProjekteSeeder::anlegen();
        (require database_path('migrations/2026_10_23_260000_projekte_bereich.php'))->up();

        $this->assertSame(1, Page::where('slug', 'projekte')->where('locale', 'de')->count());
        $this->assertSame(3, $seite->blocks()->count());
        $this->assertSame(['Vom Verein geändert.'], $text->fresh()->data['absaetze']);
    }

    /** Stand vor KEV-160: Entwurf mit zwei leeren Überschriften. */
    public function test_entwurf_wird_zur_uebersicht(): void
    {
        $seite = Page::where('slug', 'projekte')->where('locale', 'de')->firstOrFail();
        $seite->blocks()->delete();
        $seite->update(['published_at' => null, 'meta_description' => null]);
        foreach (['Laufende Projekte', 'Abgeschlossene Projekte'] as $position => $titel) {
            $seite->blocks()->create(['typ' => 'text', 'position' => $position, 'data' => ['titel' => $titel]]);
        }

        (require database_path('migrations/2026_10_23_260000_projekte_bereich.php'))->up();

        $seite->refresh();
        $this->assertNotNull($seite->published_at);
        $this->assertSame(['text', 'unterseiten', 'text'], $seite->blocks()->orderBy('position')->pluck('typ')->all());
        $this->assertSame(ProjekteSeeder::ABSAETZE, $seite->blocks()->orderBy('position')->first()->data['absaetze']);

        // Hat der Verein unter eine der Überschriften schon geschrieben, bleibt alles.
        $seite->blocks()->delete();
        $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['titel' => 'Laufende Projekte', 'absaetze' => ['Eigenes.']]]);
        ProjekteSeeder::anlegen();
        $this->assertSame(['Laufende Projekte'], $seite->blocks()->get()->pluck('data.titel')->all());
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/projekte')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Projects\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Ideas grow out of experience and need', $html);
        $this->assertStringContainsString('Do you have your own project idea?', $html);
        $this->assertStringContainsString('href="mailto:arbeitsgruppe@kein-einzelfall.de"', $html);
        $this->assertStringContainsString('What we are working on.', $html);
        // Der Menüpunkt heißt wie die englische Seite.
        $this->assertMatchesRegularExpression('/href="\/en\/projekte"[^>]*>\s*Projects\s*</', $html);
    }
}
