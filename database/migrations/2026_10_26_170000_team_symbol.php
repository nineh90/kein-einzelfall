<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Über uns: rechts neben dem Abschnitt „Team“ ein Symbol mit Menschen im
 * Kreis (KEV-184). Nur wo der Abschnitt noch kein Symbol hat. Danach die
 * englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', 'ueber-uns-vorstand-und-team')->where('locale', 'de')->first();

        foreach ($seite?->blocks()->where('typ', 'text')->get() ?? [] as $block) {
            if (($block->data['titel'] ?? null) === 'Team' && empty($block->data['symbol'])) {
                $block->update(['data' => $block->data + ['symbol' => 'circle-users']]);
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
