<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zwei Spalten für KEV-156 („Raum für Ungesagtes“).
 *
 * - `triggerwarnung`: eigene Warnung vor einer Seite und, wenn sie ein Bereich
 *   im Menü ist, vor allen Seiten darunter. Absätze als Liste, wie im
 *   Textbaustein. Leer = keine eigene Warnung, es gilt nur der allgemeine
 *   Hinweis.
 * - `aufrufe`: wie oft die Seite aufgerufen wurde. Nur die Zahl, keine
 *   Adresse, keine Uhrzeit, nichts über die Person (App\Support\Aufrufe).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->json('triggerwarnung')->nullable()->after('untertitel');
            $table->unsignedInteger('aufrufe')->default(0)->after('triggerwarnung');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['triggerwarnung', 'aufrufe']);
        });
    }
};
