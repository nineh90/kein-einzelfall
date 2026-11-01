<?php

use App\Models\Page;
use Database\Seeders\SchutzkonzeptSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Red Flags: statt Flagge & Häkchen der Leitfaden zum Herunterladen rechts
 * neben dem Text (KEV-299), siehe SchutzkonzeptSeeder::leitfadenNebenDenText().
 * Danach die englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        SchutzkonzeptSeeder::leitfadenNebenDenText();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
