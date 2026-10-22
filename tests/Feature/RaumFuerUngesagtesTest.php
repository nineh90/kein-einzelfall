<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\RaumFuerUngesagtesSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-159: neuer Bereich „Raum für Ungesagtes“ mit der Unterseite
 * „Unüberhörbar“.
 */
class RaumFuerUngesagtesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_unueberhoerbar_mit_taddis_text(): void
    {
        $html = $this->get('/unueberhoerbar')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Unüberhörbar - Kein Einzelfall e.V.</title>', $html);
        $this->assertStringContainsString('Manches findet seinen Ausdruck erst im Klang.', $html);
        $this->assertStringContainsString('so unterschiedlich wie das, was dahintersteht.', $html);
        $this->assertStringContainsString('du entscheidest, wie sichtbar du sein möchtest.', $html);

        // Beide Stellen in Gänsefüßchen fett, die Adresse als Mailto-Link.
        $this->assertMatchesRegularExpression('/<strong[^>]*><a href="mailto:sichtbarkeit@kein-einzelfall\.de"[^>]*>sichtbarkeit@kein-einzelfall\.de<\/a><\/strong>/', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Was lange ungesagt blieb, darf unüberhörbar werden\.<\/strong>/u', $html);
        $this->assertStringNotContainsString('*sichtbarkeit', $this->text($html));
        $this->assertStringNotContainsString('werden.*', $this->text($html));
    }

    public function test_titelbilder_von_taddi(): void
    {
        foreach (['unueberhoerbar' => 60, 'raum-fuer-ungesagtes' => 25] as $slug => $fokus) {
            $html = $this->get('/'.$slug)->getContent();

            $this->assertStringContainsString('src="/img/titelbilder/'.$slug.'.webp?v=', $html, $slug);
            $this->assertStringContainsString('object-position: 0% '.$fokus.'%', $html, $slug);
            $this->assertNotContains($slug, Titelbilder::PLATZHALTER_SEITEN);
            // Rechts um Wand verlängert wie die Jahresberichte.
            $this->assertSame([2000, 776], array_slice(getimagesize(public_path('img/titelbilder/'.$slug.'.webp')), 0, 2));
        }
    }

    public function test_eigener_bereich_im_menue_nach_wissen(): void
    {
        $bereiche = collect(config('navigation.main'));
        $urls = $bereiche->pluck('url')->all();

        $this->assertSame(array_search('/wissen', $urls) + 1, array_search('/raum-fuer-ungesagtes', $urls));

        $raum = $bereiche->firstWhere('url', '/raum-fuer-ungesagtes');
        $this->assertSame('Raum für Ungesagtes', $raum['label']);
        $this->assertSame(['/unueberhoerbar'], array_column($raum['children'], 'url'));

        $html = $this->get('/unueberhoerbar')->getContent();
        $this->assertStringContainsString('href="/raum-fuer-ungesagtes"', $html);
        // Brotkrumen: Start › Raum für Ungesagtes › Unüberhörbar
        $this->assertMatchesRegularExpression('/aria-label="Sie sind hier".*Raum für Ungesagtes.*Unüberhörbar/s', $html);
    }

    /** Ohne Text von Taddi: veröffentlicht, damit das Menü nicht ins Leere führt, aber noindex (wie Rückblick). */
    public function test_bereichsseite_wartet_auf_text(): void
    {
        $seite = Page::where('slug', 'raum-fuer-ungesagtes')->where('locale', 'de')->firstOrFail();
        $this->assertTrue($seite->noindex);
        $this->assertSame(0, $seite->blocks()->count());

        $html = $this->get('/raum-fuer-ungesagtes')->assertOk()->getContent();
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        $this->assertStringContainsString('href="/unueberhoerbar"', $html);
        $this->assertStringNotContainsString('/raum-fuer-ungesagtes</loc>', $this->get('/sitemap.xml')->getContent());
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'unueberhoerbar')->where('locale', 'de')->firstOrFail();
        $seite->blocks()->first()->update(['data' => ['absaetze' => ['Vom Verein geändert.']]]);

        RaumFuerUngesagtesSeeder::anlegen();
        (require database_path('migrations/2026_10_22_235950_raum_fuer_ungesagtes_anlegen.php'))->up();

        $this->assertSame(1, Page::where('slug', 'unueberhoerbar')->where('locale', 'de')->count());
        $this->assertSame(1, Page::where('slug', 'raum-fuer-ungesagtes')->where('locale', 'de')->count());
        $this->assertSame(['Vom Verein geändert.'], $seite->blocks()->first()->fresh()->data['absaetze']);
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/unueberhoerbar')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Loud and clear\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Some things only find their expression in sound.', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>What remained unsaid for a long time may now be heard loud and clear\.<\/strong>/u', $html);
        $this->assertStringContainsString('href="mailto:sichtbarkeit@kein-einzelfall.de"', $html);
        $this->assertStringContainsString('Space for the unsaid', $html);
    }

    /** Sichtbarer Text ohne Kopf und Skripte, um übrig gebliebene Sternchen zu finden. */
    private function text(string $html): string
    {
        $html = preg_replace('#<(script|style|head)\b.*?</\1>#s', '', $html);

        return html_entity_decode(strip_tags($html));
    }
}
