<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * KEV-281 (Nils): Auf der Startseite alle Termine des Vereins als Kalender,
 * vor dem Hilfe-Kasten. Danach die englische Fassung (nur auf einer
 * eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        StartseiteSeeder::kalenderEinfuegen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
