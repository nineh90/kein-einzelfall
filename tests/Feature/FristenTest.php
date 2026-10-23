<?php

namespace Tests\Feature;

use App\Filament\Resources\Fristen\Pages\CreateFrist;
use App\Filament\Resources\Fristen\Pages\EditFrist;
use App\Filament\Resources\Fristen\Pages\ListFristen;
use App\Models\Frist;
use App\Models\Page;
use App\Models\User;
use App\Support\Suche;
use Database\Seeders\FristenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * KEV-152: „Fristen und Möglichkeiten“ unter „Wissen“ mit Taddis
 * Fristenübersicht (Suche, A–Z, aufklappbare Karten, Schlagworte) und ihrem
 * Leitfaden. Die Fristen pflegt der Verein im Panel.
 */
class FristenTest extends TestCase
{
    use RefreshDatabase;

    public function test_seite_unter_wissen_nach_atteste(): void
    {
        $wissen = collect(config('navigation.main'))->firstWhere('url', '/wissen');
        $urls = array_column($wissen['children'], 'url');
        // Taddis Nummern: 1. Atteste, 4. Formulare (KEV-150), 5. Fristen
        $this->assertSame(array_search('/formulare', $urls) + 1, array_search('/fristen', $urls));

        $html = $this->get('/fristen')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Fristen und Möglichkeiten\s*<\/h1>/u', $html);
        $this->assertStringContainsString('src="/img/titelbilder/fristen.webp?v=', $html);
        $this->assertStringContainsString('object-position: 0% 65%', $html);
        $this->assertSame([2000, 776], array_slice(getimagesize(public_path('img/titelbilder/fristen.webp')), 0, 2));
        $this->assertStringContainsString('/fristen</loc>', $this->get('/sitemap.xml')->getContent());
    }

    /** Taddis Vorschlag: zwei Kacheln, links „Mögliche Fristen“, rechts „Frist bekommen“. */
    public function test_zwei_kacheln_springen_zu_ihrem_bereich(): void
    {
        $html = $this->get('/fristen')->getContent();

        $this->assertStringContainsString('Fristen können schnell Druck machen', $html);
        $this->assertStringContainsString('Du musst nicht alles sofort schaffen.', $html);

        $links = strpos($html, 'href="#moegliche-fristen"');
        $rechts = strpos($html, 'href="#frist-bekommen"');
        $this->assertNotFalse($links);
        $this->assertGreaterThan($links, $rechts);

        // Die Ziele gibt es, in dieser Reihenfolge.
        $this->assertLessThan(strpos($html, 'id="frist-bekommen"'), strpos($html, 'id="moegliche-fristen"'));
        $this->assertMatchesRegularExpression('/id="frist-bekommen"[^>]*>.*?<h2[^>]*>Frist bekommen – Was jetzt\?<\/h2>/su', $html);
    }

    public function test_fristenuebersicht_wie_von_taddi_vorgeschlagen(): void
    {
        $this->assertCount(19, FristenSeeder::fristen());

        $html = $this->get('/fristen')->getContent();

        // Suchmaske, A–Z, Karten, Schlagworte
        $this->assertStringContainsString('Welche Frist suchst du?', $html);
        $this->assertStringContainsString('19 Fristen', $html);
        foreach (['A', 'B', 'F', 'K', 'R', 'S', 'U', 'W'] as $buchstabe) {
            $this->assertStringContainsString('href="#buchstabe-'.strtolower($buchstabe).'"', $html, $buchstabe);
        }
        $this->assertSame(19, substr_count($html, '<details id="'));
        $this->assertMatchesRegularExpression('/<details id="widerspruch"[^>]*>.*?Frist: grundsätzlich 1 Monat.*?>Widerspruch<\/span>.*?>Bescheid<\/span>/su', $html);
        // Zwei Zeilen bei der Revision
        $this->assertStringContainsString('Einlegung: grundsätzlich 1 Monat<br />', $html);

        // Der Text in der Karte, mit Zwischenüberschriften und Listen
        $this->assertStringContainsString('<h3>Behördlich gesetzte Fristen können verlängert werden</h3>', $html);
        $this->assertStringContainsString('<li>du auf Befunde, Arztberichte oder Gutachten wartest,</li>', $html);
        $this->assertStringContainsString('§ 84 SGG.', $html);

        // Ohne Suche alles zu, Umlaut unter seinem Grundbuchstaben.
        // \sopen(?=[\s>]): das Attribut, nicht die Klasse „open:border-green“.
        $this->assertDoesNotMatchRegularExpression('/<details id="[^"]+"[^>]*\sopen(?=[\s>])/', $html);
        $this->assertLessThan(strpos($html, 'id="untaetigkeitsklage-antrag"'), strpos($html, 'id="ueberpruefungsantrag"'));
        $this->assertGreaterThan(strpos($html, 'id="buchstabe-u"'), strpos($html, 'id="ueberpruefungsantrag"'));
    }

