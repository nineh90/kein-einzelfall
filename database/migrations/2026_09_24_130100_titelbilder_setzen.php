<?php

use App\Support\Titelbilder;
use Illuminate\Database\Migrations\Migration;

/** Titelbilder für die Inhaltsseiten, siehe App\Support\Titelbilder. */
return new class extends Migration
{
    public function up(): void
    {
        Titelbilder::setzen();
    }

    public function down(): void
    {
        Titelbilder::entfernen();
    }
};
