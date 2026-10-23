<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\RueckblickSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * KEV-142: Rückblick mit dem Abschnitt „KE!N EINZELFALL im Dialog – Trauma,
 * Bindung und Beziehung“ und Taddis Bild. KEV-141: dazu „Unsere
 * Veranstaltungen zum Fonds Sexueller Missbrauch (FSM)“ mit ihrem P.S.
 * KEV-140: „Zusammenarbeit mit aus-unserer-sicht …“ mit den Präsentationen.
 * KEV-138: „Unsere Veranstaltungen rund um Assistenzhunde“ nach Trauma.
 * KEV-137: Taddis Einleitung über allem und ihr Titelbild.
 */
class RueckblickTest extends TestCase
{
    use RefreshDatabase;

    /** Die FSM-Seite, auf die das P.S. verlinkt, kommt aus dem Altbestand. */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_einleitung_und_titelbild_von_taddi(): void
    {
        $html = $this->get('/rueckblick')->getContent();

        // Einleitung ohne eigene Überschrift, vor der ersten Veranstaltung
        $einleitung = strpos($html, '<p>Manche Begegnungen, Projekte und Veranstaltungen wirken noch lange nach.');
        $this->assertNotFalse($einleitung);
        $this->assertLessThan(strpos($html, 'im Dialog – Trauma, Bindung und Beziehung'), $einleitung);
        $this->assertStringContainsString('Ehrenamtsmessen, Projekten und besonderen Momenten unserer Vereinsarbeit.', $html);
        $this->assertStringContainsString('<p>Was hinter uns liegt, hat Spuren hinterlassen – in unserer Arbeit', $html);
        $this->assertStringContainsString('und auf Erfahrungen, die bis heute in unserer Arbeit nachwirken.</p>', $html);
        // Vier Absätze: nichts eingeklappt
        $this->assertDoesNotMatchRegularExpression('/nachwirken\.<\/p>\s*<\/div>\s*<details/', $html);

        // Ihr Bild statt des Platzhalters, Album ganz im Bild
        $this->assertStringContainsString('src="/img/titelbilder/rueckblick.webp?v=', $html);
        $this->assertStringNotContainsString('/img/titelbilder/platzhalter.webp', $html);
        $this->assertStringContainsString('object-position: 0% 40%', $html);
        $this->assertSame([2000, 776], array_slice(getimagesize(public_path('img/titelbilder/rueckblick.webp')), 0, 2));
        $this->assertFileExists(public_path('img/titelbilder/rueckblick-1000.webp'));

        // Beschreibung für Suchmaschinen aus ihrem Text, ohne „zuletzt …“
        $this->assertStringContainsString('<meta name="description" content="Rückblick von KE!N EINZELFALL e.V.: Eindrücke von Veranstaltungen', $html);
    }

    /** Auf der Seite, wie sie vor KEV-137 war; Gepflegtes bleibt. */
    public function test_bestehende_seite_bekommt_einleitung_bild_und_beschreibung(): void
    {
        $seite = Page::where('slug', 'rueckblick')->where('locale', 'de')->firstOrFail();
        $seite->blocks()->where('typ', 'text')->delete();
        $seite->update(['titelbild' => Titelbilder::PLATZHALTER, 'meta_description' => RueckblickSeeder::META_DESCRIPTION_ALT]);

        (require database_path('migrations/2026_10_23_250000_rueckblick_einleitung_titelbild.php'))->up();

        $seite->refresh();
        $this->assertSame(Titelbilder::datei('rueckblick'), $seite->titelbild);
        $this->assertSame(RueckblickSeeder::META_DESCRIPTION, $seite->meta_description);
        $erster = $seite->blocks()->orderBy('position')->first();
        $this->assertSame(['text', RueckblickSeeder::EINLEITUNG], [$erster->typ, $erster->data['absaetze']]);
        $this->assertSame(1, $seite->blocks()->where('typ', 'text')->count());

        // Vom Verein gepflegt: bleibt.
        $erster->update(['data' => ['absaetze' => ['Vom Verein geändert.']]]);
        $seite->update(['titelbild' => '/storage/eigenes.webp', 'meta_description' => 'Eigene Beschreibung.']);
        (require database_path('migrations/2026_10_23_250000_rueckblick_einleitung_titelbild.php'))->up();

        $seite->refresh();
        $this->assertSame(['/storage/eigenes.webp', 'Eigene Beschreibung.'], [$seite->titelbild, $seite->meta_description]);
        $this->assertSame(1, $seite->blocks()->where('typ', 'text')->count());
        $this->assertSame(['Vom Verein geändert.'], $erster->fresh()->data['absaetze']);
    }