    /** Ohne JavaScript filtert der Server, die Treffer stehen offen. */
    public function test_suche_auf_der_seite(): void
    {
        $passend = Frist::all()->filter->passt('Untätigkeitsklage')->count();
        $this->assertGreaterThanOrEqual(2, $passend);

        $html = $this->get('/fristen?suche=Untätigkeitsklage')->assertOk()->getContent();

        $this->assertStringContainsString("Treffer: {$passend} von 19", $html);
        $this->assertMatchesRegularExpression('/<details id="untaetigkeitsklage-antrag"[^>]*\sopen(?=[\s>])/', $html);
        $this->assertMatchesRegularExpression('/<details id="berufung"[^>]*\shidden/', $html);
        // Schlagworte zählen mit.
        $this->assertTrue(Frist::where('slug', 'berufung')->first()->passt('gericht'));
    }

    public function test_hinweis_leitfaden_und_noch_wichtig(): void
    {
        $html = $this->get('/fristen')->getContent();

        // „Wichtig“ als Kasten, die Adresse als Link
        $this->assertMatchesRegularExpression('/Wichtig\s*<\/p>\s*.*?ersetzt keine Rechtsberatung.*?href="mailto:kontakt@kein-einzelfall\.de"/su', $html);

        // Ihr Leitfaden in acht Schritten
        $this->assertMatchesRegularExpression('/<ol class="flex flex-col">(.*?)<\/ol>/s', $html);
        preg_match('/<ol class="flex flex-col">(.*?)<\/ol>/s', $html, $leitfaden);
        $this->assertSame(8, substr_count($leitfaden[1], '<li'));
        $this->assertStringContainsString('Eingangstag notieren', $leitfaden[1]);
        $this->assertStringContainsString('Versand nachweisen können', $leitfaden[1]);

        // „Noch wichtig“ mit ihrer Aufzählung
        $this->assertMatchesRegularExpression('/<p>Fristen können unter anderem davon abhängen:<\/p><ul><li>um welches Verfahren es geht,<\/li>/u', $html);
        $this->assertStringContainsString('<li>ob im konkreten Einzelfall besondere Umstände bestehen.</li></ul>', $html);
        $this->assertStringContainsString('href="mailto:kontakt@kein-einzelfall.de"', $html);
        $this->assertStringNotContainsString('Kontakt@kein-einzelfall.de', $html);
    }

    /** Aufzählungen im Textbaustein (KEV-152): sonst bleibt alles, wie es war. */
    public function test_aufzaehlung_im_textbaustein(): void
    {
        $this->assertSame('<p>Nur ein Absatz mit <strong class="font-semibold text-ink">Fett</strong>.</p>', (string) absatz('Nur ein Absatz mit *Fett*.'));
        $this->assertSame(
            '<p>Davor:</p><ul><li>eins</li><li>zwei</li></ul><p>Danach.</p>',
            (string) absatz("Davor:\n- eins\n- zwei\nDanach."),
        );
        $this->assertSame('<ul><li>&lt;b&gt;</li></ul>', (string) absatz('- <b>'));
    }

