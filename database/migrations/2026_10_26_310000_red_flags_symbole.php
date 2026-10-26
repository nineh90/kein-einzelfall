<?php

use App\Models\Page;
use Database\Seeders\SchutzkonzeptSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Red Flags: rechts neben dem Text die rote Flagge & das grüne Häkchen
 * (KEV-208), siehe SchutzkonzeptSeeder::nachtragen(). Danach die englische
 * Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        SchutzkonzeptSeeder::nachtragen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