    public function test_abschnitt_mit_taddis_text_und_bild(): void
    {
        $html = $this->get('/rueckblick')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h2[^>]*>\s*KE!N EINZELFALL im Dialog – Trauma, Bindung und Beziehung\s*<\/h2>/u', $html);
        $this->assertStringContainsString('startete am 10. August 2026 unsere Veranstaltungsreihe', $html);
        $this->assertStringContainsString('Als Referenten durften wir Lutz-Ulrich Besser, Facharzt für Psychiatrie und Psychotherapie', $html);
        $this->assertStringContainsString('Fragen konnten auch ausschließlich über den Chat gestellt werden.', $html);
        // Komma ergänzt
        $this->assertStringContainsString('Mit den Fragen, die Menschen, die mit den Folgen von Trauma leben, beschäftigen.', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>KE!N EINZELFALL im Dialog – Wissen trifft Erfahrung\.<\/strong>/u', $html);

        // Stimmungsbild ohne Bildbeschreibung
        $this->assertStringContainsString('src="/img/rueckblick/trauma-bindung-und-beziehung.webp?v=', $html);
        $this->assertMatchesRegularExpression('/<img src="\/img\/rueckblick\/trauma-bindung-und-beziehung\.webp[^"]*" alt=""/', $html);
        $this->assertFileExists(public_path('img/rueckblick/trauma-bindung-und-beziehung.webp'));

        // Langer Text: Bild oben und mitlaufend, nach vier Absätzen eingeklappt
        $this->assertStringContainsString('lg:sticky', $html);
        $this->assertStringContainsString('Weiterlesen (4 weitere Absätze)', $html);
    }

    public function test_fsm_abschnitt_mit_taddis_text_bild_und_ps(): void
    {
        $html = $this->get('/rueckblick')->getContent();

        $this->assertMatchesRegularExpression('/<h2[^>]*>\s*Unsere Veranstaltungen zum Fonds Sexueller Missbrauch \(FSM\)\s*<\/h2>/u', $html);
        // Neueste Veranstaltung oben
        $this->assertLessThan(strpos($html, 'Unsere Veranstaltungen zum Fonds'), strpos($html, 'im Dialog – Trauma, Bindung und Beziehung'));

        $this->assertStringContainsString('haben viele Betroffene nicht nur verunsichert – sie haben Vertrauen erschüttert.', $html);
        $this->assertStringContainsString('zum Thema „FSM vs. offene Fragen“ angeboten', $html);
        $this->assertStringContainsString('Dann kam der 24. Juni 2025.', $html);
        $this->assertStringContainsString('Hilfe darf nicht selbst zur nächsten Belastung werden.', $html);
        // Korrigiert
        $this->assertStringContainsString('–, und dass ab sofort keine weiteren Neuanträge mehr möglich waren.', $html);
        $this->assertStringContainsString('So setzten wir im November 2025 unsere Veranstaltungen fort.', $html);
        $this->assertStringContainsString('Aus einer geplanten Ausfüllhilfe-Veranstaltung', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Gemeinsam KE!N EINZELFALL\.<\/strong>/u', $html);
        $this->assertStringContainsString('Weiterlesen (13 weitere Absätze)', $html);

        // Die Bilder wechseln die Seite: rechts, links, rechts, links
        foreach (['trauma' => 2, 'assistenzhunde' => 1, 'fsm' => 2, 'aus-unserer-sicht' => 1] as $bild => $order) {
            $this->assertMatchesRegularExpression('/class="order-1 lg:order-'.$order.'[^"]*"[^>]*>\s*<img src="\/img\/rueckblick\/'.$bild.'/', $html, $bild);
        }

        // Ihr P.S. als Kasten, der Link führt zu den Musterwidersprüchen
        $this->assertStringContainsString('Ablehnungsbescheid bekommen?', $html);
        $this->assertStringContainsString('Ablehnungsbescheid zum behinderungsbedingten Mehrbedarf', $html);
        $this->assertStringContainsString('href="/fsm-erweitertes-hilfesystem#dokumente"', $html);
        $this->assertStringNotContainsString('LINK zu den Widersprüchen', $html);

        $ziel = $this->get('/fsm-erweitertes-hilfesystem')->getContent();
        $this->assertStringContainsString('id="dokumente"', $ziel);
        $this->assertStringContainsString('Musterwiderspruch', $ziel);
    }

    public function test_aus_unserer_sicht_mit_praesentationen(): void
    {
        $html = $this->get('/rueckblick')->getContent();

        $this->assertMatchesRegularExpression('/<h2[^>]*>\s*Zusammenarbeit mit aus-unserer-sicht im Bereich Soziales Entschädigungsrecht: SGB XIV \/ OEG\s*<\/h2>/u', $html);
        // Ältester Abschnitt unten
        $this->assertGreaterThan(strpos($html, 'Unsere Veranstaltungen zum Fonds'), strpos($html, 'Zusammenarbeit mit aus-unserer-sicht'));
        $this->assertMatchesRegularExpression('/<strong[^>]*>aus-unserer-sicht e\.V\. löste sich zum 11\.02\.2026 auf<\/strong>/u', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>„SGB XIV – eine erste Auswertung des Sozialen Entschädigungsrechts aus unserer Sicht!“<\/strong>/u', $html);
        // Fachtag laut Folien und Protokoll am 30.11.2024
        $this->assertStringContainsString('war der Fachtag im November 2024.', $html);
        $this->assertStringNotContainsString('November 2025. Dort', $html);
        // Ihre Aufzählung als Liste
        $this->assertStringContainsString('<p>Untersucht werden sollten insbesondere:</p><ul><li>strukturelle Missstände und Verfahrensfehler,</li>', $html);
        $this->assertStringContainsString('src="/img/rueckblick/aus-unserer-sicht.webp', $html);

        // Die zwei Präsentationen als PDF
        $this->assertStringContainsString('Präsentationen vom Fachtag am 30.11.2024', $html);
        foreach (['workshop-sgb-xiv', 'ag-ser'] as $name) {
            $pfad = '/dokumente/2026/10/aus-unserer-sicht-fachtag-2024-'.$name.'.pdf';
            $this->assertStringContainsString('href="'.$pfad, $html, $name);
            $this->assertFileExists(public_path(ltrim($pfad, '/')));
            $pdf = file_get_contents(public_path(ltrim($pfad, '/')));
            // Keine weiteren Namen, keine Metadaten der fremden Vorlage
            $this->assertStringNotContainsString('Frauenwohnen', $pdf, $name);
        }
        $this->assertStringContainsString('PDF-Datei', $html);
    }

    public function test_assistenzhunde_mit_taddis_text_und_bild(): void
    {
        $html = $this->get('/rueckblick')->getContent();

        $this->assertMatchesRegularExpression('/<h2[^>]*>\s*Unsere Veranstaltungen rund um Assistenzhunde\s*<\/h2>/u', $html);
        // Teil I im Oktober 2025, Teil II danach: nach Trauma (2026), vor FSM (bis November 2025)
        $hier = strpos($html, 'rund um Assistenzhunde');
        $this->assertGreaterThan(strpos($html, 'im Dialog – Trauma, Bindung und Beziehung'), $hier);
        $this->assertLessThan(strpos($html, 'Unsere Veranstaltungen zum Fonds'), $hier);

        $this->assertStringContainsString('eine enorme Unterstützung im Alltag sein.', $html);
        foreach (['Teil I – Ausbildung, Rechte und Pflichten', 'Teil II – Halter &amp; Haltung: Verantwortung, Alltag und Rechte',
            '30.10.2025', 'Ullrich Horn von der Spürnasenakademie'] as $fett) {
            $this->assertMatchesRegularExpression('/<strong[^>]*>'.preg_quote($fett, '/').'<\/strong>/u', $html, $fett);
        }
        $this->assertMatchesRegularExpression('/<strong[^>]*>Nicht nur fragen: Was kann ein Assistenzhund für mich tun\?/u', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Assistenzhunde können Selbstständigkeit und Teilhabe ermöglichen – .*?ehrlich anzuschauen\.<\/strong>/u', $html);

        // Ihre zwei Aufzählungen als Listen, auch im eingeklappten Teil mit Punkten
        $this->assertStringContainsString('<p>Gemeinsam haben wir uns unter anderem damit beschäftigt,</p><ul><li>welche Voraussetzungen', $html);
        $this->assertStringContainsString('<li>und welche Rechte und Pflichten im Alltag eine Rolle spielen.</li></ul>', $html);
        $this->assertStringContainsString('<p>Dabei ging es auch um Fragen, die oft zu wenig gestellt werden:</p><ul><li>Welchen konkreten Hilfebedarf habe ich überhaupt?</li>', $html);
        $this->assertMatchesRegularExpression('/\[&_ul\]:list-disc \[&_ul\]:pl-5">(?:(?!<\/details>).)*Gemeinsam haben wir uns/su', $html);
        $this->assertStringContainsString('Weiterlesen (16 weitere Absätze)', $html);

        $this->assertMatchesRegularExpression('/<img src="\/img\/rueckblick\/assistenzhunde\.webp[^"]*" alt=""/', $html);
        $this->assertSame([1200, 675], array_slice(getimagesize(public_path('img/rueckblick/assistenzhunde.webp')), 0, 2));
    }

    public function test_mit_inhalt_fuer_suchmaschinen_offen(): void
    {
        $seite = Page::where('slug', 'rueckblick')->where('locale', 'de')->firstOrFail();
        $this->assertFalse($seite->noindex);
        $this->assertStringContainsString('/rueckblick</loc>', $this->get('/sitemap.xml')->getContent());
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        $seite = Page::where('slug', 'rueckblick')->where('locale', 'de')->firstOrFail();
        $block = $seite->blocks()->where('typ', 'text_media')->firstOrFail();
        $block->update(['data' => array_merge($block->data, ['absaetze' => ['Vom Verein geändert.']])]);

        RueckblickSeeder::anlegen();
        (require database_path('migrations/2026_10_23_180000_rueckblick_trauma_bindung.php'))->up();

        (require database_path('migrations/2026_10_23_190000_rueckblick_fsm.php'))->up();
        (require database_path('migrations/2026_10_23_200000_rueckblick_aus_unserer_sicht.php'))->up();
        (require database_path('migrations/2026_10_23_240000_rueckblick_assistenzhunde.php'))->up();
        (require database_path('migrations/2026_10_23_250000_rueckblick_einleitung_titelbild.php'))->up();

        // Einleitung, Trauma, Assistenzhunde, FSM, Hinweis, aus-unserer-sicht, Präsentationen
        $this->assertSame(7, $seite->blocks()->count());
        $this->assertSame(['Vom Verein geändert.'], $block->fresh()->data['absaetze']);
    }

    /** Auf der Seite, wie sie vor KEV-138 war: dazwischen eingefügt, Bilder wechseln weiter. */
    public function test_assistenzhunde_kommt_auf_bestehender_seite_an_seinen_platz(): void
    {
        $seite = Page::where('slug', 'rueckblick')->where('locale', 'de')->firstOrFail();
        $bild = fn (string $pfad) => $seite->blocks()->get()->first(fn ($b) => ($b->data['bild'] ?? null) === $pfad);

        // Stand vor KEV-138
        $bild(RueckblickSeeder::ASSISTENZ_BILD)->delete();
        foreach ([RueckblickSeeder::FSM_BILD => 'links', RueckblickSeeder::AUS_BILD => 'rechts'] as $pfad => $seiteBild) {
            $b = $bild($pfad);
            $b->update(['data' => array_merge($b->data, ['bild_seite' => $seiteBild])]);
        }
        // Vom Verein zwischen Trauma und FSM gesetzt: bleibt dort.
        $nachTrauma = $bild(RueckblickSeeder::TRAUMA_BILD)->position + 1;
        $seite->blocks()->where('position', '>=', $nachTrauma)->increment('position');
        $seite->blocks()->create(['typ' => 'hinweis', 'position' => $nachTrauma, 'data' => ['titel' => 'Vom Verein', 'text' => 'Eigener Kasten.']]);

        (require database_path('migrations/2026_10_23_240000_rueckblick_assistenzhunde.php'))->up();

        $reihenfolge = $seite->blocks()->orderBy('position')->get()
            ->map(fn ($b) => $b->data['bild'] ?? $b->data['titel'] ?? $b->typ)->all();
        $this->assertSame([
            'text', RueckblickSeeder::TRAUMA_BILD, 'Vom Verein', RueckblickSeeder::ASSISTENZ_BILD, RueckblickSeeder::FSM_BILD,
            RueckblickSeeder::FSM_PS_TITEL, RueckblickSeeder::AUS_BILD, RueckblickSeeder::AUS_DOKUMENTE_TITEL,
        ], $reihenfolge);
        $this->assertSame('rechts', $bild(RueckblickSeeder::FSM_BILD)->data['bild_seite']);
        $this->assertSame('links', $bild(RueckblickSeeder::AUS_BILD)->data['bild_seite']);

        // Ein zweites Mal ändert nichts mehr.
        (require database_path('migrations/2026_10_23_240000_rueckblick_assistenzhunde.php'))->up();
        $this->assertSame(8, $seite->blocks()->count());
        $this->assertSame('rechts', $bild(RueckblickSeeder::FSM_BILD)->data['bild_seite']);
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/rueckblick')->assertOk()->getContent();

        $this->assertStringContainsString('Some encounters, projects and events stay with us for a long time.', $html);
        $this->assertStringContainsString('content="A look back at KE!N EINZELFALL e.V.: impressions of events', $html);
        $this->assertStringContainsString('src="/img/titelbilder/rueckblick.webp?v=', $html);

        $this->assertStringContainsString('KE!N EINZELFALL im Dialog – Trauma, attachment and relationships', $html);
        $this->assertStringContainsString('our event series “KE!N EINZELFALL im Dialog – Knowledge meets experience” began on 10 August 2026', $html);
        $this->assertStringContainsString('/img/rueckblick/trauma-bindung-und-beziehung.webp', $html);

        $this->assertStringContainsString('Our events on the Fund for Sexual Abuse', $html);
        $this->assertStringContainsString('Received a rejection notice?', $html);
        $this->assertStringContainsString('href="/en/fsm-erweitertes-hilfesystem#dokumente"', $html);
        $this->assertStringContainsString('id="dokumente"', $this->get('/en/fsm-erweitertes-hilfesystem')->getContent());

        $this->assertStringContainsString('Our events on assistance dogs', $html);
        $this->assertMatchesRegularExpression('/<strong[^>]*>Part I – Training, rights and obligations<\/strong>/u', $html);
        $this->assertStringContainsString('<li>Who supports me during the training?</li>', $html);

        $this->assertStringContainsString('Cooperation with aus-unserer-sicht on social compensation law', $html);
        $this->assertStringContainsString('Results of the SGB XIV/SER working group (5 slides, in German)', $html);
    }
}
