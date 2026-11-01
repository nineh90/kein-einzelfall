<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * /istanbul-konvention: Unterzeile „Schutz vor Gewalt ist kein Privileg“ unter dem Titel (KEV-301). Titelbilder::setzen()
 * schreibt sie nur, wo noch keine steht, auf Englisch aus dem Wörterbuch.
 * Danach die englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        Titelbilder::setzen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
