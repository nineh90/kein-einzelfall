<?php

use Database\Seeders\LandesstellenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Landesstellen (KEV-104): Taddis Text, Unterzeile, veröffentlicht.
 * Füllt nur den leeren Entwurf.
 */
return new class extends Migration
{
    public function up(): void
    {
        LandesstellenSeeder::fuellen();
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
