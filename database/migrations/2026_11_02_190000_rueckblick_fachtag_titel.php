<?php

use App\Models\Page;
use Database\Seeders\RueckblickSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * /rueckblick, Abschnitt aus-unserer-sicht: Die Dokumentenliste heißt
 * „Präsentationen vom Fachtag von aus-unserer-sicht e.V. am 30.11.2024“
 * (KEV-322). Nur wo noch der alte Titel steht. Danach die englische Fassung
 * neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        Page::where('slug', 'rueckblick')->where('locale', 'de')->first()
            ?->blocks()->where('typ', 'download_list')->get()
            ->filter(fn ($b) => ($b->data['titel'] ?? null) === RueckblickSeeder::AUS_DOKUMENTE_TITEL_BIS_KEV_322)
            ->each(fn ($b) => $b->update(['data' => array_replace($b->data, ['titel' => RueckblickSeeder::AUS_DOKUMENTE_TITEL])]));

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
