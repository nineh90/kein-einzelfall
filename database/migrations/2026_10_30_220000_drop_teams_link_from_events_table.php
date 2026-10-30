<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Teams-Link bei Veranstaltungen wieder entfernt (Kevin, 30.10.2026): Auf
 * /veranstaltungen beschreibt der Verein selbst, dass man sich per E-Mail
 * anmeldet und den Zugangslink danach persönlich bekommt. Ein öffentlicher
 * Link widerspräche dem.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('events', 'teams_link')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('teams_link');
            });
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('teams_link', 2048)->nullable()->after('online');
        });
    }
};
