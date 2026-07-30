<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Leichte Sprache als Fassung einer Seite — nicht als Sprache.
 *
 * Diese Unterscheidung ist die ganze Entscheidung, deshalb hier ausgeschrieben.
 *
 * Warum KEINE Sprache in der `languages`-Tabelle:
 *
 *  - Leichte Sprache *ist* Deutsch. Das lang-Attribut muss `de` bleiben,
 *    sonst liest eine Vorlesehilfe den Text falsch aus. Ein Tag wie
 *    `de-x-leicht` ist ein Private-Use-Subtag nach BCP 47: Assistenztechnik
 *    ignoriert den Zusatz ohnehin und fällt auf `de` zurück.
 *  - Google akzeptiert in `hreflang` keine Private-Use-Subtags. `de-x-leicht`
 *    würde in der Search Console als Fehler auftauchen — und „SEO darf nicht
 *    schlechter werden“ ist ausdrücklicher Kundenwunsch.
 *  - Sie hätte im Sprachumschalter gestanden, zwischen „English“ und
 *    „Русский“. Wer Leichte Sprache braucht, sucht sie dort nicht.
 *
 * Warum aber auch kein blosser Baustein mehr:
 *
 *  - Als Baustein innerhalb einer Seite hat sie keine eigene Adresse. Sie ist
 *    damit nicht verlinkbar, nicht als Lesezeichen zu speichern und für
 *    Suchmaschinen nicht auffindbar.
 *  - BITV 2.0 § 4 erwartet Leichte Sprache als eigenen, von der Startseite aus
 *    erreichbaren Bereich. Ein aufklappbarer Kasten irgendwo auf Seite drei
 *    erfüllt das nicht.
 *
 * Also: eigene Zeile, eigener Slug, eigene Adresse unter /leichte-sprache/…,
 * dieselbe Übersetzungsgruppe wie die Standardfassung. Damit finden beide
 * Fassungen automatisch gegenseitig zueinander.
 *
 * Der Baustein-Typ `leichte_sprache` bleibt bestehen: Eine kurze Zusammenfassung
 * *innerhalb* einer schweren Seite ist etwas anderes als eine vollständige
 * Fassung, und beides ist üblich.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('fassung', 32)->default(Page::FASSUNG_STANDARD)->after('locale');
        });

        Schema::table('pages', function (Blueprint $table) {
            // Der Slug ist ab jetzt innerhalb von Sprache *und* Fassung
            // eindeutig. /verein und /leichte-sprache/verein sind zwei Seiten.
            $table->dropUnique(['locale', 'slug']);
            $table->unique(['locale', 'fassung', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique(['locale', 'fassung', 'slug']);
            $table->unique(['locale', 'slug']);
            $table->dropColumn('fassung');
        });
    }
};
