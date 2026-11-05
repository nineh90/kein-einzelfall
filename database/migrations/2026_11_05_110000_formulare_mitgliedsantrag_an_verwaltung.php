<?php

use App\Models\Page;
use Database\Seeders\FormulareSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * /formulare: Den Mitgliedsantrag an verwaltung@ schicken, die übrigen
 * Formulare weiter an kontakt@ (KEV-250, Taddi). Nur der genau so stehende
 * Satz. Danach die englische Fassung neu, wie bei allen Seiten seit
 * 08.10.2026.
 *
 * Die Datei Vollmacht.pdf hat Taddi im Original korrigiert (letzter Satz
 * „Ich erteile diese Vollmacht …“ statt „… Schweigepflichtentbindung …“);
 * sie ist im selben Commit ausgetauscht, die Adresse bleibt.
 */
return new class extends Migration
{
    public function up(): void
    {
        FormulareSeeder::schickenAktualisieren();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
