<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;

/**
 * Seelenfarben bekommt Taddis Titelbild (KEV-229), nur wo noch keins steht.
 */
return new class extends Migration
{
    private const BILD = '/img/titelbilder/gruppe-seelenfarben.webp';

    public function up(): void
    {
        Group::where('slug', 'seelenfarben')->whereNull('titelbild')->update(['titelbild' => self::BILD]);
    }

    public function down(): void
    {
        Group::where('slug', 'seelenfarben')->where('titelbild', self::BILD)->update(['titelbild' => null]);
    }
};