    /** Taddi: „Inhalt muss erweiterbar und änderbar sein.“ */
    public function test_verein_pflegt_die_fristen_im_panel(): void
    {
        $redaktion = User::factory()->redaktion()->create();
        $this->actingAs($redaktion)->get('/admin/fristen')->assertOk();

        Livewire::actingAs($redaktion)
            ->test(ListFristen::class)
            ->assertCanSeeTableRecords(Frist::all());

        Livewire::actingAs($redaktion)
            ->test(CreateFrist::class)
            ->fillForm([
                'titel' => 'Erinnerung', 'slug' => 'erinnerung', 'frist' => 'Frist: grundsätzlich 2 Wochen',
                'schlagworte' => ['Gericht'], 'inhalt' => '<p>Neu vom Verein.</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $widerspruch = Frist::where('slug', 'widerspruch')->firstOrFail();
        Livewire::actingAs($redaktion)
            ->test(EditFrist::class, ['record' => $widerspruch->getKey()])
            ->fillForm(['frist' => 'Frist: geändert'])
            ->call('save')
            ->assertHasNoFormErrors();

        $html = $this->get('/fristen')->getContent();
        $this->assertStringContainsString('20 Fristen', $html);
        $this->assertStringContainsString('<details id="erinnerung"', $html);
        $this->assertStringContainsString('Neu vom Verein.', $html);
        $this->assertStringContainsString('Frist: geändert', $html);
        // Der übrige Text der Frist hat das Speichern überstanden.
        $this->assertStringContainsString('Muss der Widerspruch sofort begründet werden?', (string) $widerspruch->fresh()->inhalt);
    }

    public function test_websuche_fuehrt_zur_karte(): void
    {
        $treffer = collect(app(Suche::class)->suchen('Fristverlängerung')['treffer']);

        $frist = $treffer->firstWhere('art', 'frist');
        $this->assertSame('/fristen#fristverlaengerung', $frist['url']);
        $this->assertSame('Fristen und Möglichkeiten', $frist['bereich']);
        // „Frist“ trifft fast jede, es bleiben höchstens fünf.
        $this->assertLessThanOrEqual(5, collect(app(Suche::class)->suchen('Frist')['treffer'])->where('art', 'frist')->count());
    }

    public function test_legt_nur_an_was_fehlt(): void
    {
        Frist::where('slug', 'widerspruch')->update(['frist' => 'Vom Verein geändert.']);
        $seite = Page::where('slug', 'fristen')->where('locale', 'de')->firstOrFail();
        $seite->blocks()->where('typ', 'quick_access')->delete();

        FristenSeeder::anlegen();
        (require database_path('migrations/2026_10_23_160100_fristen_anlegen.php'))->up();

        $this->assertSame(1, Page::where('slug', 'fristen')->where('locale', 'de')->count());
        $this->assertSame(19, Frist::count());
        $this->assertSame('Vom Verein geändert.', Frist::where('slug', 'widerspruch')->value('frist'));
        $this->assertSame(0, $seite->blocks()->where('typ', 'quick_access')->count());
    }

    public function test_englische_fassung(): void
    {
        $this->seed(UebersetzungenSeeder::class);

        $html = $this->get('/en/fristen')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Deadlines and options\s*<\/h1>/u', $html);
        $this->assertStringContainsString('Which deadline are you looking for?', $html);
        $this->assertStringContainsString('19 deadlines', $html);
        $this->assertStringContainsString('Restoration to the previous position – authority', $html);
        $this->assertStringContainsString('>Objection</span>', $html);
        // Gleiche Sprungziele in jeder Sprache
        $this->assertStringContainsString('href="#moegliche-fristen"', $html);
        $this->assertStringContainsString('id="moegliche-fristen"', $html);
        $this->assertStringContainsString('<li>which proceedings are involved,</li>', $html);

        $treffer = collect(app(Suche::class)->suchen('Objection', 'en')['treffer'])->firstWhere('art', 'frist');
        $this->assertStringStartsWith('/en/fristen#', $treffer['url']);
    }
}
