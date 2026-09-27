<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Überschrift der Vereinsseite: „Gemeinnütziger Verein“ statt „Verein“
 * (KEV-59). Nur die deutsche Seite und nur, solange dort noch der alte Titel
 * steht. Hat der Verein ihn im Panel schon selbst geändert, bleibt er.
 *
 * Der Menüpunkt heisst weiter „Verein“, er kommt aus navigation.bereich_verein.
 */
return new class extends Migration
{
    public function up(): void
    {
        Page::where('slug', 'verein')->where('locale', 'de')->where('titel', 'Verein')
            ->update(['titel' => 'Gemeinnütziger Verein']);
    }

    public function down(): void
    {
        Page::where('slug', 'verein')->where('locale', 'de')->where('titel', 'Gemeinnütziger Verein')
            ->update(['titel' => 'Verein']);
    }
};
