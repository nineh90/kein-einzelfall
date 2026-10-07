<?php

use Database\Seeders\BeschwerdemanagementSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Beschwerdemanagement (KEV-98): Taddis Text in zwei Spalten mit je einem
 * Formular, die Seite wird veröffentlicht. Füllt nur den leeren Entwurf.
 */
return new class extends Migration
{
    public function up(): void
    {
        BeschwerdemanagementSeeder::fuellen();
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
