<?php

namespace Tests\Feature;

use App\Support\Dokument;
use Tests\TestCase;

/**
 * Die Inhaltsmodule, direkt gezeichnet. Bis 29.10.2026 liefen diese Tests
 * über die interne Vorschau /module-demo, die vor dem Go-Live entfernt wurde;
 * die Fusszeile wird seitdem auf echten Seiten geprüft.
 */
class ModuleTest extends TestCase
{
    public function test_hilfe_box_zeigt_anrufbare_notfallnummern(): void
    {
        $r = $this->blade('<x-blocks.hilfe-box />');

        // tel:-Links, damit mobil ein Tipp genügt — dafür existiert die Box.
        foreach (config('hilfe.nummern') as $n) {
            $r->assertSee('tel:'.$n['tel'], false);
            $r->assertSee($n['name']);
        }
        $r->assertSee('tel:110', false);
        $r->assertSee('tel:112', false);
    }

    /** KEV-46: Kurzfassung in zwei Spalten, so wie Taddi sie aufgeteilt hat. */
    public function test_kurzfassung_der_hilfe_box_hat_zwei_spalten(): void
    {
        $kurz = (string) $this->blade('<x-blocks.hilfe-box titel="Sofort jemanden erreichen" :kompakt="true" />');

        $this->assertStringContainsString('md:grid-cols-2', $kurz);
        $this->assertMatchesRegularExpression(
            '/116 016.*0800 123 99 00.*<\/ul>.*116 123.*116 006.*tel:110.*tel:112/s', $kurz);
        // Die übrigen Nummern stehen nur in der ausführlichen Fassung.
        $this->assertStringNotContainsString('116 111', $kurz);
    }

    public function test_inhaltshinweis_und_leichte_sprache_brauchen_kein_javascript(): void
    {
        // Natives <details>: funktioniert ohne JS, Screenreader kennen das Muster.
        $html = $this->blade('<x-blocks.inhalts-hinweis thema="Gewalt und Traumafolgen"><p>Text</p></x-blocks.inhalts-hinweis>')
            .$this->blade('<x-blocks.leichte-sprache><p>Wir sind ein Verein.</p></x-blocks.leichte-sprache>');

        $this->assertStringContainsString('<details', $html);
        $this->assertStringContainsString('Hinweis zum Inhalt', $html);
        $this->assertStringContainsString('Leichter Sprache', $html);
    }

    public function test_downloads_zeigen_titel_statt_dateiname_und_nennen_groesse(): void
    {
        // Die Dokumente der Seite Erwerbsminderungsrente, wie der AltseiteSeeder sie anlegt.
        $manifest = json_decode(file_get_contents(base_path('docs/dokumente-manifest.json')), true);
        $dokumente = collect($manifest)
            ->filter(fn ($d) => in_array('/erwerbsminderungsrente/', $d['verlinkt_auf']))
            ->map(fn ($d) => ['titel' => $d['titel'], 'url' => Dokument::pfad($d['alt_url']), 'bytes' => Dokument::groesse($d['alt_url']) ?? $d['bytes']])
            ->values()->all();

        $html = (string) $this->blade('<x-blocks.download-list titel="Erwerbsminderungsrente" :dokumente="$dokumente" />', ['dokumente' => $dokumente]);

        // Lesbarer Titel aus dem Linktext der Altseite ...
        $this->assertStringContainsString('Infoblatt', $html);

        // ... und kein roher Dateiname im *sichtbaren* Text (WCAG 2.4.4).
        // Im href darf er selbstverständlich stehen — geprüft wird der Textknoten.
        $sichtbar = strip_tags($html);
        $this->assertStringNotContainsString('6.5.3.1.-Info-Erwerbsminderung', $sichtbar);
        $this->assertStringNotContainsString('.pdf', $sichtbar);
        // Dateityp und Größe vorab (WCAG 3.2.5)
        $this->assertMatchesRegularExpression('/PDF-Datei, [\d.,]+ KB/', $html);
    }

