<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();

            // Sprachkennung nach BCP 47 und zugleich das URL-Präfix: /en/…, /ru/….
            // Die Standardsprache bekommt kein Präfix — nur so bleiben die 24
            // bestehenden Adressen und die 26 Weiterleitungen unangetastet.
            $table->string('code', 16)->unique();

            // Eigenbezeichnung, so wie sie im Umschalter steht: "Русский", nicht
            // "Russisch". Wer die Seite nicht lesen kann, erkennt nur die eigene.
            $table->string('label');

            // Deutscher Name für das Verwaltungspanel und für die Vorlesehilfe
            // im deutschen Umschalter ("Sprache wechseln zu Russisch").
            $table->string('label_deutsch');

            // Von Anfang an mitgeführt, obwohl DE/EN/RU alle ltr sind: für einen
            // Opferhilfeverein in Hamburg sind Arabisch oder Farsi realistische
            // spätere Wünsche, und nachträglich ist rtl teuer.
            $table->string('richtung', 3)->default('ltr');

            // Neue Sprachen starten unsichtbar. Der Verein soll Übersetzungen in
            // Ruhe vorbereiten können, bevor sie öffentlich erreichbar sind.
            $table->boolean('aktiv')->default(false);

            $table->unsignedSmallInteger('position')->default(0);

            // Genau eine Sprache ist Standard. Sie ist die Sprache ohne Präfix
            // und der Rückfall, wenn eine Übersetzung fehlt.
            $table->boolean('ist_standard')->default(false);

            // Abweichender Rückfall, falls eine Sprache lieber auf eine andere
            // als auf die Standardsprache zurückfallen soll (z.B. de-x-leicht
            // auf de statt auf eine Fremdsprache).
            $table->string('fallback_code', 16)->nullable();

            $table->timestamps();

            $table->index(['aktiv', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
