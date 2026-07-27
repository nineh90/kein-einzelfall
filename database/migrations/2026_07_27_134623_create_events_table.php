<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Veranstaltungen (Angebotsposition 2).
 *
 * Ersetzt das WordPress-Plugin "The Events Calendar". Dessen Umfang wird
 * bewusst nicht nachgebaut: Auf der Altseite steht dort genau ein Eintrag und
 * es gibt keine kommenden Termine. Ein eigener, schlanker Kalender passt
 * besser als ein Plugin mit hundert Einstellungen, von denen zwei genutzt
 * werden.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();
            $table->string('titel');
            $table->text('teaser')->nullable();
            $table->longText('beschreibung')->nullable();

            $table->timestamp('beginnt_am');
            $table->timestamp('endet_am')->nullable();
            // Ganztägig: dann wird keine Uhrzeit angezeigt und im iCal-Export
            // ein reines Datum ausgegeben.
            $table->boolean('ganztaegig')->default(false);

            // Art der Veranstaltung (Selbsthilfegruppe, Vortrag, …).
            // Freitext statt fester Liste: Der Verein soll neue Formate anlegen
            // können, ohne dass wir eine Migration schreiben.
            $table->string('art')->nullable();

            $table->string('ort')->nullable();
            $table->string('adresse')->nullable();
            $table->boolean('online')->default(false);

            // Bewusst nur ein Link, keine Anmeldeverwaltung: Anmeldungen zu
            // Selbsthilfegruppen sind Art.-9-Daten. Das gehört in die
            // Gruppenverwaltung mit eigenem Konzept, nicht nebenbei in den
            // Kalender.
            $table->string('anmeldung_url')->nullable();
            $table->string('anmeldung_hinweis')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['beginnt_am', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
