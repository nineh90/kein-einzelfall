<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Veranstaltungen nach Taddis neuem Doc (KEV-317): Jede gehört zu einer
 * Reihe („Im Dialog – Wissen trifft Erfahrung“, „Im Dialog – Frage trifft
 * Antwort“, „Zu Gast“) und hat ein eigenes Bild.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'reihe')) {
                $table->string('reihe', 40)->nullable()->after('art');
            }
            if (! Schema::hasColumn('events', 'titelbild')) {
                $table->string('titelbild')->nullable()->after('beschreibung');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['reihe', 'titelbild']);
        });
    }
};
