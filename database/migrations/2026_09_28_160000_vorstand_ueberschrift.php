<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Teamseite: „Vorstandsebene“ über den Vorstandskarten (KEV-65, Wunsch von
 * Taddi). Nur wo das Raster noch keinen Titel hat.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->raster() as $block) {
            if (blank($block->data['titel'] ?? null)) {
                $block->update(['data' => ['titel' => 'Vorstandsebene'] + $block->data]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->raster() as $block) {
            if (($block->data['titel'] ?? null) === 'Vorstandsebene') {
                $block->update(['data' => array_diff_key($block->data, ['titel' => true])]);
            }
        }
    }

    private function raster()
    {
        return Page::where('slug', 'ueber-uns-vorstand-und-team')->where('locale', 'de')->get()
            ->flatMap(fn ($seite) => $seite->blocks()->where('typ', 'team_grid')->get())
            ->filter(fn ($block) => ($block->data['bereich'] ?? null) === 'Vorstand');
    }
};
