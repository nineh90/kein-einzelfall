<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\SchutzkonzeptSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Red Flags: Überschrift „Leitfaden für die Zusammenarbeit“, darunter „Red
 * Flags & Green Lights“ (KEV-206, KEV-210). Danach die englische Fassung
 * neu und die Unterzeilen nachgetragen.
 */
return new class extends Migration
{
    public function up(): void
    {
        SchutzkonzeptSeeder::nachtragen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }

        Titelbilder::setzen();
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
