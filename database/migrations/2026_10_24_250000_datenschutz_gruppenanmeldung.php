<?php

use App\Models\Page;
use Database\Seeders\DatenschutzSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Datenschutzerklärung: Abschnitt „Anmeldung zu Selbsthilfegruppen“
 * (KEV-226), hinter „Kontaktformular“. Danach die englische Fassung (nur auf
 * einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Page::where('slug', DatenschutzSeeder::SLUG)->where('locale', 'de')->get() as $seite) {
            DatenschutzSeeder::anmeldungEinfuegen($seite);
        }

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
