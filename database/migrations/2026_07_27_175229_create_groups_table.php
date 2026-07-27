<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Selbsthilfe- und Arbeitsgruppen.
 *
 * Bisher standen die Gruppen als gewöhnliche Textabschnitte auf zwei Seiten.
 * Als eigene Datensätze lassen sie sich einheitlich darstellen, filtern und
 * an mehreren Stellen einbinden — und der Verein kann eine neue Gruppe
 * anlegen, ohne im Seitentext zu wühlen.
 *
 * Bewusst KEINE Anmeldeverwaltung: Wer sich zu einer Selbsthilfegruppe
 * anmeldet, offenbart damit Angaben nach Art. 9 DSGVO. Das braucht ein
 * eigenes Konzept mit Löschfristen und Zugriffsregelung — es gehört nicht
 * nebenbei in eine Gruppenübersicht. Hier steht nur, wie man Kontakt aufnimmt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();
            $table->string('name');
            // "AG 01" — die Arbeitsgruppen sind durchnummeriert
            $table->string('kuerzel')->nullable();

            // selbsthilfe | arbeits
            $table->string('typ');

            $table->text('teaser')->nullable();
            $table->longText('beschreibung')->nullable();

            // "Jeden 4. Mittwoch im Monat", "19:00 Uhr", "online via Teams"
            $table->string('rhythmus')->nullable();
            $table->string('uhrzeit')->nullable();
            $table->string('ort')->nullable();
            $table->boolean('online')->default(false);

            // offen | geplant | geschlossen — "Schreibwerkstatt" ist in Planung
            $table->string('status')->default('offen');
            $table->string('anmeldung_hinweis')->nullable();

            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['typ', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
