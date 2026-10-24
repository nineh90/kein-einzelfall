<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anmeldung zu Selbsthilfegruppen über die Website (KEV-226).
 *
 * Die Gruppe bekommt ihren Zugangslink (Teams) und einen Schalter, ob er
 * nach der Anmeldung sofort rausgeht. Voreinstellung: nein, der Verein gibt
 * jede Anmeldung im Verwaltungsbereich frei (die Gruppen sind nur für
 * Betroffene).
 *
 * Eine Anmeldung verrät, dass jemand an einer Selbsthilfegruppe zu Gewalt-
 * und Traumafolgen teilnimmt, also Gesundheitsdaten (Art. 9 DSGVO). Name
 * und E-Mail deshalb verschlüsselt, Löschung nach Frist
 * (config/anmeldungen.php). Der Zugangslink ebenfalls verschlüsselt: Wer
 * ihn hat, sitzt im Gruppenraum.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->text('zugangslink')->nullable()->after('anmeldung_hinweis');
            $table->boolean('link_automatisch')->default(false)->after('zugangslink');
        });

        Schema::create('group_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            // Verschlüsselt (Laravel `encrypted`), daher text
            $table->text('name')->nullable();
            $table->text('email');
            // Sprache der Seite beim Anmelden: Die Mails gehen in derselben.
            $table->string('locale', 5)->default('de');
            $table->string('status', 20)->default('offen');
            // Zustimmung zu Teilnahme- und Verschwiegenheitsvereinbarung,
            // Gruppenregeln und zur Verarbeitung, als Nachweis
            $table->timestamp('zugestimmt_at');
            $table->timestamp('bearbeitet_at')->nullable();
            $table->timestamp('link_versendet_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_registrations');

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['zugangslink', 'link_automatisch']);
        });
    }
};
