<?php

use App\Models\Page;
use Database\Seeders\RaumFuerUngesagtesSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * „Blickwinkel“ unter „Raum für Ungesagtes“ (KEV-158). Der Seeder legt nur an,
 * was fehlt; Bereich und Unüberhörbar bleiben, wie sie sind. Danach die
 * englische Fassung, wie bei allen Seiten seit 08.10.2026 (nur auf einer
 * eingerichteten Datenbank, siehe englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        RaumFuerUngesagtesSeeder::anlegen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        Page::where('slug', RaumFuerUngesagtesSeeder::BLICKWINKEL_SLUG)->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->delete();
        });
    }
};
