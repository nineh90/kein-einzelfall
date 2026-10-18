<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, „Was wir gemeinsam bewegen“: Auf der Karte „Spenden“ ist der
 * letzte Satz nicht mehr fett, Wunsch von Taddi (KEV-116). Der erste bleibt
 * es.
 *
 * Nur wo noch der Text mit beiden fetten Sätzen steht. Danach die englische
 * Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = '*Deine Spende macht unsere Arbeit möglich.* Sie hilft uns, Informationen und '
        .'Wissen kostenfrei zugänglich zu machen, Selbsthilfe und Austausch zu ermöglichen und Projekte '
        .'umzusetzen, die Betroffenen eine Stimme geben. Danke, dass Du dabei bist. *Jeder Beitrag hilft '
        .'uns, unabhängig zu arbeiten und gemeinsam etwas zu bewegen.*';

    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'quick_access')->get() as $block) {
                $data = $block->data;
                $geaendert = false;

                foreach ($data['karten'] ?? [] as $i => $karte) {
                    if (($karte['url'] ?? null) === '/spenden' && ($karte['text'] ?? null) === self::ALT) {
                        $data['karten'][$i]['text'] = StartseiteSeeder::SPENDEN_KARTE_TEXT;
                        $geaendert = true;
                    }
                }

                if ($geaendert) {
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