    public function test_dokumenten_manifest_ist_vollstaendig(): void
    {
        $manifest = json_decode(file_get_contents(base_path('docs/dokumente-manifest.json')), true);

        // Vollbestand der WP-Medienbibliothek vom 26.07.2026 (121), plus die
        // Teilnahmevereinbarung, die der Verein am 27.07. hochgeladen und auf
        // /veranstaltungen verlinkt hat (nachgetragen 20.09.2026).
        $this->assertCount(122, $manifest);

        $verlinkt = array_filter($manifest, fn ($d) => ! empty($d['verlinkt_auf']));
        $this->assertCount(32, $verlinkt, 'Auf Seiten verlinkte Dokumente');

        foreach ($manifest as $dok) {
            $this->assertNotEmpty($dok['titel']);
            $this->assertStringStartsWith('/wp-content/uploads/', $dok['alt_url']);
            $this->assertGreaterThan(0, $dok['bytes']);
        }

        // Für die verlinkten muss der lesbare Linktext gewonnen worden sein —
        // ein roher Dateiname wäre als Linktext unbrauchbar.
        foreach ($verlinkt as $dok) {
            $this->assertSame('linktext', $dok['titel_quelle']);
            $this->assertNotEquals(basename($dok['datei']), $dok['titel']);
        }
    }

    /**
     * KEV-42: Das Hinweisfenster verspricht „Die Notfallnummern stehen am Ende
     * jeder Seite“. Also stehen sie in der Fusszeile, auf jeder Seite.
     *
     * KEV-123: Dieselben Nummern wie in der Box der Startseite, in derselben
     * Reihenfolge, aber ohne Polizei und Feuerwehr.
     */
    public function test_notfallnummern_stehen_in_der_fusszeile_jeder_seite(): void
    {
        foreach (['/suche', '/gibt-es-nicht'] as $pfad) {
            $html = $this->get($pfad)->getContent();
            $fuss = substr($html, strpos($html, '<footer'));

            $this->assertMatchesRegularExpression(
                '/tel:\+49116016.*tel:\+498001239900.*<\/ul>.*tel:\+49116123.*tel:\+49116006/s', $fuss, $pfad);
            $this->assertStringNotContainsString('tel:110', $fuss, $pfad);
            $this->assertStringNotContainsString('tel:112', $fuss, $pfad);
        }
    }

    /**
     * KEV-122: Unter dem Vereinsnamen das Motto in der Handschrift, jeder Satz
     * für sich, damit es nur zwischen den Sätzen umbricht. Auf Englisch übersetzt.
     */
    public function test_fusszeile_zeigt_das_motto_unter_dem_namen(): void
    {
        $html = $this->get('/suche')->getContent();
        $fuss = substr($html, strpos($html, '<footer'));

        $this->assertMatchesRegularExpression(
            '/KE!N EINZELFALL e\.V\.<\/span>\s*.*?<p class="font-hand[^"]*">\s*'
            .'<span class="inline-block">Für Sichtbarkeit\.<\/span>\s*'
            .'<span class="inline-block">Für eine Stimme\.<\/span>\s*'
            .'<span class="inline-block">Für Gehör\.<\/span>/s', $fuss);

        app()->setLocale('en');
        $this->assertSame('For visibility. For a voice. For being heard.',
            __('Für Sichtbarkeit. Für eine Stimme. Für Gehör.'));
    }

    /**
     * KEV-124: Unter Kontakt die vier Postfächer statt „Anfragen & Austausch“.
     * Bricht eine Adresse um, dann nach dem @, nie am Bindestrich der Domain.
     */
    public function test_fusszeile_nennt_unter_kontakt_die_postfaecher(): void
    {
        $html = $this->get('/suche')->getContent();
        $fuss = substr($html, strpos($html, '<footer'));
        $fuss = substr($fuss, 0, strpos($fuss, '</footer>'));

        foreach (['kontakt', 'selbsthilfe', 'arbeitsgruppe', 'presse'] as $postfach) {
            $this->assertStringContainsString('href="mailto:'.$postfach.'@kein-einzelfall.de"', $fuss);
            $this->assertStringContainsString(
                $postfach.'@<wbr><span class="whitespace-nowrap">kein-einzelfall.de</span>', $fuss);
        }
        $this->assertStringNotContainsString('href="/anfragen"', $fuss);
        $this->assertStringNotContainsString('Anfragen &amp; Austausch', $fuss);
    }
}
