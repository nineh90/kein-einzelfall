<?php

use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Übersetzungen sind eigene Seiten-Datensätze, keine JSON-Spalten.
 *
 * Begründung, weil die Entscheidung später teuer zu drehen ist:
 *
 * - Eine Übersetzung darf eine *andere Baustein-Struktur* haben. Bei diesem
 *   Projekt ist das kein Randfall: die Zielgruppen-Module (leichte_sprache,
 *   hilfe_box, inhalts_hinweis) und die Notfallnummern sind sprach- und
 *   länderabhängig.
 * - Slugs müssen übersetzbar sein, sonst verschenkt man SEO.
 * - „Noch nicht übersetzt" ist ein natürlicher Zustand statt eines leeren Feldes.
 * - Der bestehende Renderer bleibt unangetastet — page_blocks hängt an page_id.
 *
 * Ein Paket mit JSON-Spalten pro Feld (spatie/laravel-translatable) hätte
 * bedeutet, JSON in JSON zu schachteln: page_blocks.data ist bereits JSON.
 */
return new class extends Migration
{
    public function up(): void
    {
        $standard = Language::standardCode();

        Schema::table('pages', function (Blueprint $table) use ($standard) {
            $table->string('locale', 16)->default($standard)->after('id');

            // Klammert die Fassungen einer Seite über alle Sprachen zusammen.
            // Eine undurchsichtige Kennung statt eines sprechenden Schlüssels:
            // Slugs dürfen sich ändern, ohne die Verbindung zu zerreissen.
            $table->ulid('uebersetzungs_gruppe')->nullable()->after('locale');
        });

        // Bestand: jede vorhandene Seite ist die deutsche Fassung und bildet
        // ihre eigene Gruppe. Übersetzungen hängen sich später daran.
        foreach (DB::table('pages')->select('id')->get() as $zeile) {
            DB::table('pages')
                ->where('id', $zeile->id)
                ->update(['locale' => $standard, 'uebersetzungs_gruppe' => (string) Str::ulid()]);
        }

        Schema::table('pages', function (Blueprint $table) {
            // Der Slug ist ab jetzt nur noch *innerhalb* einer Sprache eindeutig.
            // Ohne diesen Schritt könnte /en/kontakt nicht neben /kontakt stehen,
            // sobald eine Übersetzung denselben Slug behält.
            $table->dropUnique('pages_slug_unique');
            $table->unique(['locale', 'slug']);
            $table->index(['uebersetzungs_gruppe', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['uebersetzungs_gruppe', 'locale']);
            $table->dropUnique(['locale', 'slug']);
            $table->unique('slug');
            $table->dropColumn(['locale', 'uebersetzungs_gruppe']);
        });
    }
};
