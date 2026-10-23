<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Wissen: neuer Text von Taddi (KEV-143). Ersetzt die Einleitung der
 * Altseite und nimmt „Zu den einzelnen Kategorien:“ heraus (zwei Links auf
 * 2 von 12 Unterseiten, „Mehr zu „Wissen““ darunter nennt alle). Beides nur,
 * wo noch der Wortlaut der Altseite steht; was der Verein im Panel geändert
 * hat, bleibt. Danach die englische Fassung neu.
 */
return new class extends Migration
{
    private const ALT = [
        'Wir möchten informieren – über Rechte, Antragsmöglichkeiten oder einfach nur darüber, woran wir gerade '
            .'arbeiten.',
        'In diesem Bereich sammeln wir deshalb allgemeine Informationen oder auch aktuelle Entwicklungen zu den '
            .'verschiedensten Themen rund um den Bereich der Opferhilfe. Es handelt sich hierbei um eine stetig '
            .'wachsende Seite, die um weitere Themen ergänzt wird, sobald Material und Kapazitäten vorhanden sind.',
        'Wichtig:',
        'Diese Informationen stellen keine Beratung dar und erheben nicht den Anspruch, vollständig zu sein. Sie '
            .'wurden von uns als Verein nach eigenen Erfahrungen und Auskünften, die uns verschiedene Stellen '
            .'gegeben haben, zusammengetragen.',
    ];

    private const KATEGORIEN = [
        'titel' => 'Zu den einzelnen Kategorien:',
        'absaetze' => [
            '[Erwerbsminderungsrente](/erwerbsminderungsrente)',
            '[FSM – Fonds sexueller Missbrauch](/fsm-erweitertes-hilfesystem)',
        ],
    ];

    public function up(): void
    {
        $seite = Page::where('slug', 'wissen')->where('locale', 'de')->first();

        if (! $seite) {
            return;
        }

        foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
            $data = $block->data ?? [];

            if (blank($data['titel'] ?? null) && ($data['absaetze'] ?? null) === self::ALT) {
                $block->update(['data' => array_replace($data, ['absaetze' => AltseiteSeeder::NEUE_TEXTE['wissen']['']])]);
            }

            if (($data['titel'] ?? null) === self::KATEGORIEN['titel'] && ($data['absaetze'] ?? null) === self::KATEGORIEN['absaetze']) {
                $block->delete();
            }
        }

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        $seite = Page::where('slug', 'wissen')->where('locale', 'de')->first();

        foreach ($seite?->blocks()->where('typ', 'text')->get() ?? [] as $block) {
            if (($block->data['absaetze'] ?? null) === AltseiteSeeder::NEUE_TEXTE['wissen']['']) {
                $block->update(['data' => array_replace($block->data, ['absaetze' => self::ALT])]);
            }
        }
    }
};
