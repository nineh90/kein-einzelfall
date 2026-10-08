<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprachfassungen für Gruppen und Team (08.10.2026): {"en": {"teaser": …}}.
 * Siehe App\Models\Concerns\Uebersetzbar.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['groups', 'team_members'] as $tabelle) {
            Schema::table($tabelle, function (Blueprint $table) {
                $table->json('uebersetzungen')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['groups', 'team_members'] as $tabelle) {
            Schema::table($tabelle, function (Blueprint $table) {
                $table->dropColumn('uebersetzungen');
            });
        }
    }
};
