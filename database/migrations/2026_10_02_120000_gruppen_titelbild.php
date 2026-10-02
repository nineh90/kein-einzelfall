<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gruppen bekommen ein Titelbild, wie die Seiten (KEV-82). Mit Bild steht
 * auf der Seite der Gruppe der Seitenkopf mit Name und Kurzbeschreibung.
 *
 * Das erste kommt von Taddi, für „Wir sind nicht mehr stumm“. Nur, wo dort
 * noch keins steht.
 */
return new class extends Migration
{
    private const BILD = '/img/titelbilder/gruppe-wir-sind-nicht-mehr-stumm.webp';

    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('titelbild')->nullable()->after('schlusssatz');
        });

        Group::where('slug', 'wir-sind-nicht-mehr-stumm')
            ->whereNull('titelbild')
            ->update(['titelbild' => self::BILD]);
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('titelbild');
        });
    }
};
