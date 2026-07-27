<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog / Aktuelles (Angebotsposition 3).
 *
 * Die Altseite hat null Beiträge — der Blog wird also neu aufgebaut, nicht
 * migriert. Die drei bestehenden Kategorien übernehmen wir trotzdem.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('beschreibung')->nullable();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('slug')->unique();
            $table->string('titel');
            $table->text('teaser')->nullable();
            $table->longText('inhalt');

            $table->string('bild_pfad')->nullable();
            $table->string('bild_alt')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['published_at', 'category_id']);
        });

        // Kein Volltextindex: Die Suche läuft über LIKE. Der Index greift erst
        // ab vier Zeichen (fände „OEG" also nicht), ignoriert Stoppwörter und
        // sieht keine Daten aus offenen Transaktionen — womit sich die Suche
        // nicht testen liesse. Begründung ausführlich in App\Models\Post::scopeSuche().
        // Für einen Vereinsblog ist LIKE schnell genug und dafür vorhersehbar.
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categories');
    }
};
