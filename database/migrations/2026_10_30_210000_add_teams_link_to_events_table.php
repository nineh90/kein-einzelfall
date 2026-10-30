<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Teams-Link bei Online-Veranstaltungen (Kevin, 30.10.2026). Steht öffentlich
 * auf der Veranstaltungsseite, anders als der Zugangslink der
 * Selbsthilfegruppen, der nur nach Freigabe per E-Mail rausgeht.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('teams_link', 2048)->nullable()->after('online');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('teams_link');
        });
    }
};
