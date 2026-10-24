<?php

use App\Models\Page;
use Database\Seeders\TeamUndGruppenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * /arbeitsgruppen wie /selbsthilfegruppen (KEV-234): Einleitung mit den
 * Dokumenten daneben, darunter die Karten aller AGs. Die Abschnitte
 * „AG 02 – …“ der Altseite fallen weg, sie standen doppelt zu den Karten.
 * Danach die englische Fassung (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        TeamUndGruppenSeeder::gruppenseitenVervollstaendigen();

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
