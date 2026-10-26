<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unterschrift unter dem Text jeder Person (KEV-183): rechtsbündig „– Name“.
 * Leer heißt der Name; eigene Zeile etwa bei „Herr und Frau Unbekannt“
 * (KEV-188: „DANKE! Herr und Frau Unbekannt“).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->string('signatur')->nullable()->after('profil');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn('signatur');
        });
    }
};
