<?php

use App\Support\Titelbilder;
use Illuminate\Database\Migrations\Migration;

/**
 * Jede Seite bekommt ein Titelbild (KEV-36): Wo noch keins steht, vorerst
 * der neutrale Platzhalter. Siehe App\Support\Titelbilder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Titelbilder::platzhalterSetzen();
    }

    public function down(): void
    {
        Titelbilder::platzhalterEntfernen();
    }
};
