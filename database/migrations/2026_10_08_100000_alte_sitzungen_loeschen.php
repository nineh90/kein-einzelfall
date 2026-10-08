<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Löscht die Sitzungen aus der Zeit vor der Verschlüsselung. Darin können
 * Nachrichtentexte nach einem Formularfehler, Suchbegriffe, IP-Adressen und
 * Browserkennungen im Klartext stehen. Wer gerade angemeldet ist, muss sich
 * einmal neu anmelden.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->delete();
        }
    }

    public function down(): void
    {
        // Gelöscht ist gelöscht.
    }
};
