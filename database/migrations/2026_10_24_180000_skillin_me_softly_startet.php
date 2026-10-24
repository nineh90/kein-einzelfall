<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;

/**
 * „Skillin me Softly“ läuft ab November 2026, jeden 3. Dienstag um 11 Uhr
 * (KEV-230): offen für neue Teilnehmende, der nächste Termin rechnet sich aus
 * dem Rhythmus (17.11.2026). Dazu Taddis Titelbild, nur wo noch keins steht.
 */
return new class extends Migration
{
    public function up(): void
    {
        Group::where('slug', 'skillin-me-softly')->where('status', 'geplant')->update(['status' => 'offen']);

        Group::where('slug', 'skillin-me-softly')->whereNull('titelbild')
            ->update(['titelbild' => '/img/titelbilder/gruppe-skillin-me-softly.webp']);
    }

    public function down(): void
    {
        Group::where('slug', 'skillin-me-softly')->update(['status' => 'geplant']);
        Group::where('slug', 'skillin-me-softly')
            ->where('titelbild', '/img/titelbilder/gruppe-skillin-me-softly.webp')
            ->update(['titelbild' => null]);
    }
};
