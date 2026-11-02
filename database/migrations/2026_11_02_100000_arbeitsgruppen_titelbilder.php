<?php

use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Die Arbeitsgruppen bekommen Taddis Bilder (KEV-314), Karten und Seiten
 * wie bei den Selbsthilfegruppen. Nur wo noch keins steht, siehe
 * TeamUndGruppenSeeder::AG_TITELBILDER.
 */
return new class extends Migration
{
    public function up(): void
    {
        TeamUndGruppenSeeder::agTitelbilderSetzen();
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
