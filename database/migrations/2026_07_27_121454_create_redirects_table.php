<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 301-Weiterleitungen.
 *
 * Kern der SEO-Migration: WordPress hängt an alle Seiten-URLs einen Schrägstrich
 * an (/verein/), Laravel nicht (/verein). Ohne Weiterleitung wäre jede einzelne
 * indexierte URL ein 404 — und "nicht schlechter dastehen als jetzt" war eine
 * ausdrückliche Zusage an den Verein.
 *
 * In der Datenbank statt in einer Config, damit die Redaktion später selbst
 * Weiterleitungen anlegen kann, ohne dass jemand deployen muss.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();

            // Ohne führenden Schrägstrich gespeichert, damit der Vergleich
            // mit $request->path() direkt aufgeht.
            $table->string('von')->unique();
            $table->string('nach');
            $table->unsignedSmallInteger('status')->default(301);

            // Für die Kontrolle nach dem Go-Live: greift die Regel überhaupt?
            $table->unsignedInteger('treffer')->default(0);
            $table->timestamp('zuletzt_genutzt_at')->nullable();

            $table->string('notiz')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
