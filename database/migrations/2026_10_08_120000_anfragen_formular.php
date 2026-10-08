<?php

use Database\Seeders\AnfragenSeiteSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * /anfragen: echtes Kontaktformular statt der Feldnamen als Text, Hilfe-Nummern
 * aus config/hilfe.php statt der Krisenliste der Altseite. Begründung im
 * Seeder. Nur wo noch der Stand der Altseite steht.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new AnfragenSeiteSeeder)->run();
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
