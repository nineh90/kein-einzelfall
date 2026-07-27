<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anfragen aus dem Kontaktformular.
 *
 * Hier landen Angaben nach Art. 9 DSGVO: Menschen schreiben über erlebte
 * Straftaten, Gesundheit und laufende Verfahren. Das Schema ist danach gebaut.
 *
 * Grundsätze:
 *  - Alle Inhalts- und Kontaktfelder werden verschlüsselt abgelegt (siehe Model).
 *    Deshalb TEXT statt VARCHAR: verschlüsselte Werte sind deutlich länger als
 *    der Klartext und sprengen VARCHAR(255) schnell.
 *  - Verschlüsselte Felder lassen sich nicht durchsuchen oder sortieren.
 *    Das ist hier kein Nachteil, sondern gewollt.
 *  - Unverschlüsselt bleibt nur, was zur Bearbeitung nötig und für sich
 *    genommen nicht aussagekräftig ist: Status und Zeitstempel.
 *  - Keine IP-Adresse, kein User-Agent. Was wir nicht speichern, kann auch
 *    nicht abfließen — und für eine Anfrage brauchen wir beides nicht.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();

            // verschlüsselt (siehe App\Models\Inquiry)
            $table->text('name')->nullable();
            $table->text('email')->nullable();
            $table->text('betreff');
            $table->text('nachricht');

            // Bearbeitungsstand — nicht personenbezogen
            $table->string('status')->default('offen');
            $table->timestamp('erledigt_at')->nullable();

            // Woher die Anfrage kam (/anfragen oder /kontakt). Hilft bei der
            // Einordnung, sagt nichts über die Person aus.
            $table->string('herkunft')->nullable();

            $table->timestamps();

            $table->index('status');
            // Für die automatische Löschung nach Ablauf der Aufbewahrungsfrist
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
