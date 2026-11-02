<?php

use Database\Seeders\OeffentlichkeitsarbeitSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Einträge der Öffentlichkeitsarbeit bekommen Dokumente zum Herunterladen
 * und einen Text danach (KEV-320). Die eigene Petition „Erst Versorgung
 * sichern“ bekommt Taddis ganzen Text, ihr Bild, Aufruf, Unterschriftenliste
 * und Infoblatt, siehe OeffentlichkeitsarbeitSeeder::petitionErgaenzen().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outreach_items', function (Blueprint $table) {
            if (! Schema::hasColumn('outreach_items', 'dokumente')) {
                $table->string('dokumente_titel')->nullable()->after('link_text');
                $table->text('dokumente_einleitung')->nullable()->after('dokumente_titel');
                $table->json('dokumente')->nullable()->after('dokumente_einleitung');
                $table->text('nachtext')->nullable()->after('dokumente');
            }
        });

        OeffentlichkeitsarbeitSeeder::petitionErgaenzen();
    }

    public function down(): void
    {
        Schema::table('outreach_items', function (Blueprint $table) {
            $table->dropColumn(['dokumente_titel', 'dokumente_einleitung', 'dokumente', 'nachtext']);
        });
    }
};
