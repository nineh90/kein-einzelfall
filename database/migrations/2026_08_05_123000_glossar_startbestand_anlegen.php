<?php

use App\Models\GlossaryTerm;
use Database\Seeders\GlossarSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Legt den Startbestand des Glossars an, falls die Tabelle noch leer ist.
 *
 * Warum eine Migration: Seeder laufen bei uns nur bei leerer Datenbank und auf
 * dem Server gar nicht. Dasselbe Muster wie bei der Startseite.
 *
 * Die Prüfung auf „leer“ statt auf einzelne Begriffe ist Absicht: Sobald der
 * Verein selbst Einträge angelegt hat, ist das Glossar seines — dann hat eine
 * Migration nichts mehr hineinzuschreiben.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (GlossaryTerm::query()->exists()) {
            return;
        }

        Artisan::call('db:seed', [
            '--class' => GlossarSeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
