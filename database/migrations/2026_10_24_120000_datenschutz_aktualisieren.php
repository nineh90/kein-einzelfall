<?php

use App\Models\Page;
use Database\Seeders\DatenschutzSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Datenschutzerklärung: Cookie-Banner der Altseite raus, Cookies,
 * Browser-Speicher, betterplace und Kontaktformular so, wie die Website
 * heute arbeitet (DatenschutzSeeder). Ersetzt nur den unveränderten Text der
 * Altseite; danach die englische Fassung (nur auf einer eingerichteten
 * Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Page::where('slug', DatenschutzSeeder::SLUG)->where('locale', 'de')->get() as $seite) {
            DatenschutzSeeder::umbauen($seite);
        }

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        // Bewusst nichts: Die alten Angaben stimmten nicht.
    }
};
