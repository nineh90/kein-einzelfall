<?php

use App\Models\Page;
use Database\Seeders\AntraegeUndFormulareSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Legt die fünf Seiten aus Abschnitt 6.2 des Strukturpapiers an: OEG, SER,
 * GdB, Pflegegrad, Persönliches Budget.
 *
 * Warum eine Migration: Seeder laufen bei uns nur bei leerer Datenbank und auf
 * dem Server gar nicht. Dasselbe Muster wie bei den vier Bereichen aus
 * `neue_bereiche_als_entwurf_anlegen`.
 *
 * Der Seeder schreibt mit `updateOrCreate` und würde Arbeit des Vereins
 * überschreiben. Deshalb läuft er hier nur, wenn noch keine der fünf Seiten
 * existiert — danach gehören sie dem Verein.
 */
return new class extends Migration
{
    public function up(): void
    {
        $slugs = array_column(AntraegeUndFormulareSeeder::seiten(), 'slug');

        if (Page::whereIn('slug', $slugs)->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', [
            '--class' => AntraegeUndFormulareSeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
