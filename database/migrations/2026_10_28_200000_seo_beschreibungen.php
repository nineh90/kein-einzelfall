<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\KlartextStattFachspracheSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * SEO-Prüfung (28.10.2026): Beschreibungen fürs Suchergebnis, die zu kurz
 * (Verein 56, Mitgliedschaft 25 Zeichen) oder zu lang waren (Klartext 212).
 *
 * Nur wo noch der alte Wortlaut steht; was der Verein im Panel gepflegt hat,
 * bleibt. Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $ersetzen = [];

        foreach (AltseiteSeeder::BESCHREIBUNGEN_BIS_28_10 as $slug => $alt) {
            $ersetzen[$slug] = [$alt, AltseiteSeeder::NEUE_BESCHREIBUNGEN[$slug]];
        }

        $ersetzen[KlartextStattFachspracheSeeder::SLUG] = [
            KlartextStattFachspracheSeeder::META_DESCRIPTION_BIS_28_10,
            KlartextStattFachspracheSeeder::META_DESCRIPTION,
        ];

        foreach ($ersetzen as $slug => [$alt, $neu]) {
            Page::where('slug', $slug)->where('locale', 'de')
                ->where('meta_description', $alt)
                ->update(['meta_description' => $neu]);
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
