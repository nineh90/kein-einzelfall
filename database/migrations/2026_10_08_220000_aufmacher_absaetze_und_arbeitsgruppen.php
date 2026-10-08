<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, Aufmacher (KEV-86): Taddis Text mit einem Satz je Zeile, dazu
 * der Knopf „Arbeitsgruppen“ neben „Selbsthilfegruppen“. Der Text nur, wo er
 * noch in einem Stück steht, der Knopf nur, wo noch die zwei alten stehen.
 * Danach die englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        $alt = str_replace("\n", ' ', StartseiteSeeder::AUFMACHER_TEXT);
        $alteKnoepfe = array_slice(StartseiteSeeder::AUFMACHER_KNOEPFE, 0, 2);

        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'hero')->get() as $block) {
                $data = $block->data ?? [];

                if (($data['text'] ?? null) === $alt) {
                    $data['text'] = StartseiteSeeder::AUFMACHER_TEXT;
                }
                if (($data['ctas'] ?? null) === $alteKnoepfe) {
                    $data['ctas'] = StartseiteSeeder::AUFMACHER_KNOEPFE;
                }

                if ($data !== $block->data) {
                    $block->update(['data' => $data]);
                }
            }
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
