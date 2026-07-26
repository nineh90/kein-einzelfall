<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModuleTest extends TestCase
{
    public function test_hilfe_box_zeigt_anrufbare_notfallnummern(): void
    {
        $r = $this->get('/module-demo');

        $r->assertOk();
        // tel:-Links, damit mobil ein Tipp genügt — dafür existiert die Box.
        foreach (config('hilfe.nummern') as $n) {
            $r->assertSee('tel:'.$n['tel'], false);
            $r->assertSee($n['name']);
        }
        $r->assertSee('tel:110', false);
    }

    public function test_inhaltshinweis_und_leichte_sprache_brauchen_kein_javascript(): void
    {
        // Natives <details>: funktioniert ohne JS, Screenreader kennen das Muster.
        $html = $this->get('/module-demo')->getContent();

        $this->assertStringContainsString('<details', $html);
        $this->assertStringContainsString('Hinweis zum Inhalt', $html);
        $this->assertStringContainsString('Leichter Sprache', $html);
    }

    public function test_downloads_zeigen_titel_statt_dateiname_und_nennen_groesse(): void
    {
        $html = $this->get('/module-demo')->getContent();

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

        // Vollbestand der WP-Medienbibliothek, nicht nur die verlinkten.
        $this->assertCount(121, $manifest);

        $verlinkt = array_filter($manifest, fn ($d) => ! empty($d['verlinkt_auf']));
        $this->assertCount(31, $verlinkt, 'Auf Seiten verlinkte Dokumente');

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
}
