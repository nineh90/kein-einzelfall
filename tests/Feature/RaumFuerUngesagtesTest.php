<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use App\Support\Titelbilder;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\RaumFuerUngesagtesSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * KEV-159: neuer Bereich „Raum für Ungesagtes“ mit der Unterseite
 * „Unüberhörbar“. KEV-158: dazu „Blickwinkel“. KEV-156: Text der Übersicht,
 * eigene Triggerwarnung, vier Kategorien als Kacheln mit Aufrufzähler, „Wort
 * für Wort“.
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

    public function test_blickwinkel_mit_taddis_text(): void
    {
        $html = $this->get('/blickwinkel')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Blickwinkel - Kein Einzelfall e.V.</title>', $html);
        $this->assertStringContainsString('Manches lässt sich nicht in Worte fassen – aber zeigen.', $html);
        $this->assertStringContainsString('Sie zeigen Erlebtes, Gefühle, Gedanken und Perspektiven auf eine ganz eigene Weise.', $html);
        $this->assertStringContainsString('Dann schick uns deine Bilder gern an', $html);

        $this->assertMatchesRegularExpression('/<strong[^>]*><a href="mailto:sichtbarkeit@kein-einzelfall\.de"[^>]*>sichtbarkeit@kein-einzelfall\.de<\/a><\/strong>/', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Manchmal zeigt ein Bild mehr von einer Wahrheit, als Worte es je könnten\.<\/strong>/u', $html);
        $this->assertStringNotContainsString('*sichtbarkeit', $this->text($html));
    }

    public function test_titelbilder_von_taddi(): void
    {
        foreach (['unueberhoerbar' => 60, 'raum-fuer-ungesagtes' => 25, 'blickwinkel' => 35, 'wort-fuer-wort' => 80] as $slug => $fokus) {
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
        // Reihenfolge wie Taddis Kategorien (KEV-156) und Bilder im Drive.
        $this->assertSame(
            ['/wall-of-shame', '/wort-fuer-wort', '/blickwinkel', '/unueberhoerbar'],
            array_column($raum['children'], 'url'),
        );

        $html = $this->get('/unueberhoerbar')->getContent();
        $this->assertStringContainsString('href="/raum-fuer-ungesagtes"', $html);
        // Brotkrumen: Start › Raum für Ungesagtes › Unüberhörbar
        $this->assertMatchesRegularExpression('/aria-label="Sie sind hier".*Raum für Ungesagtes.*Unüberhörbar/s', $html);
    }

    /** KEV-156: Taddis Text, „Deine Stimme“ auf dem Bild, jetzt für Suchmaschinen offen. */
    public function test_bereichsseite_mit_taddis_text(): void
    {
        $seite = Page::where('slug', 'raum-fuer-ungesagtes')->where('locale', 'de')->firstOrFail();
        $this->assertFalse($seite->noindex);
        $this->assertSame('Deine Stimme', $seite->untertitel);
        $this->assertSame(['text', 'unterseiten', 'text'], $seite->blocks->pluck('typ')->all());

        $html = $this->get('/raum-fuer-ungesagtes')->assertOk()->getContent();

        $this->assertStringNotContainsString('<meta name="robots" content="noindex', $html);
        $this->assertStringContainsString('/raum-fuer-ungesagtes</loc>', $this->get('/sitemap.xml')->getContent());

        $this->assertStringContainsString('Deine Stimme', $html);
        $this->assertStringContainsString('Für alles, was zu lange keinen Platz hatte.', $html);
        $this->assertStringContainsString('Manches, weil niemand hinsieht.', $html);
        $this->assertStringContainsString('Texte, Gedichte, Briefe, Bilder, Lieder etc.', $html);
        $this->assertStringContainsString('viel zu oft einfach stehen gelassen wird.', $html);
        $this->assertStringContainsString('Möchtest du uns auch etwas sichtbar machen?', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*><a href="mailto:sichtbarkeit@kein-einzelfall\.de"[^>]*>sichtbarkeit@kein-einzelfall\.de<\/a><\/strong>/', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Was zu lange keinen Platz hatte, darf hier einen bekommen\.<\/strong>/u', $html);

        // Nichts davon hinter „Weiterlesen“: Adresse und Schlusssatz stehen
        // deshalb in einem eigenen Baustein nach den Kacheln.
        $this->assertStringNotContainsString('weitere Absätze', $html);
        $this->assertStringNotContainsString('*', $this->text($html));
    }

    /** KEV-156: Taddis vier Kategorien mit Bild und Unterzeile, die Liste am Seitenende entfällt. */
    public function test_vier_kategorien_als_kacheln(): void
    {
        $html = $this->get('/raum-fuer-ungesagtes')->getContent();

        $kacheln = $this->kacheln($html);
        preg_match_all('/<a href="([^"]+)"/', $kacheln, $treffer);
        $this->assertSame(['/wall-of-shame', '/wort-fuer-wort', '/blickwinkel', '/unueberhoerbar'], $treffer[1]);

        $this->assertStringContainsString('Was du hier findest', $kacheln);
        foreach (['wall-of-shame', 'wort-fuer-wort', 'blickwinkel', 'unueberhoerbar'] as $slug) {
            $this->assertStringContainsString('/img/titelbilder/'.$slug.'-1000.webp', $kacheln, $slug);
        }
        foreach (['Sätze, die Betroffene zu hören bekommen haben', 'Gedichte, Briefe und Texte von Betroffenen',
            'Bilder von Betroffenen', 'Musikstücke von Betroffenen'] as $text) {
            $this->assertStringContainsString($text, $kacheln);
        }

        $this->assertStringNotContainsString('Mehr zu „Raum für Ungesagtes“', $html);
    }

    /** Taddis Kategorie 2: Bild ja, Text fehlt noch. */
    public function test_wort_fuer_wort_wartet_auf_text(): void
    {
        $seite = Page::where('slug', 'wort-fuer-wort')->where('locale', 'de')->firstOrFail();
        $this->assertTrue($seite->noindex);
        $this->assertSame(0, $seite->blocks()->count());

        $html = $this->get('/wort-fuer-wort')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Wort für Wort\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Gedichte, Briefe und Texte von Betroffenen', $html);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
    }

    /** KEV-156: Taddis Triggerwarnung vor der Übersicht und jeder Seite darunter, vor dem Inhalt. */
    public function test_triggerwarnung_vor_dem_ganzen_bereich(): void
    {
        foreach (['raum-fuer-ungesagtes', 'wall-of-shame', 'wort-fuer-wort', 'blickwinkel', 'unueberhoerbar'] as $slug) {
            $html = $this->get('/'.$slug)->getContent();

            $this->assertStringContainsString('<dialog id="bereichswarnung"', $html, $slug);
            $this->assertStringContainsString('data-bereich="raum-fuer-ungesagtes"', $html, $slug);
            $this->assertStringContainsString('Auf dieser Seite kommen Inhalte vor, die sich mit sexualisierter Gewalt, Missbrauch', $html, $slug);
            $this->assertStringContainsString('können sehr direkt, emotional oder bildhaft sein.', $html, $slug);
            $this->assertStringContainsString('ob und wann du diese Inhalte lesen, ansehen oder anhören möchtest.', $html, $slug);

            // Ohne JavaScript ein Block vor dem Inhalt, nicht dahinter.
            $this->assertLessThan(strpos($html, '<h1'), strpos($html, '<dialog id="bereichswarnung"'), $slug);
            // Bestätigt wird für den Besuch; das Kopf-Skript kennt den Schlüssel.
            $this->assertStringContainsString("sessionStorage.getItem('ke.bereichswarnung')", $html, $slug);
        }

        // Drei Wege hinaus, zwei davon gehen ohne JavaScript.
        $dialog = $this->dialog($this->get('/wall-of-shame')->getContent());
        $this->assertStringContainsString('data-bereichswarnung-weiter', $dialog);
        $this->assertStringContainsString('href="/"', $dialog);
        $this->assertStringContainsString('href="https://www.google.de"', $dialog);
        $this->assertStringContainsString('data-notausgang', $dialog);

        foreach (['/wissen', '/verein', '/atteste'] as $adresse) {
            $this->assertStringNotContainsString('id="bereichswarnung"', $this->get($adresse)->getContent(), $adresse);
        }
    }

    /** Der Verein pflegt die Warnung an der Seite; ohne Absätze keine Warnung. */
    public function test_warnung_ist_im_panel_pflegbar(): void
    {
        Page::where('slug', 'raum-fuer-ungesagtes')->update(['triggerwarnung' => null]);
        $this->assertStringNotContainsString('id="bereichswarnung"', $this->get('/blickwinkel')->getContent());

        // An einen anderen Bereich gehängt, gilt sie dort für alle Unterseiten.
        Page::where('slug', 'wissen')->where('locale', 'de')->firstOrFail()
            ->update(['triggerwarnung' => ['Hier geht es um *Atteste*.']]);

        $html = $this->get('/atteste')->getContent();
        $this->assertStringContainsString('data-bereich="wissen"', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Atteste<\/strong>/', $this->dialog($html));
    }

    /** KEV-156, Taddis Frage: wie oft jede Kategorie angeklickt wird. */
    public function test_aufrufe_werden_ohne_kennung_gezaehlt(): void
    {
        $seite = Page::where('slug', 'blickwinkel')->where('locale', 'de')->firstOrFail();
        $geaendert = $seite->updated_at;

        $this->get('/blickwinkel')->assertOk();
        $this->get('/blickwinkel')->assertOk();
        $this->assertSame(2, $seite->fresh()->aufrufe);

        // Am Zeitstempel vorbei: Die Sitemap soll gelesene Seiten nicht als geändert melden.
        $this->assertEquals($geaendert, $seite->fresh()->updated_at);

        // Nicht gezählt: Suchmaschinen, Vorab-Laden, angemeldete Vereinsmitglieder.
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->get('/blickwinkel')->assertOk();
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 Firefox/130.0', 'Sec-Purpose' => 'prefetch'])
            ->get('/blickwinkel')->assertOk();
        $this->actingAs(User::factory()->create())->get('/blickwinkel')->assertOk();

        $this->assertSame(2, $seite->fresh()->aufrufe);
    }

    public function test_kacheln_zeigen_die_aufrufe(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        // Noch nicht aufgerufen: keine „0 Aufrufe“.
        $this->assertStringNotContainsString('data-aufrufe', $this->kacheln($this->get('/raum-fuer-ungesagtes')->getContent()));

        DB::table('pages')->where('slug', 'wall-of-shame')->where('locale', 'de')->update(['aufrufe' => 1233]);
        // Die englische Fassung zählt mit, es ist dieselbe Kategorie.
        $this->get('/en/wall-of-shame')->assertOk();
        DB::table('pages')->where('slug', 'unueberhoerbar')->where('locale', 'de')->update(['aufrufe' => 1]);

        $kacheln = $this->kacheln($this->get('/raum-fuer-ungesagtes')->getContent());
        $this->assertStringContainsString('1.234 Aufrufe', $kacheln);
        // Einzahl bei einem Aufruf.
        $this->assertMatchesRegularExpression('#</svg>\s*1 Aufruf\s*</span>#', $kacheln);
        $this->assertSame(2, substr_count($kacheln, 'data-aufrufe'));

        $this->assertStringContainsString('1,234 views', $this->kacheln($this->get('/en/raum-fuer-ungesagtes')->getContent()));

        // Im Panel abgeschaltet: keine Zahlen auf der Seite.
        $baustein = Page::where('slug', 'raum-fuer-ungesagtes')->where('locale', 'de')->firstOrFail()
            ->blocks()->where('typ', 'unterseiten')->firstOrFail();
        $baustein->update(['data' => ['titel' => 'Was du hier findest']]);

        $this->assertStringNotContainsString('data-aufrufe', $this->kacheln($this->get('/raum-fuer-ungesagtes')->getContent()));
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'unueberhoerbar')->where('locale', 'de')->firstOrFail();
        $seite->blocks()->first()->update(['data' => ['absaetze' => ['Vom Verein geändert.']]]);

        $bereich = Page::where('slug', 'raum-fuer-ungesagtes')->where('locale', 'de')->firstOrFail();
        $bereich->blocks()->first()->update(['data' => ['absaetze' => ['Auch geändert.']]]);
        $bereich->update(['triggerwarnung' => ['Eigene Warnung.']]);

        RaumFuerUngesagtesSeeder::anlegen();
        RaumFuerUngesagtesSeeder::bereichEinrichten();
        (require database_path('migrations/2026_10_22_235950_raum_fuer_ungesagtes_anlegen.php'))->up();
        (require database_path('migrations/2026_10_23_100100_raum_fuer_ungesagtes_bereich.php'))->up();

        $this->assertSame(1, Page::where('slug', 'unueberhoerbar')->where('locale', 'de')->count());
        $this->assertSame(1, Page::where('slug', 'raum-fuer-ungesagtes')->where('locale', 'de')->count());
        $this->assertSame(1, Page::where('slug', 'wort-fuer-wort')->where('locale', 'de')->count());
        $this->assertSame(['Vom Verein geändert.'], $seite->blocks()->first()->fresh()->data['absaetze']);
        $this->assertSame(3, $bereich->blocks()->count());
        $this->assertSame(['Auch geändert.'], $bereich->blocks()->first()->fresh()->data['absaetze']);
        $this->assertSame(['Eigene Warnung.'], $bereich->fresh()->triggerwarnung);
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

        $html = $this->get('/en/blickwinkel')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Perspectives\s*<\/h1>/u', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Sometimes a picture shows more of a truth than words ever could\.<\/strong>/u', $html);
        // KEV-156: Übersicht, Warnung und Kacheln
        $html = $this->get('/en/raum-fuer-ungesagtes')->assertOk()->getContent();
        $this->assertStringContainsString('Your voice', $html);
        $this->assertStringContainsString('For everything that went without a place for too long.', $html);
        $this->assertStringContainsString('This section contains content dealing with sexualised violence', $this->dialog($html));
        $this->assertStringContainsString('Understood – continue', $this->dialog($html));
        $this->assertStringContainsString('href="/en/wort-fuer-wort"', $this->kacheln($html));
        $this->assertStringContainsString('Word by word', $this->kacheln($html));

        // Die Warnung kommt auch auf den englischen Unterseiten, aus der englischen Übersicht.
        $this->assertStringContainsString('This section contains content', $this->dialog($this->get('/en/blickwinkel')->getContent()));
    }

    /** Der Abschnitt mit den Kacheln. */
    private function kacheln(string $html): string
    {
        $this->assertSame(1, preg_match('#<section[^>]*aria-labelledby="unterseiten-\d+".*?</section>#s', $html, $treffer));

        return $treffer[0];
    }

    /** Die Warnung vor dem Bereich. */
    private function dialog(string $html): string
    {
        $this->assertSame(1, preg_match('#<dialog id="bereichswarnung".*?</dialog>#s', $html, $treffer));

        return $treffer[0];
    }

    /** Sichtbarer Text ohne Kopf und Skripte, um übrig gebliebene Sternchen zu finden. */
    private function text(string $html): string
    {
        $html = preg_replace('#<(script|style|head)\b.*?</\1>#s', '', $html);

        return html_entity_decode(strip_tags($html));
    }
}
