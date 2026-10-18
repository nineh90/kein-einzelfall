<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, „Jetzt spenden“: Der Leitsatz in Handschrift heisst „Gute Ideen
 * brauchen Deinen Rückenwind.“ und steht unter dem Absatz statt unter der
 * Überschrift (KEV-119). Die Stelle regelt die Vorlage, hier nur der Text.
 *
 * Nur wo noch der alte Leitsatz steht. Danach die englische Fassung neu, wie
 * bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = 'Gute Ideen brauchen Rückenwind.';

    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'donation_options')->get() as $block) {
                if (($block->data['hand'] ?? null) === self::ALT) {
                    $block->update(['data' => array_replace($block->data, [
                        'hand' => StartseiteSeeder::SPENDEN_LEITSATZ,
                    ])]);
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
