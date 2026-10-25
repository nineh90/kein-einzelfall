<?php

use App\Support\Titelbilder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Fristen und Möglichkeiten (KEV-251): Unterzeile „Fristen verstehen.
 * Möglichkeiten erkennen. Rechtzeitig handeln.“ unter dem Titel. Setzen()
 * füllt nur leere Unterzeilen; die englische steht im Wörterbuch.
 */
return new class extends Migration
{
    public function up(): void
    {
        Titelbilder::setzen();

        if (\App\Models\Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
