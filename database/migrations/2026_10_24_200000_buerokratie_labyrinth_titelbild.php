<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;

/**
 * Bürokratie-Labyrinth bekommt Taddis Titelbild (KEV-228), nur wo noch
 * keins steht.
 */
return new class extends Migration
{
    private const BILD = '/img/titelbilder/gruppe-buerokratie-labyrinth.webp';

    public function up(): void
    {
        Group::where('slug', 'buerokratie-labyrinth')->whereNull('titelbild')->update(['titelbild' => self::BILD]);
    }

    public function down(): void
    {
        Group::where('slug', 'buerokratie-labyrinth')->where('titelbild', self::BILD)->update(['titelbild' => null]);
    }
};
