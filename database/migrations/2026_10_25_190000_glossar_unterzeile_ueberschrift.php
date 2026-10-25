<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\GlossarSozialrechtSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Glossar („Abkürzungen Sozialrecht“, KEV-255): Unterzeile „Damit aus
 * Buchstaben wieder Bedeutung wird.“ und die neue grüne Überschrift über der
 * Einleitung. Die Überschrift nur, wo noch die bisherige steht, die
 * Unterzeile nur, wo keine steht. Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', GlossarSozialrechtSeeder::SLUG)->where('locale', 'de')->first();

        if (! $seite) {
            return;
        }

        foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
            if (($block->data['titel'] ?? null) === GlossarSozialrechtSeeder::UEBERSCHRIFT_BIS_25_10) {
                $block->update(['data' => array_replace($block->data, ['titel' => GlossarSozialrechtSeeder::UEBERSCHRIFT])]);
            }
        }

        Titelbilder::setzen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
