<?php

use Database\Seeders\AlteSeitenZurueckziehenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Alte Seiten unter „Wissen“ und „KE!N EINZELFALL im Dialog“ offline, mit
 * Weiterleitung (KEV-246). Was und warum: AlteSeitenZurueckziehenSeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        AlteSeitenZurueckziehenSeeder::zurueckziehen();
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
