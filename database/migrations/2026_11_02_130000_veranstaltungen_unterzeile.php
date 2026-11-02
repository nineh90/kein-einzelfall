<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * /veranstaltungen: Unterzeile „Wissen teilen. Perspektiven erweitern.“
 * (KEV-317) statt der längeren. Nur wo noch die alte steht. Danach die
 * englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = 'Wissen teilen. Erfahrungen verbinden. Gemeinsam Perspektiven schaffen.';

    public function up(): void
    {
        Page::where('slug', 'veranstaltungen')->where('locale', 'de')->where('untertitel', self::ALT)
            ->update(['untertitel' => Titelbilder::SEITEN['veranstaltungen']]);

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
