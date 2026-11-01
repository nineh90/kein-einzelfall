<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Teamseite: Unterzeile auf dem Titelbild „Die Menschen hinter KE!N
 * EINZELFALL e.V.“ statt „Die Menschen hinter unserer Arbeit.“ (KEV-296).
 * Nur wo noch die alte steht. Danach die englische Fassung neu, wie bei
 * allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = 'Die Menschen hinter unserer Arbeit.';

    public function up(): void
    {
        Page::where('slug', 'ueber-uns-vorstand-und-team')->where('locale', 'de')
            ->where('untertitel', self::ALT)
            ->update(['untertitel' => Titelbilder::SEITEN['ueber-uns-vorstand-und-team']]);

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
