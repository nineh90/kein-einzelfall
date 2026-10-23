<?php

use App\Models\Frist;
use App\Models\Page;
use Database\Seeders\FristenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * „Fristen und Möglichkeiten“ unter „Wissen“ mit Taddis 19 Fristen (KEV-152).
 * Legt nur an, was fehlt. Danach die englische Fassung, wie bei allen Seiten
 * seit 08.10.2026 (nur auf einer eingerichteten Datenbank, siehe
 * englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        FristenSeeder::anlegen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        Page::where('slug', FristenSeeder::SLUG)->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->delete();
        });

        Frist::query()->delete();
    }
};
