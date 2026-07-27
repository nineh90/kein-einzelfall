<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageBlock;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BausteineTest extends TestCase
{
    use RefreshDatabase;

    private function seiteMitBaustein(string $typ, array $daten): string
    {
        $seite = Page::create([
            'slug' => 'testseite',
            'titel' => 'Testseite',
            'published_at' => now(),
        ]);

        $seite->blocks()->create(['typ' => $typ, 'position' => 0, 'data' => $daten]);

        return $this->get('/testseite')->getContent();
    }

    public function test_zu_jedem_blocktyp_gibt_es_eine_komponente(): void
    {
        // Ein Typ ohne Komponente wird beim Rendern still übersprungen — die
        // Redaktion könnte ihn im Panel auswählen und würde nichts sehen.
        foreach (array_keys(PageBlock::TYPEN) as $typ) {
            $this->assertTrue(
                view()->exists('components.blocks.'.str_replace('_', '-', $typ)),
                "Blocktyp '{$typ}' hat keine Komponente"
            );
        }
    }

    public function test_ablauf_in_schritten_ist_eine_nummerierte_liste(): void
    {
        // Als <ol> ausgezeichnet, damit Screenreader „1 von 3" ansagen.
        $html = $this->seiteMitBaustein('schritte', [
            'titel' => 'So stellst du den Antrag',
            'schritte' => [
                ['titel' => 'Formular anfordern', 'text' => 'Beim Versorgungsamt.'],
                ['titel' => 'Unterlagen sammeln', 'text' => 'Atteste und Nachweise.'],
                ['titel' => 'Antrag abgeben', 'text' => 'Schriftlich einreichen.'],
            ],
        ]);

        $this->assertStringContainsString('<ol', $html);
        $this->assertStringContainsString('Formular anfordern', $html);
        $this->assertStringContainsString('Antrag abgeben', $html);
    }

    public function test_fragen_und_antworten_funktionieren_ohne_javascript(): void
    {
        $html = $this->seiteMitBaustein('accordion', [
            'titel' => 'Häufige Fragen',
            'eintraege' => [
                ['frage' => 'Wie lange dauert das?', 'antwort' => '<p>Unterschiedlich.</p>'],
            ],
        ]);

        // Natives <details> statt Alpine
        $this->assertStringContainsString('<details', $html);
        $this->assertStringContainsString('Wie lange dauert das?', $html);
        // Antwort bleibt im Dokument — auch für Suchmaschinen sichtbar
        $this->assertStringContainsString('Unterschiedlich.', $html);
    }

    public function test_fragen_und_antworten_bringen_faq_auszeichnung_mit(): void
    {
        $html = $this->seiteMitBaustein('accordion', [
            'eintraege' => [['frage' => 'Was kostet das?', 'antwort' => '<p>Nichts.</p>']],
        ]);

        $this->assertStringContainsString('"@type":"FAQPage"', $html);
        $this->assertStringContainsString('"@type":"Question"', $html);
    }

    public function test_hinweis_kennt_verschiedene_dringlichkeiten(): void
    {
        $frist = $this->seiteMitBaustein('hinweis', [
            'art' => 'frist',
            'text' => 'Der Widerspruch muss innerhalb eines Monats eingehen.',
        ]);

        $this->assertStringContainsString('Auf die Frist achten', $frist);
        $this->assertStringContainsString('innerhalb eines Monats', $frist);
    }

    public function test_text_mit_bild_zeigt_platzhalter_solange_kein_bild_da_ist(): void
    {
        // Bildmaterial kommt vom Verein — bis dahin soll die Seite trotzdem
        // fertig aussehen.
        $html = $this->seiteMitBaustein('text_media', [
            'titel' => 'Wer wir sind',
            'absaetze' => ['Ein Absatz.'],
        ]);

        $this->assertStringContainsString('Platzhalter', $html);
        $this->assertStringContainsString('Wer wir sind', $html);
    }

    public function test_text_mit_bild_setzt_alt_text_wenn_vorhanden(): void
    {
        $html = $this->seiteMitBaustein('text_media', [
            'absaetze' => ['Text.'],
            'bild' => '/img/logo.png',
            'bild_alt' => 'Vorstand des Vereins bei einer Sitzung',
        ]);

        $this->assertStringContainsString('alt="Vorstand des Vereins bei einer Sitzung"', $html);
    }

    public function test_dokumentenliste_bleibt_im_satzspiegel(): void
    {
        // Vorher lief sie randlos über die volle Fensterbreite und fiel aus
        // dem Layout der Seite.
        $html = $this->seiteMitBaustein('download_list', [
            'titel' => 'Dokumente',
            'dokumente' => [['titel' => 'Merkblatt', 'url' => '/test.pdf', 'bytes' => 102400]],
        ]);

        preg_match('/<section[^>]*aria-labelledby="dl-[^"]*"[^>]*>(.*?)<\/section>/s', $html, $m);
        $abschnitt = $m[0] ?? '';

        $this->assertStringContainsString('max-w-6xl', $abschnitt);
        $this->assertStringContainsString('PDF-Datei, 100 KB', $abschnitt);
    }

    public function test_seitentitel_verwenden_umlaute(): void
    {
        // Aus dem Slug abgeleitet hiesse die Seite „Ueber Uns Vorstand Und Team".
        $this->seed(AltseiteSeeder::class);

        foreach (Page::pluck('titel', 'slug') as $slug => $titel) {
            $this->assertDoesNotMatchRegularExpression(
                '/\b\w*(ae|oe|ue|Ae|Oe|Ue)\w+\b/u',
                $titel,
                "Titel von /{$slug} schreibt Umlaute aus: „{$titel}“"
            );
        }

        $this->assertSame(
            'Über uns – Vorstand und Team',
            Page::where('slug', 'ueber-uns-vorstand-und-team')->value('titel')
        );
    }
}
