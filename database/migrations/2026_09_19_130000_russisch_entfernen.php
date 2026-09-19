<?php

use App\Models\GlossaryTerm;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

/**
 * Nimmt Russisch als Sprache heraus.
 *
 * Russisch war seit dem 31.07.2026 die dritte Sprache — als maschinell
 * übersetzte Vorführung von vier Kernseiten, die niemand im Team gegenlesen
 * konnte. Kevin hat sie am 19.09.2026 gestrichen. Die Sprachdateien und die
 * kyrillischen Schriftschnitte sind mit demselben Commit aus dem Code
 * verschwunden; hier geht es um das, was in der Datenbank liegt.
 *
 * Zwei Fälle, und die Migration unterscheidet sie selbst:
 *
 *   1. Niemand hat je eine russische Seite bearbeitet — dann ist alles von
 *      uns und wird gelöscht: die vier Seiten samt Bausteinen, russische
 *      Glossareinträge, die Sprachzeile.
 *
 *   2. Jemand hat eine russische Seite im Panel angefasst — dann steckt darin
 *      Arbeit, die nicht von uns stammt. Die Sprache wird nur abgeschaltet
 *      (`aktiv = false`), die Seiten bleiben. Nichts ist öffentlich
 *      erreichbar, aber auch nichts verloren; der Verein entscheidet.
 *
 * „Bearbeitet“ heisst: updated_at liegt mehr als eine Minute nach created_at.
 * Der Seeder legt eine Seite und ihre Bausteine in derselben Sekunde an.
 */
return new class extends Migration
{
    public function up(): void
    {
        $russisch = Language::where('code', 'ru')->first();

        if (! $russisch) {
            return;
        }

        $bearbeitet = Page::where('locale', 'ru')
            ->whereRaw('updated_at > created_at + INTERVAL 1 MINUTE')
            ->exists();

        if ($bearbeitet) {
            $russisch->update(['aktiv' => false]);
            Language::memoLeeren();

            Log::warning('Russisch nur abgeschaltet, nicht gelöscht: Mindestens eine russische '
                .'Seite wurde im Panel bearbeitet. Entscheidung liegt beim Verein.');

            return;
        }

        // Bausteine hängen per Fremdschlüssel an der Seite und gehen mit.
        Page::where('locale', 'ru')->get()->each->delete();
        GlossaryTerm::where('locale', 'ru')->delete();

        $russisch->delete();
        Language::memoLeeren();
    }

    public function down(): void
    {
        // Bewusst nichts. Was gelöscht wurde, waren maschinelle Entwürfe;
        // sie kämen aus dem Seeder zurück, nicht aus dieser Migration.
    }
};
