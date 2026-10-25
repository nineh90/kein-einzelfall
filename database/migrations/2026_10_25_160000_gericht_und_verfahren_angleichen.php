<?php

use App\Models\GlossaryTerm;
use App\Models\Page;
use Database\Seeders\GerichtUndVerfahrenSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * „Gericht & Verfahren“ (KEV-259) wie die anderen Glossare: „Fehlt Dir hier
 * ein Begriff?“ fett und „Gemeinsam KE!N EINZELFALL“ am Ende der Einleitung
 * (Kevin: bei Taddi vergessen). Nur wo noch der bisherige Wortlaut steht.
 *
 * Dazu verweist „Gutachten nach § 109 SGG“ auf die Projektseite, auf Deutsch
 * und Englisch, nur wo noch kein Verweis gepflegt ist. Danach die englische
 * Fassung der Seite neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', GerichtUndVerfahrenSeeder::SLUG)->where('locale', 'de')->first();

        if (! $seite) {
            return;
        }

        foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
            if (($block->data['absaetze'] ?? null) === GerichtUndVerfahrenSeeder::ABSAETZE_BIS_25_10) {
                $block->update(['data' => array_replace($block->data, ['absaetze' => GerichtUndVerfahrenSeeder::ABSAETZE])]);
            }
        }

        $verweise = [
            'de' => ['/109-sgg-gegengutachten', 'Zu unserem Projekt § 109 SGG'],
            'en' => ['/en/109-sgg-gegengutachten', 'To our project on § 109 SGG'],
        ];

        foreach ($verweise as $locale => [$url, $label]) {
            GlossaryTerm::where('slug', 'gutachten-nach-109-sgg')->where('locale', $locale)
                ->whereNull('mehr_url')
                ->update(['mehr_url' => $url, 'mehr_label' => $label]);
        }

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
