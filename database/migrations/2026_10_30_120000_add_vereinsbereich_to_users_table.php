<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zugang nur zum Vereinsbereich (/intern), nicht zur Verwaltung (/admin).
 *
 * Kevin und Nils am 30.10.2026: Der Verein soll Veranstaltungen, Anmeldungen
 * und Anfragen bearbeiten, aber keine Seiten, Menüs oder Texte der Website
 * ändern können. Eigenes Kennzeichen statt panel_zugang, damit ein
 * Vereinskonto nie versehentlich in die Verwaltung kommt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('vereinsbereich')->default(false)->after('panel_zugang');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('vereinsbereich');
        });
    }
};
