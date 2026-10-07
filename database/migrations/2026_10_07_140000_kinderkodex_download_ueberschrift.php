<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Kinderkodex: Die Dokumentenliste heisst „Kinderkodex herunterladen“ statt
 * „Dokumente zum Herunterladen“, dort liegt nur ein Dokument (KEV-100).
 * Dazu eine neue Beschreibung für Suchmaschinen statt der schiefen der
 * Altseite. Beides nur, wo noch der alte Wortlaut steht.
 */
return new class extends Migration
{
    private const ALT = 'Dokumente zum Herunterladen';

    private const ALTE_BESCHREIBUNG = 'Die KE!N EINZELFALL-Mitglieder und seine Arbeitsgruppen verpflichten '
        .'sich zur Einhaltung des Kinderkodex zum Schutz von Kindern und Jugendlichen.';

    public function up(): void
    {
        $this->tauschen(self::ALT, AltseiteSeeder::DOKUMENTE_TITEL['kinderkodex']);
        $this->beschreibung(self::ALTE_BESCHREIBUNG, AltseiteSeeder::NEUE_BESCHREIBUNGEN['kinderkodex']);
    }

    public function down(): void
    {
        $this->tauschen(AltseiteSeeder::DOKUMENTE_TITEL['kinderkodex'], self::ALT);
        $this->beschreibung(AltseiteSeeder::NEUE_BESCHREIBUNGEN['kinderkodex'], self::ALTE_BESCHREIBUNG);
    }

    private function beschreibung(string $von, string $nach): void
    {
        Page::where('slug', 'kinderkodex')->where('meta_description', $von)->update(['meta_description' => $nach]);
    }

    private function tauschen(string $von, string $nach): void
    {
        foreach (Page::where('slug', 'kinderkodex')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'download_list')->get() as $block) {
                if (($block->data['titel'] ?? null) === $von) {
                    $block->update(['data' => array_replace($block->data, ['titel' => $nach])]);
                }
            }
        }
    }
};
