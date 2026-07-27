<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ausdrückliche Freigabe für das Verwaltungs-Panel.
 *
 * Wichtig für später: Sobald es einen Mitglieder-Login gibt, liegen
 * Vereinsmitglieder und Redaktion in derselben Tabelle. Ohne dieses Kennzeichen
 * käme dann jedes angemeldete Mitglied an die Verwaltung — und damit an
 * Anfragen mit Gesundheitsdaten. Das Kennzeichen ist der Riegel davor und
 * steht standardmäßig auf "nein".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('panel_zugang')->default(false)->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('panel_zugang');
        });
    }
};
