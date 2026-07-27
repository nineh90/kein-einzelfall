<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('titel');

            // Metadaten getrennt vom Titel: die Altseite hat gepflegte
            // Meta-Angaben, die wir 1:1 übernehmen (SEO darf sich nicht
            // verschlechtern — ausdrücklicher Kundenwunsch).
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('noindex')->default(false);

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('published_at');
        });

        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();

            // Entspricht einer Blade-Komponente unter components/blocks/.
            // Kuratierte Liste statt freier Page-Builder — genau der Unterschied
            // zu Elementor auf der Altseite.
            $table->string('typ');
            $table->unsignedSmallInteger('position')->default(0);

            // Inhalt je nach Typ. JSON, weil jeder Blocktyp andere Felder hat
            // und wir sonst zwölf Spezialtabellen bräuchten.
            $table->json('data');

            $table->timestamps();

            $table->index(['page_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
        Schema::dropIfExists('pages');
    }
};
