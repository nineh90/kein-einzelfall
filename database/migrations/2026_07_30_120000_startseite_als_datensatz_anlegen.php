<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Legt die Startseite als Datensatz an, falls sie fehlt.
 *
 * Warum eine Migration und nicht nur ein Seeder: Seeder laufen bei uns genau
 * einmal, nämlich wenn die Datenbank leer ist (`bin/start`) — und auf dem
 * Server überhaupt nicht, dort läuft nur `migrate`. Jede bestehende Datenbank
 * hatte die Startseite deshalb nicht, und `/` lieferte ein 404. Genau das ist
 * nach dem Umbau auf allen bereits eingerichteten Rechnern passiert.
 *
 * Inhalte gehören normalerweise nicht in eine Migration. Hier schon: Die
 * Startseite ist keine Beispieldatei, sondern die Adresse der Website. Eine
 * Anwendung, die nach `migrate` auf ihrer eigenen Wurzel ein 404 wirft, ist
 * nicht startklar. Die Texte selbst stehen weiterhin nur an einer Stelle, im
 * `StartseiteSeeder` — diese Migration ruft ihn bloss auf.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Page::where('slug', Page::STARTSEITE_SLUG)->exists()) {
            return;
        }

        Artisan::call('db:seed', [
            '--class' => StartseiteSeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        /*
         * Bewusst nichts.
         *
         * Ein Rücklauf würde eine Seite löschen, an der der Verein
         * zwischenzeitlich gearbeitet hat — im Panel wiederherstellen liesse
         * sie sich nicht. Eine übrig gebliebene Seite ist der harmlosere
         * Zustand von beiden.
         */
    }
};
