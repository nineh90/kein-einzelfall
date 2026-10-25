<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\FormulareSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-150: „Formulare“ unter „Wissen“, Taddis Text, die Formulare folgen.
 * KEV-151: ihr Titelbild „4. Formulare“.
 * KEV-250: ihre Formulare, am PC ausfüllbar.
 */
class FormulareTest extends TestCase
{
    use RefreshDatabase;

    public function test_seite_unter_wissen_zwischen_atteste_und_fristen(): void
    {
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $urls = array_column($wissen['children'], 'url');
        $hier = array_search('/formulare', $urls);

        $this->assertSame('/atteste', $urls[$hier - 1]);
        $this->assertSame('/fristen', $urls[$hier + 1]);

        $html = $this->get('/formulare')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Formulare\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Manchmal braucht es einfach den nächsten Schritt – und dafür das richtige Formular.', $html);
        $this->assertStringContainsString('zum Beispiel Schweigepflichtentbindungen, Vollmachten, Mitgliedsanträge und weitere Unterlagen.', $html);
        $this->assertStringContainsString('Damit du nicht lange suchen musst', $html);
    }

    /** KEV-250: Taddis Formulare in vier Listen, die Seite ist jetzt für Suchmaschinen offen. */
    public function test_formulare_in_vier_listen(): void
    {
        $html = $this->get('/formulare')->getContent();

        $this->assertStringNotContainsString('noindex', $html);
        foreach (['Mitgliedschaft', 'Vollmacht und Schweigepflichtentbindung', 'Freigaben für Bilder, Texte und Tonaufnahmen',
            'Gruppen und Veranstaltungen'] as $liste) {
            $this->assertMatchesRegularExpression('/<h2[^>]*>\s*'.preg_quote($liste, '/').'\s*<\/h2>/u', $html, $liste);
        }

        // Jede Datei liegt bereit, jeder Eintrag wird angezeigt.
        $dokumente = collect(FormulareSeeder::listen())->flatMap(fn ($l) => $l['dokumente']);
        $this->assertCount(16, $dokumente);
        foreach ($dokumente as $d) {
            $this->assertFileExists(public_path(ltrim($d['url'], '/')), $d['url']);
            $this->assertStringContainsString('href="'.$d['url'].'"', $html);
        }
        $this->assertSame(8, substr_count($html, '(ausfüllbar)'));

        // So geht's: am PC, auf Papier, Unterschrift, wohin
        $this->assertMatchesRegularExpression('/<h2[^>]*>\s*So nutzt du die Formulare\s*<\/h2>/u', $html);
        $this->assertStringContainsString('Jedes Formular lässt sich auch ausdrucken und von Hand ausfüllen.', $html);
        $this->assertStringContainsString('brauchen deine eigenhändige Unterschrift', $html);
        $this->assertStringContainsString('href="mailto:kontakt@kein-einzelfall.de"', $html);
    }

    /** Die als ausfüllbar bezeichneten PDFs haben wirklich Formularfelder, die übrigen nicht. */
    public function test_ausfuellbare_pdfs_haben_felder(): void
    {
        foreach (collect(FormulareSeeder::listen())->flatMap(fn ($l) => $l['dokumente']) as $d) {
            $inhalt = file_get_contents(public_path(ltrim($d['url'], '/')));
            $felder = preg_match_all('#/FT\s*/(Tx|Btn)#', $inhalt);

            if (str_contains($d['titel'], 'ausfüllbar')) {
                $this->assertStringContainsString('/AcroForm', $inhalt, $d['titel']);
                $this->assertGreaterThanOrEqual(10, $felder, $d['titel']);
            } else {
                $this->assertSame(0, $felder, $d['titel']);
            }
        }
    }

    /** Bestehende Seite im Zustand von KEV-150: Listen kommen dazu; hat der Verein schon etwas hochgeladen, nicht. */
    public function test_migration_nur_bei_leerer_liste(): void
    {
        $seite = Page::where('slug', 'formulare')->where('locale', 'de')->sole();
        $migration = require database_path('migrations/2026_10_25_230000_formulare_einbauen.php');

        $seite->blocks()->delete();
        $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => FormulareSeeder::ABSAETZE]]);
        $seite->blocks()->create(['typ' => 'download_list', 'position' => 1, 'data' => ['titel' => FormulareSeeder::LISTE_TITEL]]);
        $seite->update(['noindex' => true]);

        $migration->up();
        $this->assertSame(['text', 'text', 'download_list', 'download_list', 'download_list', 'download_list'],
            $seite->blocks()->orderBy('position')->pluck('typ')->all());
        $this->assertFalse($seite->fresh()->noindex);

        $seite->blocks()->delete();
        $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => ['x']]]);
        $seite->blocks()->create(['typ' => 'download_list', 'position' => 1, 'data' => ['dokumente' => [['titel' => 'Eigenes', 'url' => '/x.pdf']]]]);
        $migration->up();
        $this->assertSame(2, $seite->blocks()->count());
    }

    /** KEV-248: Unterzeile unter dem Titel, auch nachträglich und auf Englisch. */
    public function test_unterzeile(): void
    {
        $this->assertStringContainsString('Übersichtlich an einem Ort', $this->get('/formulare')->getContent());

        $seite = Page::where('slug', 'formulare')->where('locale', 'de')->sole();
        $seite->update(['untertitel' => null]);
        (require database_path('migrations/2026_10_25_240000_formulare_unterzeile.php'))->up();
        $this->assertSame('Übersichtlich an einem Ort', $seite->fresh()->untertitel);

        $this->seed(UebersetzungenSeeder::class);
        $this->assertStringContainsString('All in one place', $this->get('/en/formulare')->getContent());
    }

    public function test_titelbild_von_taddi(): void
    {
        $html = $this->get('/formulare')->getContent();

        $this->assertStringContainsString('src="/img/titelbilder/formulare.webp?v=', $html);
        $this->assertStringContainsString('object-position: 0% 40%', $html);
        $this->assertStringNotContainsString('platzhalter.webp', $html);
        $this->assertNotContains('formulare', Titelbilder::PLATZHALTER_SEITEN);
        // Rechts um Wand verlängert wie die übrigen Bilder aus „3. Wissen“
        $this->assertSame([2000, 776], array_slice(getimagesize(public_path('img/titelbilder/formulare.webp')), 0, 2));
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'formulare')->where('locale', 'de')->firstOrFail();
        $seite->blocks()->first()->update(['data' => ['absaetze' => ['Vom Verein geändert.']]]);

        FormulareSeeder::anlegen();
        (require database_path('migrations/2026_10_23_210000_formulare_anlegen.php'))->up();

        $this->assertSame(1, Page::where('slug', 'formulare')->where('locale', 'de')->count());
        $this->assertSame(['Vom Verein geändert.'], $seite->blocks()->first()->fresh()->data['absaetze']);
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/formulare')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Forms\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Sometimes all it takes is the next step', $html);
        $this->assertStringContainsString('How to use the forms', $html);
        $this->assertStringContainsString('Membership application (fillable, German)', $html);
    }
}
