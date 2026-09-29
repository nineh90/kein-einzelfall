<?php

use App\Models\Group;
use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Arbeitsgruppen neu, Texte von Taddi (KEV-74).
 *
 *  - Gruppen bekommen einen Schlusssatz (handschriftlich unter dem Text).
 *  - Die acht AGs mit neuen Namen, Kurzbeschreibung und ausführlichem Text.
 *    AG Nr. 7 (Glaubhaftigkeitsgutachten) ist neu, die App war bisher
 *    AG 07 „Traumabegleiter“ und ist jetzt AG Nr. 8. Alle offen und online.
 *  - Die Seite /arbeitsgruppen: neue Einleitung, der Block „So bist Du
 *    dabei:“ fällt weg. Nur, wo noch die Einleitung der Altseite steht.
 *
 * Die AG-Texte werden überschrieben: Im Panel hat der Verein daran noch
 * nichts geändert, die Seite ist nicht übergeben.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('schlusssatz', 400)->nullable()->after('beschreibung');
        });

        Group::where('slug', 'ag-07-traumabegleiter')->update(['slug' => 'ag-08-app']);

        $neu = collect(TeamUndGruppenSeeder::gruppenliste())->where('typ', 'arbeits');

        foreach ($neu->values() as $i => $ag) {
            Group::updateOrCreate(['slug' => $ag['slug']], $ag + [
                'position' => 100 + $i,
                'published_at' => now(),
            ]);
        }

        TeamUndGruppenSeeder::arbeitsgruppenseiteAufbauen([
            'de' => TeamUndGruppenSeeder::ARBEITSGRUPPEN_ALT + ['seite' => TeamUndGruppenSeeder::ARBEITSGRUPPEN_SEITE],
        ]);
    }

    public function down(): void
    {
        // Texte nicht zurück: Die alten waren durch diese ersetzt. Nur die
        // Spalte geht, damit ein Rücklauf sauber bleibt.
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('schlusssatz');
        });
    }
};
