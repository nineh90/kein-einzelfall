<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\KlartextStattFachspracheSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Klartext statt Fachsprache (KEV-278): Taddis Bild statt des Platzhalters,
 * Unterzeile „Verstehen statt verzweifeln“ und die grüne Überschrift „Damit
 * du weißt, was über dich entschieden wird.“ über ihrem Text.
 *
 * Ersetzt wird nur der Platzhalter, die Überschrift kommt nur in einen
 * Textabschnitt ohne Überschrift; was der Verein gepflegt hat, bleibt.
 * Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', KlartextStattFachspracheSeeder::SLUG)->where('locale', 'de')->first();

        if (! $seite) {
            return;
        }

        Page::where('uebersetzungs_gruppe', $seite->uebersetzungs_gruppe)
            ->where('titelbild', Titelbilder::PLATZHALTER)
            ->update(['titelbild' => null]);

        Titelbilder::setzen();

        $text = $seite->blocks()->where('typ', 'text')->orderBy('position')->first();

        if ($text && blank($text->data['titel'] ?? null)) {
            // Überschrift vor den Absätzen, in der Reihenfolge des Formulars
            $text->update(['data' => ['titel' => KlartextStattFachspracheSeeder::TEXT_TITEL] + $text->data]);
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
