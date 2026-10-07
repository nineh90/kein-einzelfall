<?php

use App\Models\Page;
use Database\Seeders\JahresberichteSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Seite „Tätigkeits- und Jahresberichte“ unter Verein (KEV-103), mit Taddis
 * Text und Platzhalterbild. Legt nur an, was fehlt.
 */
return new class extends Migration
{
    public function up(): void
    {
        JahresberichteSeeder::anlegen();
    }

    public function down(): void
    {
        Page::where('slug', JahresberichteSeeder::SLUG)->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->delete();
        });
    }
};
