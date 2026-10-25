<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Formulare (KEV-248): Unterzeile „Übersichtlich an einem Ort“ unter dem
 * Titel. Setzen() füllt nur leere Unterzeilen; die englische steht im
 * Wörterbuch.
 */
return new class extends Migration
{
    public function up(): void
    {
        Titelbilder::setzen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
