<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite: Der Aufmacher bekommt ein Hintergrundbild (KEV-35, Wunsch von
 * Taddi): eine ruhige Wand ohne Motiv, davor steht das Logo (KEV-36).
 *
 * Nur wo der Aufmacher noch kein Bild hat. Alle Sprachfassungen, das Bild
 * zeigt keinen Text.
 */
return new class extends Migration
{
    private const BILD = '/img/titelbilder/startseite.webp';

    public function up(): void
    {
        $this->jeAufmacher(function (array $data) {
            if (filled($data['bild'] ?? null)) {
                return null;
            }

            // Direkt hinter den Titel, wie im Seeder: Die Reihenfolge der
            // Felder ist die des Panels, sonst meldete es beim Speichern eine
            // Änderung.
            $neu = [];
            foreach ($data as $schluessel => $wert) {
                $neu[$schluessel] = $wert;
                if ($schluessel === 'titel') {
                    $neu['bild'] = self::BILD;
                }
            }

            return $neu + ['bild' => self::BILD];
        });
    }

    public function down(): void
    {
        $this->jeAufmacher(fn (array $data) => ($data['bild'] ?? null) === self::BILD
            ? array_diff_key($data, ['bild' => true])
            : null);
    }

    /** @param  callable(array): ?array  $aendern  null = nichts tun */
    private function jeAufmacher(callable $aendern): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'hero')->get() as $block) {
                $neu = $aendern($block->data);

                if ($neu !== null) {
                    $block->update(['data' => $neu]);
                }
            }
        }
    }
};
