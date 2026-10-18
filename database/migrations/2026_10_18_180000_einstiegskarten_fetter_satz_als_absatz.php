<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, „Was wir gemeinsam bewegen“: Der fette Satz am Anfang jeder
 * Karte steht als eigener Absatz (KEV-112, Wunsch von Taddi). Bei
 * „Arbeitsgruppen“ und „Anfragen & Austausch“ war das seit KEV-114/115 so,
 * jetzt auch bei „Selbsthilfegruppen“ und „Spenden“.
 *
 * Unabhängig vom Wortlaut: Wo eine Karte mit einem *fetten Satz* beginnt und
 * der Rest in derselben Zeile folgt, kommt dazwischen ein Zeilenumbruch, und
 * der wird auf der Karte ein Absatz. Karten mit Absatz oder ohne fetten
 * Anfang bleiben, wie sie sind. Danach die englische Fassung neu, wie bei
 * allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'quick_access')->get() as $block) {
                $data = $block->data;

                foreach ($data['karten'] ?? [] as $i => $karte) {
                    if (is_string($karte['text'] ?? null)) {
                        $data['karten'][$i]['text'] = preg_replace('/^(\*[^*\n]+\*)[ \t]+(?=\S)/u', "$1\n", $karte['text']);
                    }
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
