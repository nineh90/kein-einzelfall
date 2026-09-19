<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Tests\TestCase;

/**
 * Der vorgeschaltete Hinweis auf belastende Inhalte.
 *
 * Erste Zeile im Strukturpapier des Vereins und in der Besprechung vom
 * 02.08.2026 noch einmal bestätigt. Was hier abgesichert wird, ist nicht das
 * Aussehen, sondern die eine Eigenschaft, an der alles hängt: Der Hinweis
 * steht im Server-HTML und wirkt auch dann, wenn kein JavaScript läuft.
 *
 * Ein Overlay, das erst JavaScript aufbaut, gibt bei jedem Skriptfehler den
 * Inhalt ungewarnt frei. Bei dieser Zielgruppe ist das kein Schönheitsfehler.
 */
class TriggerWarnungTest extends TestCase
{
    /** Die Migration legt die Seite an — hier nur der bequeme Zugriff darauf. */
    private function warnung(): Page
    {
        return Page::where('slug', Page::TRIGGER_SLUG)->firstOrFail();
    }

    public function test_die_migration_legt_die_warnung_an(): void
    {
        // Sie muss auf jeder Installation stehen, nicht nur auf frisch
        // aufgesetzten: Seeder laufen auf dem Server nicht.
        $this->assertSame(1, Page::where('slug', Page::TRIGGER_SLUG)->count());
        $this->assertTrue($this->warnung()->blocks()->exists());
    }

    public function test_warnung_steht_im_server_html_und_ist_ohne_javascript_sichtbar(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // `open` ist der ganze Punkt: Ohne das Attribut zeigt der Browser ein
        // <dialog> überhaupt nicht an, und ohne JavaScript käme nie eines.
        $this->assertMatchesRegularExpression(
            '/<dialog[^>]*\bid="trigger-warnung"[^>]*\bopen\b/',
            $html,
            'Die Warnung muss als <dialog open> im ausgelieferten HTML stehen'
        );

        $this->assertStringContainsString('Auf dieser Website geht es um Straftaten', $html);
    }

    public function test_warnung_steht_auf_jeder_seite(): void
    {
        $this->seed(AltseiteSeeder::class);

        foreach (['/', '/verein', '/spenden', '/anfragen'] as $pfad) {
            $this->get($pfad)
                ->assertOk()
                ->assertSee('id="trigger-warnung"', false);
        }
    }

    public function test_der_notausgang_im_dialog_ist_ein_echter_link(): void
    {
        $html = $this->get('/')->getContent();

        // Kein <button>: Ohne JavaScript muss der Weg hier raus trotzdem führen.
        $this->assertMatchesRegularExpression(
            '/<a[^>]+href="'.preg_quote(config('navigation.exit_url'), '/').'"[^>]*data-notausgang/',
            $html
        );
    }

    public function test_wegklicken_und_abbestellen_verlangen_javascript(): void
    {
        $html = $this->get('/')->getContent();

        /*
         * Beides sind gespeicherte Zustände im Browser. Knopf und Kästchen
         * tragen deshalb ein Merkmal, über das die CSS sie ausblendet, solange
         * das Skript sie nicht verdrahtet hat — ein Bedienelement, das auf
         * Druck nichts tut, ist schlimmer als gar keines.
         */
        $this->assertStringContainsString('data-trigger-braucht-js', $html);
        $this->assertStringContainsString('data-trigger-weiter', $html);
        $this->assertStringContainsString('data-trigger-nie', $html);
    }

    public function test_nicht_mehr_anzeigen_ist_ein_kontrollkaestchen(): void
    {
        /*
         * Der Verein hat es selbst so beschrieben: „…oder aber auch auswählen
         * kann ‚diese Meldung nicht mehr anzeigen‘“. Auswählen, nicht drücken —
         * es ist eine Einstellung und keine Handlung. Als dritter Knopf stand
         * es gleichrangig neben zwei Handlungen und zwang zu einer Entscheidung,
         * die niemand treffen wollte.
         *
         * Und es muss ein natives <input> sein: Tastaturbedienung, Vorlesehilfe
         * und der Zustand „ausgewählt“ kommen dann vom Browser.
         */
        $html = $this->get('/')->getContent();

        $this->assertMatchesRegularExpression(
            '/<input[^>]+type="checkbox"[^>]*data-trigger-nie/',
            $html
        );
    }

