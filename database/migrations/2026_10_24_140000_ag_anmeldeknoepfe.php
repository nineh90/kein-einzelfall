<?php

use App\Models\Page;
use Database\Seeders\TeamUndGruppenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Auf /arbeitsgruppen bekommt jeder Abschnitt „AG 01 – …“ wieder seinen
 * Knopf „Anmelden (AG 01)“, der eine Mail an arbeitsgruppe@ öffnet, wie auf
 * der Altseite (KEV-233). Nur wo noch kein Knopf ist; danach die englische
 * Fassung (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Page::where('slug', 'arbeitsgruppen')->where('locale', 'de')->get() as $seite) {
            TeamUndGruppenSeeder::agAnmeldeknoepfeNachtragen($seite);
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
