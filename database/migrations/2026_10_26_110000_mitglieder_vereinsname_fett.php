<?php

use App\Models\Page;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, Abschnitt „Mitglieder“: „KE!N EINZELFALL“ im Text fett, Wunsch
 * von Taddi (KEV-167).
 *
 * Nur wo der Name noch ohne Hervorhebung im Text von KEV-55 steht. Hat der
 * Verein den Abschnitt im Panel geändert, bleibt er, wie er ist. Danach die
 * englische Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = 'dass Du hinter KE!N EINZELFALL und unserer Arbeit stehst.';

    private const NEU = 'dass Du hinter *KE!N EINZELFALL* und unserer Arbeit stehst.';

    public function up(): void
    {
        // Der Mitglieder-Abschnitt ist der mit dem Knopf zur Mitgliedschaft
        // (wie in 2026_09_27_140000_mitglieder_text_erneuern).
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            $seite->blocks()
                ->where('typ', 'text')
                ->get()
                ->filter(fn ($block) => ($block->data['cta']['url'] ?? null) === '/mitgliedschaft')
                ->each(function ($block) {
                    $absaetze = $block->data['absaetze'] ?? [];

                    if (count($absaetze) === 1 && is_string($absaetze[0])
                        && str_starts_with($absaetze[0], 'Du fühlst Dich mit unserer Vision verbunden?')
                        && substr_count($absaetze[0], self::ALT) === 1) {
                        $block->update(['data' => array_replace($block->data, [
                            'absaetze' => [str_replace(self::ALT, self::NEU, $absaetze[0])],
                        ])]);
                    }
                });
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
