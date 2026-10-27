<?php

use App\Models\Page;
use Database\Seeders\KontaktSeiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Kontakt nach Taddis Tickets (KEV-267 bis KEV-272): neue Einleitung statt
 * der Anschrift, kürzere Überschrift, Kassenwartin unter finanzen@,
 * Sachgebiete in ihrer Reihenfolge. Das neue Bild (KEV-266) liegt unter
 * demselben Dateinamen. Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        KontaktSeiteSeeder::aktualisieren();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
