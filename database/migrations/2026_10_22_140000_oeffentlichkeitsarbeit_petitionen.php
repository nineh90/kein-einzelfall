<?php

use App\Models\Page;
use Database\Seeders\OeffentlichkeitsarbeitSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Öffentlichkeitsarbeit: Abschnitt „Petitionen“ zwischen Messen und Presse,
 * mit der eigenen Petition auf openPetition und der von Tour41 (KEV-135).
 * Nur, was fehlt. Danach die englische Fassung, wie bei allen Seiten seit
 * 08.10.2026 (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        OeffentlichkeitsarbeitSeeder::abschnittEinfuegen(OeffentlichkeitsarbeitSeeder::PETITIONEN, 'Messen');
        OeffentlichkeitsarbeitSeeder::eintragAnlegen(OeffentlichkeitsarbeitSeeder::PETITION_VERSORGUNG);
        OeffentlichkeitsarbeitSeeder::eintragAnlegen(OeffentlichkeitsarbeitSeeder::PETITION_TOUR41);

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
