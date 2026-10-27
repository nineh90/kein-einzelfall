<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Landesstellen (KEV-273): Unterzeile „Gemeinsam stark – an vielen Orten“.
 * Nur, wo noch die alte steht. Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Page::where('slug', 'landesstellen')
            ->where('locale', 'de')
            ->where('untertitel', 'KE!N EINZELFALL ist nicht nur an einem Ort zuhause.')
            ->update(['untertitel' => 'Gemeinsam stark – an vielen Orten']);

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
