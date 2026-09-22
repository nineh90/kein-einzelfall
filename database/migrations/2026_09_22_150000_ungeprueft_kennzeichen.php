<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kennzeichen für Seiten, die wir vorbereitet haben und die der Verein noch
 * gegenlesen muss.
 *
 * Vertraglich stellt der Verein die Inhalte. Wo wir ausnahmsweise Text
 * schreiben — bei den Seiten aus Abschnitt 6.2 des Strukturpapiers, weil sie
 * geltendes Recht beschreiben und nicht den Verein —, muss das für jede
 * Besucherin sichtbar sein.
 *
 * Warum ein Feld und kein Baustein: Der Vermerk ist keine Aussage der Seite,
 * sondern eine über sie. Als Baustein stünde er in der Bausteinliste zwischen
 * echten Inhalten, liefe in den Flächenwechsel hinein (ein Hinweis-Kasten
 * bleibt auf der Fläche des Textes davor — ganz oben gibt es keinen) und
 * müsste nach der Freigabe von Hand gelöscht werden. Als Häkchen ist es ein
 * Klick.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $tabelle) {
            $tabelle->boolean('ungeprueft')->default(false)->after('noindex');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $tabelle) {
            $tabelle->dropColumn('ungeprueft');
        });
    }
};
