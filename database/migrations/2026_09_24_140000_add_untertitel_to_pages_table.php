<?php

use App\Support\Titelbilder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unterzeile für Seiten mit Titelbild.
 *
 * Auf dem Bild stehen immer drei Zeilen: Bereich, Titel, Unterzeile. Vorher
 * war es je Seite etwas anderes (mal nur der Titel, mal ein ganzer Absatz als
 * Vorspann), und die Köpfe sahen nebeneinander nicht aus wie aus einem Guss.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('untertitel')->nullable()->after('titelbild_alt');
        });

        Titelbilder::setzen();
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('untertitel');
        });
    }
};
