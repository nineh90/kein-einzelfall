<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Englische Seiten in Suchmaschinen (30.10.2026, Kevin): Beim Verein liest
 * niemand die maschinellen Übersetzungen gegen, und die Maschine ist besser
 * als nichts. Jede ungeprüfte Übersetzung bekommt das noindex ihrer
 * deutschen Fassung; der Vermerk „maschinell übersetzt“ bleibt.
 *
 * Nur ungeprüfte Seiten: Eine vom Verein freigegebene Übersetzung gehört ihm.
 */
return new class extends Migration
{
    public function up(): void
    {
        $deutsch = Page::where('locale', 'de')
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->pluck('noindex', 'uebersetzungs_gruppe');

        Page::where('locale', '!=', 'de')
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->where('ungeprueft', true)
            ->get()
            ->each(fn (Page $seite) => $seite->update(['noindex' => (bool) ($deutsch[$seite->uebersetzungs_gruppe] ?? true)]));
    }

    public function down(): void
    {
        Page::where('locale', '!=', 'de')->where('ungeprueft', true)->update(['noindex' => true]);
    }
};
