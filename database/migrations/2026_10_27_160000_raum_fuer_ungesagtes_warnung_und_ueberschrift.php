<?php

use App\Models\Page;
use Database\Seeders\RaumFuerUngesagtesSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Raum für Ungesagtes (KEV-263, KEV-264): Taddis neue Triggerwarnung und
 * „Für alles, was zu lange keinen Platz hatte“ als grüne Überschrift.
 * Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        RaumFuerUngesagtesSeeder::nachtragen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
