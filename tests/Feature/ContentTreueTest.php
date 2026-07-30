<?php

namespace Tests\Feature;

use Database\Seeders\StartseiteSeeder;
use Tests\TestCase;

/**
 * Vertraglich gilt: Texte und Bilder stellt der Verein, wir pflegen sie nur ein.
 * Diese Tests halten fest, dass die Startseite den Bestand von kein-einzelfall.de
 * wiedergibt und keine von uns erfundenen Formulierungen enthält.
 *
 * Beim ersten Bau war genau das passiert — die Texte stammten aus dem Design-Mockup
 * statt von der echten Seite.
 */
class ContentTreueTest extends TestCase
{
    /** Wörtlich von https://kein-einzelfall.de/ (Stand 26.07.2026). */
    private const ORIGINAL = [
        'Keiner soll mehr sagen müssen',
        'Wir schaffen eine Austausch',
        'Ein zentrales Netzwerk aus Expertise im Betroffenenkontext',
        'Opferhilfe für soziale Gerechtigkeit',
        'Der KE!N EINZELFALL e.V. wurde 2024 gegründet',
        'Jede Mitgliedschaft stärkt unsere Arbeit',
        'Der Austausch in unseren Selbsthilfegruppen',
        'Um unsere Arbeit weiter zu professionalisieren',
        'Mit Deiner Spende hilfst Du uns',
        'Sei Du dabei, jede Unterstützung zählt',
    ];

    /**
     * Formulierungen aus dem Design-Mockup. Das Mockup gibt das Aussehen vor,
     * nicht den Inhalt — diese Sätze stehen nirgends auf der echten Seite.
     */
    private const NICHT_ERFINDEN = [
        'Ein Ort zum Ankommen',
        'Gemeinsam wachsen wir weiter',
        '1.000+',
        'erreichte Menschen',
        'Vier Wege',
        'Melde dich — in deinem Tempo',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Die Startseite ist ein Datensatz. Ohne ihn liefert `/` ein 404 und
        // dieser Test prüfte die Fehlerseite statt der Startseite.
        $this->seed(StartseiteSeeder::class);
    }

    public function test_startseite_gibt_den_bestand_wieder(): void
    {
        $html = html_entity_decode($this->get('/')->getContent());

        foreach (self::ORIGINAL as $satz) {
            $this->assertStringContainsString($satz, $html, "Originaltext fehlt: {$satz}");
        }
    }

    public function test_startseite_enthaelt_keine_erfundenen_texte(): void
    {
        $html = html_entity_decode($this->get('/')->getContent());

        foreach (self::NICHT_ERFINDEN as $satz) {
            $this->assertStringNotContainsString(
                $satz,
                $html,
                "Mockup-Text statt Vereinstext auf der Seite: {$satz}"
            );
        }
    }

    public function test_kaputte_links_der_altseite_sind_repariert(): void
    {
        // Auf der Altseite zeigen die Einstiegskarten auf /selbsthilfegruppen-2/
        // und /kontaktformular/ — beide liefern dort 404.
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('/selbsthilfegruppen-2', $html);
        $this->assertStringNotContainsString('/kontaktformular', $html);
        $this->assertStringContainsString('/selbsthilfegruppen', $html);
        $this->assertStringContainsString('/anfragen', $html);
    }
}
