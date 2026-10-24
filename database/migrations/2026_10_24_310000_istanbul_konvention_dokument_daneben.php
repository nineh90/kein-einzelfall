<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Istanbul-Konvention: das Dokument rechts neben dem Text, wie beim
 * Kinderkodex (KEV-217, gleicher Aufbau, Kevin: „mach ruhig mit“). Danach
 * die englische Fassung (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        AltseiteSeeder::dokumenteNebenText();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
