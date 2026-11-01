<?php

use App\Models\Page;
use Database\Seeders\TeamseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Teamseite: „Auf dieser Seite“ rechts neben der Einleitung, neue
 * Überschrift „WIR – mit Herz, Haltung und Vision“ und ein Schlusssatz
 * (KEV-294), siehe TeamseiteSeeder. Danach die englische Fassung neu, wie
 * bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        TeamseiteSeeder::fuellen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
