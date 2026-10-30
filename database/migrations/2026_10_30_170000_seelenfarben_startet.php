<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;

/**
 * „Seelenfarben“ ist bestätigt (KEV-287): offen für neue Teilnehmende, jeden
 * 1. Freitag um 10 Uhr. Damit steht die Gruppe im Kalender der Startseite
 * und unter Veranstaltungen, der nächste Termin rechnet sich aus dem
 * Rhythmus (06.11.2026).
 */
return new class extends Migration
{
    public function up(): void
    {
        Group::where('slug', 'seelenfarben')->where('status', 'geplant')->update(['status' => 'offen']);
    }

    public function down(): void
    {
        Group::where('slug', 'seelenfarben')->update(['status' => 'geplant']);
    }
};
