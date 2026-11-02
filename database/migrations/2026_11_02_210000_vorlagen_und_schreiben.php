<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Database\Seeders\VorlagenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * „Vorlagen & Schreiben“ unter Wissen (KEV-323/324): Übersicht und zwölf
 * Kategorien mit Taddis Musterschreiben, siehe VorlagenSeeder. Auf /wissen
 * kommt der Knopf dazu, nur wo die Knöpfe noch wie bis dahin stehen. Danach
 * die englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        VorlagenSeeder::anlegen();

        Page::where('slug', 'wissen')->where('locale', 'de')->first()
            ?->blocks()->where('typ', 'seitenkarten')->get()
            ->filter(fn ($b) => ($b->data['seiten'] ?? null) === AltseiteSeeder::WISSEN_KNOEPFE_BIS_KEV_324)
            ->each(fn ($b) => $b->update(['data' => array_replace($b->data, ['seiten' => AltseiteSeeder::WISSEN_KNOEPFE['seiten']])]));

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
