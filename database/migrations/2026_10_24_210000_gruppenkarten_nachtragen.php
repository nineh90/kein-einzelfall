<?php

use App\Models\Page;
use Database\Seeders\TeamUndGruppenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Die Gruppenkarten auf /selbsthilfegruppen und /arbeitsgruppen (KEV-226).
 *
 * Sie kamen bisher nur über den Seeder, der auf dem Server nie lief: Auf der
 * Demo fehlten beide Listen, Taddi sah keine Gruppe („wo sind die
 * Selbsthilfegruppen?“, und KEV-234). Dazu auf /selbsthilfegruppen die
 * Dokumente neben der Einleitung, darunter die Gruppen. Danach die englische
 * Fassung (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        TeamUndGruppenSeeder::gruppenseitenVervollstaendigen();

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
