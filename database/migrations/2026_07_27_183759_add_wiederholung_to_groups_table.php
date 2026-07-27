<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wiederkehrende Gruppentermine berechenbar machen.
 *
 * Bisher stand der Rhythmus als Freitext in der Gruppe („Jeden 4. Mittwoch im
 * Monat"). Lesbar für Menschen, aber nicht für den Kalender — die Termine
 * tauchten deshalb nur auf der Gruppenseite auf. Wer wissen wollte, wann das
 * nächste Treffen ist, musste die ganze Seite durchsuchen.
 *
 * Mit diesen Feldern lassen sich die nächsten Termine ausrechnen und im
 * Kalender neben den Einzelveranstaltungen anzeigen.
 *
 * Der Freitext bleibt erhalten: Er ist die verbindliche Anzeige, die
 * strukturierten Felder sind die Grundlage der Berechnung. Kann der Verein
 * einen Rhythmus nicht in dieses Schema pressen, bleibt der Text trotzdem
 * richtig — dann entfallen nur die berechneten Termine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // keine | woechentlich | monatlich_nter_wochentag
            $table->string('wiederholung')->default('keine')->after('uhrzeit');

            // 1 = Montag … 7 = Sonntag (ISO-8601, wie Carbon::dayOfWeekIso)
            $table->unsignedTinyInteger('wochentag')->nullable()->after('wiederholung');

            // 1 = erster … 5 = letzter des Monats
            $table->unsignedTinyInteger('woche_im_monat')->nullable()->after('wochentag');

            // Für die Uhrzeit im berechneten Termin
            $table->time('beginn_zeit')->nullable()->after('woche_im_monat');
            $table->unsignedSmallInteger('dauer_minuten')->nullable()->after('beginn_zeit');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn([
                'wiederholung', 'wochentag', 'woche_im_monat', 'beginn_zeit', 'dauer_minuten',
            ]);
        });
    }
};
