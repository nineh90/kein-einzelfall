<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Glossar: Erklärung freiwillig (KEV-153).
 *
 * Taddis Abkürzungsverzeichnis löst rund 200 Abkürzungen auf, erklärt sie
 * aber nicht. „AOK — Allgemeine Ortskrankenkasse“ ist schon die Antwort; ein
 * Pflichtfeld hätte den Verein gezwungen, für jede eine Erklärung zu erfinden.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('glossary_terms', function (Blueprint $table) {
            $table->text('erklaerung')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Bewusst nichts: Einträge ohne Erklärung gäbe es dann nicht mehr.
    }
};
