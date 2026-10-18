<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, „Was wir gemeinsam bewegen“: neuer Text der Karte
 * „Arbeitsgruppen“ von Taddi (KEV-114). Der Satz aus ihren Anführungszeichen
 * fett und als eigener Absatz.
 *
 * Nur wo noch der Text von KEV-49 steht. Danach die englische Fassung neu,
 * wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = '*Du hast Ideen, Wissen, bist kreativ oder hast einfach Lust, etwas zu verändern?* '
        .'In unseren Arbeitsgruppen bringen Menschen ihre Erfahrungen, Perspektiven und ihr Wissen '
        .'zusammen, entwickeln daraus Ideen und setzen gemeinsam Projekte in Bewegung. Du kannst Dich '
        .'jederzeit in laufende Arbeitsgruppen einbringen – auch ohne Vorkenntnisse.';

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
