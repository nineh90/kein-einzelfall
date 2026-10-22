<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Einträge der Öffentlichkeitsarbeit (KEV-134): Messen, später Petitionen
 * und Presse. Jeder mit eigener Seite unter /oeffentlichkeitsarbeit/…, auf
 * der Übersicht als kleine Karte. Siehe App\Models\OutreachItem.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_items', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('art')->default('messe')->index();
            $table->string('titel');
            $table->date('datum')->nullable();
            $table->string('ort')->nullable();
            $table->string('teaser', 400)->nullable();
            $table->text('beschreibung')->nullable();
            // [['bild' => '/img/…', 'alt' => '…'], …], das erste steht auf der Karte
            $table->json('fotos')->nullable();
            $table->string('link_url')->nullable();
            $table->string('link_text')->nullable();
            $table->json('uebersetzungen')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_items');
    }
};
