<?php

use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mehrere Glossare (KEV-155).
 *
 * Bis hierher gab es eine Liste unter /glossar, gezeigt von einem eigenen
 * Controller. Taddi schickt die Glossare aber einzeln („1. Abkürzungen
 * Sozialrecht“, „3. Behörden und Bescheide“, laut Drive kommt „Abkürzungen
 * sexueller Kontext“), jedes als eigene Seite unter „Wissen“.
 *
 * Deshalb ist die Liste jetzt ein Baustein (`glossar`), und jeder Eintrag
 * merkt sich, auf welcher Seite er steht: `seite_gruppe` ist die
 * Übersetzungsgruppe dieser Seite, damit gilt die Zuordnung in jeder Sprache.
 * Leer heißt: im Glossar unter /glossar, wie bisher.
 *
 * Hier bekommt die Seite „glossar“ den Baustein nach ihrem Text, in jeder
 * Sprache, und alle bisherigen Einträge werden ihr zugeordnet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('glossary_terms', function (Blueprint $table) {
            $table->ulid('seite_gruppe')->nullable()->after('uebersetzungs_gruppe')->index();
        });

        $gruppe = Page::where('slug', GlossaryTerm::HAUPTSEITE)->where('locale', 'de')->value('uebersetzungs_gruppe');

        if (! $gruppe) {
            return;
        }

        GlossaryTerm::whereNull('seite_gruppe')->update(['seite_gruppe' => $gruppe]);

        Page::where('uebersetzungs_gruppe', $gruppe)->get()->each(function (Page $seite) {
            if ($seite->blocks()->where('typ', 'glossar')->doesntExist()) {
                $seite->blocks()->create([
                    'typ' => 'glossar',
                    'position' => (int) $seite->blocks()->max('position') + 1,
                    'data' => [],
                ]);
            }
        });
    }

    public function down(): void
    {
        PageBlock::where('typ', 'glossar')->delete();

        Schema::table('glossary_terms', function (Blueprint $table) {
            $table->dropIndex(['seite_gruppe']);
            $table->dropColumn('seite_gruppe');
        });
    }
};
