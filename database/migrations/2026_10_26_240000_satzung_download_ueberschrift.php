<?php

use App\Models\Page;
use Database\Seeders\SatzungsseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Satzung: „Unsere Satzung zum Downloaden“ über dem PDF (KEV-195), siehe
 * SatzungsseiteSeeder. Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        SatzungsseiteSeeder::fuellen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
