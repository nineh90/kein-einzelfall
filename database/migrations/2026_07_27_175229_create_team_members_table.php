<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vorstand und Team.
 *
 * Die Seite /ueber-uns-vorstand-und-team umfasst 2.441 Wörter: drei Personen
 * mit je 18 bis 19 Absätzen Selbstvorstellung. Als durchlaufender Fliesstext
 * ist das kaum erfassbar — als Profile mit Kurzangaben und aufklappbarem
 * Volltext dagegen schon.
 *
 * Datenschutz: Die Texte enthalten Jahrgang, Familiensituation und teils
 * eigene Betroffenheit. Das haben die Personen selbst so veröffentlicht;
 * beim Einpflegen wird nichts ergänzt, was nicht schon öffentlich stand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            // "1. Vorsitzende", "Landesstelle Berlin"
            $table->string('rolle')->nullable();
            // "Gründungsmitglied, Jahrgang 1969, geb. in Hamburg"
            $table->string('untertitel')->nullable();

            // Kurzfassung fürs Raster; der Volltext steckt in `profil`.
            $table->text('kurzprofil')->nullable();
            $table->longText('profil')->nullable();

            $table->string('foto_pfad')->nullable();
            $table->string('foto_alt')->nullable();

            // Gruppierung auf der Seite (Vorstand, Landesstellen, Team)
            $table->string('bereich')->nullable();

            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['bereich', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
