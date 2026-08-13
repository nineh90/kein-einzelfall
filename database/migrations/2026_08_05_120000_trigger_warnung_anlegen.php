<?php

use App\Models\Page;
use Database\Seeders\TriggerWarnungSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Legt die Seite der Trigger-Warnung an, falls sie fehlt.
 *
 * Warum eine Migration und nicht nur ein Seeder: Seeder laufen bei uns genau
 * einmal, nämlich bei leerer Datenbank — und auf dem Server überhaupt nicht,
 * dort läuft ausschliesslich `migrate`. Ein neuer Seeder erreicht also keine
 * einzige bestehende Installation. Dasselbe Muster wie bei der Startseite;
 * die Begründung im Langen steht dort.
 *
 * Der Text steht weiterhin nur an einer Stelle, im TriggerWarnungSeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Page::where('slug', Page::TRIGGER_SLUG)->exists()) {
            return;
        }

        Artisan::call('db:seed', [
            '--class' => TriggerWarnungSeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        /*
         * Bewusst nichts — wie bei der Startseite. Ein Rücklauf löschte eine
         * Seite, an der der Verein zwischenzeitlich gearbeitet hat.
         */
    }
};