    public function test_beide_wege_aus_dem_dialog_sind_gleich_gebaut(): void
    {
        /*
         * Erste Fassung: drei Bedienelemente in drei Größen, eines per ms-auto
         * an den Rand geschoben. Jetzt kommen beide aus derselben
         * Knopf-Komponente — dieselbe Polsterung, dieselbe Schriftgröße.
         *
         * Die Maße prüft der Browser-Test; hier steht nur, dass niemand wieder
         * eigene Klassen danebenschreibt.
         */
        // Erst den Dialog herausschneiden: Der Notausgang im Seitenkopf trägt
        // dieselbe Kennzeichnung und hat mit gutem Grund ein anderes Format.
        preg_match('/<dialog[^>]*id="trigger-warnung".*?<\/dialog>/s', $this->get('/')->getContent(), $d);
        $dialog = $d[0] ?? '';

        $this->assertNotSame('', $dialog, 'Der Dialog fehlt');

        preg_match('/<button[^>]*data-trigger-weiter[^>]*>/', $dialog, $weiter);
        preg_match('/<a[^>]*data-notausgang[^>]*>/', $dialog, $exit);

        $this->assertNotEmpty($weiter, 'Knopf „weiterlesen“ fehlt');
        $this->assertNotEmpty($exit, 'Notausgang im Dialog fehlt');

        // Die gemeinsame Geometrie der Knopf-Komponente, Größe „base“ — samt
        // Rahmen, der bei der gefüllten Variante durchsichtig ist. Ohne ihn
        // wäre der umrandete Knopf 2 px höher.
        foreach (['px-6', 'py-3', 'text-base', 'rounded-full', 'border'] as $klasse) {
            $this->assertStringContainsString($klasse, $weiter[0], "Knopf ohne {$klasse}");
            $this->assertStringContainsString($klasse, $exit[0], "Notausgang ohne {$klasse}");
        }
    }

    public function test_entwurf_schaltet_die_warnung_ab(): void
    {
        // Der Ausschalter für den Verein: kein Deployment nötig.
        $this->warnung()->update(['published_at' => null]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="trigger-warnung"', false);
    }

    public function test_ohne_warnungsseite_laedt_die_website_weiter(): void
    {
        $this->warnung()->delete();

        $this->get('/')->assertOk()->assertDontSee('id="trigger-warnung"', false);
    }

    public function test_die_warnung_bringt_die_ueberschriften_gliederung_nicht_durcheinander(): void
    {
        /*
         * Der Hinweis steht im Quelltext vor dem Seiteninhalt. Eine Überschrift
         * darin führte die Gliederung an, bevor die h1 der Seite kommt — genau
         * der Fehler, den die A11y-Toolbar an derselben Stelle schon einmal
         * gemacht hat.
         *
         * Auch dann nicht, wenn jemand im Panel eine Baustein-Überschrift setzt:
         * die wird beim Rendern im Dialog verworfen.
         */
        $this->warnung()->blocks()->first()->update([
            'data' => ['titel' => 'Eine Überschrift', 'absaetze' => ['Text.']],
        ]);

        $html = preg_replace(
            '/<(script|style|template).*?<\/\1>/s',
            '',
            $this->get('/')->getContent()
        );

        preg_match_all('/<h([1-6])[^>]*>/', $html, $treffer);
        $ebenen = array_map('intval', $treffer[1]);

        $this->assertNotEmpty($ebenen);
        $this->assertSame(1, $ebenen[0], 'Erste Überschrift muss die h1 der Seite sein');
        $this->assertStringNotContainsString('Eine Überschrift', $html);
    }

    public function test_warnung_faellt_sichtbar_auf_die_standardsprache_zurueck(): void
    {
        Language::where('code', 'en')->update(['aktiv' => true]);
        Language::memoLeeren();

        $html = $this->get('/en')->assertOk()->getContent();

        // Lieber auf Deutsch als gar nicht — aber ausgezeichnet, damit eine
        // Vorlesehilfe nicht deutschen Text englisch ausspricht (WCAG 3.1.2).
        $this->assertStringContainsString('id="trigger-warnung"', $html);
        $this->assertMatchesRegularExpression(
            '/<dialog[^>]*\bid="trigger-warnung"[^>]*\blang="de"/',
            $html
        );
    }

    public function test_die_warnung_ist_von_suchmaschinen_ausgenommen(): void
    {
        // Sie steht auf jeder Seite. Als eigener Treffer in einer Ergebnisliste
        // wäre sie ein Einstieg ins Nichts.
        $this->assertTrue($this->warnung()->noindex);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(Page::TRIGGER_SLUG, false);
    }

    public function test_die_warnung_hat_eine_eigene_adresse_zum_nachlesen(): void
    {
        $this->get('/'.Page::TRIGGER_SLUG)
            ->assertOk()
            ->assertSee('Hinweis zu den Inhalten dieser Website');
    }
}
