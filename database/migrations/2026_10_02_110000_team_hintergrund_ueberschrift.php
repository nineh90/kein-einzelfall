<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Teamseite: Der Hinweis auf die Ehrenamtlichen im Hintergrund („Zusätzlich
 * arbeiten im Hintergrund …“) bekommt die Überschrift „Im Hintergrund“
 * (KEV-85). Vorstand und Team haben schon eine.
 *
 * Nur wo der Block noch keine Überschrift hat.
 */
return new class extends Migration
{
    private const TITEL = 'Im Hintergrund';

    public function up(): void
    {
        foreach ($this->bloecke() as $block) {
            if (filled($block->data['titel'] ?? null)) {
                continue;
            }

            // Titel vor die Absätze, wie das Panel die Felder speichert.
            $block->update(['data' => ['titel' => self::TITEL] + array_diff_key($block->data, ['titel' => true])]);
        }
    }

    public function down(): void
    {
        foreach ($this->bloecke() as $block) {
            if (($block->data['titel'] ?? null) === self::TITEL) {
                $block->update(['data' => array_diff_key($block->data, ['titel' => true])]);
            }
        }
    }

    private function bloecke()
    {
        return Page::where('slug', 'ueber-uns-vorstand-und-team')->get()
            ->flatMap(fn ($seite) => $seite->blocks()->where('typ', 'text')->get())
            ->filter(fn ($block) => str_starts_with($block->data['absaetze'][0] ?? '', 'Zusätzlich arbeiten im Hintergrund'));
    }
};
