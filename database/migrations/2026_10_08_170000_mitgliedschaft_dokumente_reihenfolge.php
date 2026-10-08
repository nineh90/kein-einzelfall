<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Mitgliedschaft (KEV-96): Taddis Nachtrag „Hier kannst du die Beitrags- und
 * Mitgliederordnung vollständig einsehen.“ ans Ende des Abschnitts aus KEV-95,
 * und die Dokumente in ihrer Reihenfolge: Antrag, Ausfüllhilfe, Ordnung.
 *
 * Der Satz kommt nur dazu, wo der Abschnitt noch genau Taddis Text aus KEV-95
 * trägt. Danach die englische Fassung neu, wie bei allen Seiten seit
 * 08.10.2026.
 */
return new class extends Migration
{
    private const SLUG = 'mitgliedschaft';

    public function up(): void
    {
        $neu = AltseiteSeeder::NEUE_ABSCHNITTE[self::SLUG][0];
        $vorher = array_slice($neu['absaetze'], 0, -1);

        foreach (Page::where('slug', self::SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                if (($block->data['titel'] ?? null) === $neu['titel'] && ($block->data['absaetze'] ?? null) === $vorher) {
                    $block->update(['data' => array_replace($block->data, ['absaetze' => $neu['absaetze']])]);
                }
            }

            foreach ($seite->blocks()->where('typ', 'download_list')->get() as $liste) {
                $dokumente = $liste->data['dokumente'] ?? [];
                $sortiert = AltseiteSeeder::dokumenteSortieren(self::SLUG, $dokumente);

                if ($sortiert !== $dokumente) {
                    $liste->update(['data' => array_replace($liste->data, ['dokumente' => $sortiert])]);
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
