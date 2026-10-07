<?php

use Database\Seeders\GremiumUkfbSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Gremium UKFB (KEV-105): Taddis Text, Unterzeile, veröffentlicht.
 * Füllt nur den leeren Entwurf.
 */
return new class extends Migration
{
    public function up(): void
    {
        GremiumUkfbSeeder::fuellen();
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
