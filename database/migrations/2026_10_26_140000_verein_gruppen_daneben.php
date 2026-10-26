<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Database\Seeders\VereinsseiteSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Vereinsseite: Selbsthilfe- und Arbeitsgruppen als Karten rechts neben dem
 * Text (KEV-174), siehe VereinsseiteSeeder. Danach die englische Fassung
 * neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        VereinsseiteSeeder::fuellen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
