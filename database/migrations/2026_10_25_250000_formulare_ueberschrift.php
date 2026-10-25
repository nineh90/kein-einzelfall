<?php

use App\Models\Page;
use Database\Seeders\FormulareSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Formulare (KEV-247): grüne Überschrift „Die Formulare des KE!N EINZELFALL
 * e.V.“ über der Einleitung. Nur in den ersten Textabschnitt und nur, wenn er
 * noch keine Überschrift hat. Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', FormulareSeeder::SLUG)->where('locale', 'de')->first();
        $einleitung = $seite?->blocks()->orderBy('position')->first();

        if ($einleitung?->typ === 'text' && blank($einleitung->data['titel'] ?? null)) {
            // Überschrift vor den Absätzen, in der Reihenfolge des Formulars
            $einleitung->update(['data' => ['titel' => FormulareSeeder::EINLEITUNG_TITEL] + $einleitung->data]);
        }

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
