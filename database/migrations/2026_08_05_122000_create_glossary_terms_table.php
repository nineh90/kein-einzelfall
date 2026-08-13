<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Glossar — Fachbegriffe und Abkürzungen.
 *
 * Wunsch aus der Besprechung vom 02.08.2026: „Ein Abkürzungsverzeichnis als
 * Glossar anlegen, um Begrifflichkeiten verständlich zu machen.“ Genannt wurden
 * unter anderem OEG, SGB XIV, GdB, Pflegegrad und Persönliches Budget.
 *
 * Für diese Zielgruppe ist das mehr als ein Nachschlagewerk. Wer nach einer
 * Straftat einen Bescheid in der Hand hält, in dem „GdB 50, SGB XIV“ steht,
 * muss das verstehen können, ohne vorher ein Studium anzufangen — und ohne
 * jemanden fragen zu müssen.
 *
 * Eigene Tabelle statt einer Inhaltsseite mit Aufklappern: Ein Begriff soll
 * später auch im Fliesstext anderer Seiten erklärbar sein, ohne dass jemand
 * die Erklärung dorthin kopiert und sie dann an zwei Stellen veraltet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('glossary_terms', function (Blueprint $table) {
            $table->id();

            /*
             * Mehrsprachig nach demselben Muster wie `pages`: Jede Sprachfassung
             * ist ein eigener Datensatz, zusammengehalten von einer gemeinsamen
             * Gruppe. Das ist hier besonders wichtig — „GdB“ heisst auf
             * Englisch nicht „GdB“, und eine Übersetzung, die den deutschen
             * Rechtsbegriff verliert, ist für einen Behördengang wertlos.
             */
            $table->string('locale', 12)->index();
            $table->ulid('uebersetzungs_gruppe')->index();

            $table->string('slug');
            // "GdB" — die Abkürzung, wie sie im Bescheid steht
            $table->string('kuerzel')->nullable();
            // "Grad der Behinderung" — der ausgeschriebene Begriff
            $table->string('begriff');
            $table->text('erklaerung');

            /*
             * Weiterführender Verweis, z.B. auf die eigene Themenseite oder auf
             * den Gesetzestext. Bewusst nur einer: Ein Glossareintrag mit sechs
             * Links ist keine Erklärung mehr, sondern eine zweite Recherche.
             */
            $table->string('mehr_url')->nullable();
            $table->string('mehr_label')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Ein Begriff einmal je Sprache.
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glossary_terms');
    }
};
