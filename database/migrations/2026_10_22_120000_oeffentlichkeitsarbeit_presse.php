<?php

use App\Models\Page;
use Database\Seeders\OeffentlichkeitsarbeitSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Öffentlichkeitsarbeit: Abschnitt „Presse“ mit dem stern-Artikel und dem
 * Besuch bei Trude Kuh (KEV-136). Beides nur, wenn es fehlt. Vorher stellt
 * sie den Abschnitt „Messen“ von `art` auf `arten` um. Danach die
 * englische Fassung, wie bei allen Seiten seit 08.10.2026 (nur auf einer
 * eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        OeffentlichkeitsarbeitSeeder::artenUmstellen();
        OeffentlichkeitsarbeitSeeder::abschnittAnhaengen(OeffentlichkeitsarbeitSeeder::PRESSE);
        OeffentlichkeitsarbeitSeeder::eintragAnlegen(OeffentlichkeitsarbeitSeeder::PRESSE_STERN);
        OeffentlichkeitsarbeitSeeder::eintragAnlegen(OeffentlichkeitsarbeitSeeder::PRESSE_TRUDE_KUH);

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
