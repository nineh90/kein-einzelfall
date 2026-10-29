<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;

/**
 * Die Schreibwerkstatt „Zwischen den Zeilen“ bekommt Taddis Titelbild
 * (KEV-282), nur wo noch keins steht.
 */
return new class extends Migration
{
    private const BILD = '/img/titelbilder/gruppe-schreibwerkstatt.webp';

    public function up(): void
    {
        Group::where('slug', 'schreibwerkstatt')->whereNull('titelbild')->update(['titelbild' => self::BILD]);
    }

    public function down(): void
    {
        Group::where('slug', 'schreibwerkstatt')->where('titelbild', self::BILD)->update(['titelbild' => null]);
    }
};
