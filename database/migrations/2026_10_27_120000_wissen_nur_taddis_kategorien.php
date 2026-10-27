<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Wissen nach Taddis Rückmeldung (KEV-239, KEV-241, KEV-242):
 *
 *  - Das Menü zeigt nur noch Taddis Kategorien (config/navigation.php,
 *    braucht hier nichts).
 *  - Der Kasten „Urteile zum Schwerbehindertenrecht“ (REHADAT, Migration vom
 *    22.09.2026) kommt von /wissen weg. Auf frischen Datenbanken gab es ihn
 *    nie, auf der Demo stand er noch unter dem Text.
 *  - Taddis Kategorien als Knöpfe rechts neben dem Text.
 *
 * Danach die englische Fassung neu (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        $gruppe = Page::where('slug', 'wissen')->where('locale', 'de')->value('uebersetzungs_gruppe');

        if ($gruppe) {
            Page::where('uebersetzungs_gruppe', $gruppe)->get()->each(fn (Page $seite) => $seite->blocks()
                ->where('typ', 'hinweis')
                ->get()
                ->filter(fn ($block) => str_contains($block->data['link']['url'] ?? '', 'rehadat-recht.de'))
                ->each->delete());
        }

        AltseiteSeeder::wissenKnoepfeDaneben();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
