<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\BehoerdenUndBescheideSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Behörden & Bescheide (KEV-258) wie die anderen Glossare unter „Wissen“:
 * Taddis Bild statt des Platzhalters, Unterzeile „Verstehen, was entschieden
 * wurde“ und die grüne Überschrift „Damit aus Behördensprache verständliche
 * Information wird“ über der Einleitung.
 *
 * Ersetzt wird nur der Platzhalter, die Überschrift kommt nur in einen
 * Einleitungsabschnitt ohne Überschrift; was der Verein gepflegt hat, bleibt.
 * Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', BehoerdenUndBescheideSeeder::SLUG)->where('locale', 'de')->first();

        if (! $seite) {
            return;
        }

        Page::where('uebersetzungs_gruppe', $seite->uebersetzungs_gruppe)
            ->where('titelbild', Titelbilder::PLATZHALTER)
            ->update(['titelbild' => null]);

        Titelbilder::setzen();

        $einleitung = $seite->blocks()->where('typ', 'text')->orderBy('position')->first();

        if ($einleitung && blank($einleitung->data['titel'] ?? null)) {
            // Überschrift vor den Absätzen, in der Reihenfolge des Formulars
            $einleitung->update(['data' => ['titel' => BehoerdenUndBescheideSeeder::TEXT_TITEL] + $einleitung->data]);
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
