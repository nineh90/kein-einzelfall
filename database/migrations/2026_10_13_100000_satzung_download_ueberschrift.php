<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Satzung (KEV-90): Die Dokumentenliste heisst „Aktuelle Satzung zum
 * Herunterladen“ statt „Dokumente zum Herunterladen“, Wunsch von Taddi. Nur,
 * wo noch der alte Wortlaut steht. Danach die englische Fassung neu, wie bei
 * allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = 'Dokumente zum Herunterladen';

    public function up(): void
    {
        foreach (Page::where('slug', 'satzung')->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'download_list')->get() as $block) {
                if (($block->data['titel'] ?? null) === self::ALT) {
                    $block->update(['data' => array_replace($block->data, [
                        'titel' => AltseiteSeeder::DOKUMENTE_TITEL['satzung'],
                    ])]);
                }
            }
        }

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
