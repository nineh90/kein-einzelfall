<?php

use App\Models\Page;
use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Teamseite: Über „Zusätzlich arbeiten im Hintergrund …“ steht jetzt „Herr
 * und Frau Unbekannt“ statt „Im Hintergrund“, der Abschnitt mittig über der
 * Karte (KEV-68). Nur wo noch die alte oder gar keine Überschrift steht.
 */
return new class extends Migration
{
    private const ALT = 'Im Hintergrund';

    public function up(): void
    {
        foreach ($this->bloecke() as $block) {
            $titel = $block->data['titel'] ?? null;

            if (filled($titel) && $titel !== self::ALT) {
                continue;
            }

            $block->update(['data' => ['titel' => TeamUndGruppenSeeder::HINTERGRUND_UEBERSCHRIFT]
                + array_diff_key($block->data, ['titel' => true]) + ['mittig' => true]]);
        }
    }

    public function down(): void
    {
        foreach ($this->bloecke() as $block) {
            if (($block->data['titel'] ?? null) === TeamUndGruppenSeeder::HINTERGRUND_UEBERSCHRIFT) {
                $block->update(['data' => ['titel' => self::ALT]
                    + array_diff_key($block->data, ['titel' => true, 'mittig' => true])]);
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
