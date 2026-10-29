<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Mitgliedschaft: In der Dokumentenliste hieß das PDF „Hilfe zum Ausfüllen“,
 * der Abschnitt darüber und /formulare sagen „Ausfüllhilfe zum
 * Mitgliedsantrag“ (Nils' Review zu KEV-203). Nur wo der alte Name noch
 * steht. Danach die englische Fassung neu, wie bei allen Seiten seit
 * 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', 'mitgliedschaft')->where('locale', 'de')->where('fassung', Page::FASSUNG_STANDARD)->first();

        foreach ($seite?->blocks()->where('typ', 'download_list')->get() ?? [] as $block) {
            $data = $block->data;
            foreach ($data['dokumente'] ?? [] as $i => $dokument) {
                if (($dokument['titel'] ?? null) === 'Hilfe zum Ausfüllen') {
                    $data['dokumente'][$i]['titel'] = 'Ausfüllhilfe zum Mitgliedsantrag';
                }
            }
            $block->update(['data' => $data]);
        }

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
