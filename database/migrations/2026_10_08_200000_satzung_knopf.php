<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Satzung (KEV-89): Knopf zum PDF im Abschnitt „Satzung lesen“. Taddi hielt
 * die Überschrift für einen Link, der nicht ging. Nur, wo noch kein Knopf
 * ist. Danach die englische Fassung neu, wie bei allen Seiten seit
 * 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Page::where('slug', 'satzung')->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                $knopf = AltseiteSeeder::NEUE_KNOEPFE['satzung'][$block->data['titel'] ?? ''] ?? null;

                if ($knopf && empty($block->data['cta'])) {
                    $block->update(['data' => array_replace($block->data, ['cta' => $knopf])]);
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
