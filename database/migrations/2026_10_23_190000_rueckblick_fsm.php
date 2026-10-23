<?php

use App\Models\Page;
use Database\Seeders\RueckblickSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Rückblick: „Unsere Veranstaltungen zum Fonds Sexueller Missbrauch (FSM)“
 * mit Taddis P.S. als Hinweis (KEV-141). Legt nur an, was fehlt. Danach die
 * englische Fassung, wie bei allen Seiten seit 08.10.2026 (nur auf einer
 * eingerichteten Datenbank, siehe englisch_vollstaendig).
 */
return new class extends Migration
{
    public function up(): void
    {
        RueckblickSeeder::anlegen();

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        Page::where('slug', RueckblickSeeder::SLUG)->each(function (Page $seite) {
            $seite->blocks->filter(fn ($b) => ($b->data['bild'] ?? null) === RueckblickSeeder::FSM_BILD
                || ($b->data['titel'] ?? null) === RueckblickSeeder::FSM_PS_TITEL)
                ->each->delete();
        });
    }
};
