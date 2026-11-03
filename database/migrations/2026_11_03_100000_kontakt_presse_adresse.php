<?php

use App\Models\Page;
use Database\Seeders\KontaktSeiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * /kontakt: presse@ statt pressekontakt@ in der Sachgebietsaufteilung
 * (KEV-267, von Taddi bestätigt). Nur die genau so stehende Zeile. Danach
 * die englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        KontaktSeiteSeeder::aktualisieren();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
