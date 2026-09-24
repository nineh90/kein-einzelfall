<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Titelbild für den Seitenkopf.
 *
 * Eine Angabe der Seite und kein Baustein: Es gehört zum Kopf, nicht in den
 * Inhalt, und steht genau einmal pro Seite. Als Baustein ließe es sich
 * irgendwohin schieben, und die Seite hätte zwei oder keinen Kopf mit Bild.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('titelbild')->nullable()->after('titel');
            $table->string('titelbild_alt')->nullable()->after('titelbild');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['titelbild', 'titelbild_alt']);
        });
    }
};
