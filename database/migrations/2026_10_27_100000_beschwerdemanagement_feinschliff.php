<?php

use App\Models\Page;
use Database\Seeders\BeschwerdemanagementSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Beschwerdemanagement nach Taddis Rückmeldung (KEV-213, KEV-214): Umbruch
 * in der Unterzeile, kontakt@ statt kritik@, grüner Satz rechts. Danach die
 * englische Fassung (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    public function up(): void
    {
        BeschwerdemanagementSeeder::nachtragen();

        // Nur die alte Unterzeile tauschen, eine im Panel geänderte bleibt.
        Page::where('slug', 'beschwerdemanagement')
            ->where('locale', 'de')
            ->where('untertitel', 'Deine Rückmeldung hilft uns, besser zu werden.')
            ->update(['untertitel' => "Deine Rückmeldung hilft uns,\nbesser zu werden."]);

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
