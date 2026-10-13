<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, „Was wir gemeinsam bewegen“: neuer Text der Karte
 * „Arbeitsgruppen“ von Taddi, der von ihr markierte Satz fett (KEV-49). Nur
 * wo noch der alte Text steht. Danach die englische Fassung neu, wie bei
 * allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = 'Um unsere Arbeit weiter zu professionalisieren und gezielt Wirkung zu '
        .'entfalten, gründen wir immer wieder Arbeitsgruppen – und laden Dich herzlich ein, Teil davon '
        .'zu werden. Ein Quereinstieg ist möglich, Du kannst jederzeit dazu kommen. Ob mit '
        .'Fachwissen, Kreativität oder einfach dem Wunsch, etwas zu bewegen!';

    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'quick_access')->get() as $block) {
                $data = $block->data;
                $geaendert = false;

                foreach ($data['karten'] ?? [] as $i => $karte) {
                    if (($karte['url'] ?? null) === '/arbeitsgruppen' && ($karte['text'] ?? null) === self::ALT) {
                        $data['karten'][$i]['text'] = StartseiteSeeder::ARBEITSGRUPPEN_KARTE_TEXT;
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
