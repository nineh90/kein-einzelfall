<?php

use App\Models\Page;
use Database\Seeders\GruppenUndVeranstaltungenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * KEV-224: Termin-Kalender rechts neben der Einleitung von
 * /gruppen-und-veranstaltungen. Danach die englische Fassung (nur auf einer
 * eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        GruppenUndVeranstaltungenSeeder::kalenderNebenEinleitung();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        Page::where('slug', 'gruppen-und-veranstaltungen')->get()
            ->each(fn (Page $seite) => $seite->blocks()->where('typ', 'kalender')->delete());
    }
};
