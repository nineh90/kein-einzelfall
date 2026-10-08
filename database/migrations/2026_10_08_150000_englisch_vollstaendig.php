<?php

use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Englische Fassung der ganzen Website (08.10.2026): alle Seiten, Glossar,
 * Gruppen, Team. Maschinell übersetzt, sichtbar als ungeprüft gekennzeichnet
 * und für Suchmaschinen gesperrt. Seiten, deren Haken „ungeprüft“ der Verein
 * entfernt hat, bleiben unberührt. Siehe UebersetzungenSeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tippfehler im deutschen Text der AG 06, beim Übersetzen aufgefallen.
        DB::table('groups')->where('beschreibung', 'like', '%Volltexturteilen%')
            ->update(['beschreibung' => DB::raw("REPLACE(beschreibung, 'Volltexturteilen', 'Volltext-Urteilen')")]);

        // Nur auf einer eingerichteten Datenbank (Demo, Server, lokal). Auf
        // einer frischen kommen die deutschen Inhalte erst nach den
        // Migrationen; dort legt bin/start die Fassung danach an.
        if (! \App\Models\Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
