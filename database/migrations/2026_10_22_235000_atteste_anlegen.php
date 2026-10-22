<?php

use App\Models\Page;
use Database\Seeders\AttesteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Seite „Atteste“ unter Wissen (KEV-144), mit Taddis Text, Bild und den 27
 * Attestvorlagen der Altseite. Danach die englische Fassung, wie bei allen
 * Seiten seit 08.10.2026 (nur auf einer eingerichteten Datenbank, siehe
 * englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        AttesteSeeder::anlegen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        Page::where('slug', AttesteSeeder::SLUG)->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->delete();
        });
    }
};
