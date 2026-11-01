<?php

use App\Models\Group;
use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Für wen eine Selbsthilfegruppe ist (KEV-308): „Für Betroffene“ oder
 * „Offen für alle“, als Schilder auf Karte und Seite statt „Online“.
 * Taddis Zuordnung für die bestehenden Gruppen, nur wo noch nichts steht.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('groups', 'zielgruppe')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->string('zielgruppe', 20)->nullable()->after('online');
            });
        }

        TeamUndGruppenSeeder::zielgruppenSetzen();
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('zielgruppe');
        });
    }
};
