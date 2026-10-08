<?php

use App\Models\Page;
use Database\Seeders\SchutzkonzeptSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Schutz- und Wertekonzept und Red Flags unter Verein (KEV-97), mit Taddis
 * Text und Platzhalterbild. Füllt den leeren Entwurf „Schutzkonzept“, legt
 * Red Flags an. Danach die englische Fassung, wie bei allen Seiten seit
 * 08.10.2026 (nur auf einer eingerichteten Datenbank, siehe
 * englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        SchutzkonzeptSeeder::anlegen();

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
