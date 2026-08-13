<?php

use App\Models\Page;
use Database\Seeders\NeueBereicheSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Legt Schutzkonzept, Beschwerdemanagement, Projekte und Publikationen als
 * Entwurf an — die vier Bereiche aus dem Strukturpapier, die es auf der
 * Altseite noch nicht gibt.
 *
 * Warum eine Migration: Seeder laufen bei uns nur bei leerer Datenbank und auf
 * dem Server gar nicht. Dasselbe Muster wie bei der Startseite.
 *
 * Der Seeder schreibt mit `updateOrCreate` und würde Arbeit des Vereins
 * überschreiben. Deshalb läuft er hier nur, wenn noch keine der vier Seiten
 * existiert — danach gehören sie dem Verein.
 */
return new class extends Migration
{
    public function up(): void
    {
        $slugs = array_column(NeueBereicheSeeder::seiten(), 'slug');

        if (Page::whereIn('slug', $slugs)->exists()) {
            return;
        }

        Artisan::call('db:seed', [
            '--class' => NeueBereicheSeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
