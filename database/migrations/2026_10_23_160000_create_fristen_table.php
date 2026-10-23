<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fristen (KEV-152): Einträge der Fristenübersicht unter /fristen.
 *
 * Taddis Wunsch: „Inhalt muss erweiterbar und änderbar sein“. Deshalb keine
 * Textbausteine, sondern eigene Einträge, die der Verein im Panel unter
 * „Fristen“ anlegt und ändert; die Seite zeigt sie mit Suche, A–Z und
 * aufklappbaren Karten (Baustein `fristen`).
 *
 * Eine Zeile je Frist, die Sprachfassungen in `uebersetzungen` wie bei
 * Gruppen und Öffentlichkeitsarbeit (App\Models\Concerns\Uebersetzbar).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fristen', function (Blueprint $table) {
            $table->id();
            // Sprungziel: /fristen#widerspruch
            $table->string('slug')->unique();
            $table->string('titel');
            // In Kürze, steht auf der zugeklappten Karte: „Frist: grundsätzlich 1 Monat“
            $table->string('frist')->nullable();
            $table->json('schlagworte')->nullable();
            // Ausführlich, aus dem Editor im Panel (Absätze, Zwischenüberschriften, Listen)
            $table->longText('inhalt')->nullable();
            $table->json('uebersetzungen')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fristen');
    }
};
