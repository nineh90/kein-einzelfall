<?php

use App\Models\Page;
use App\Support\Spenden;
use Illuminate\Database\Migrations\Migration;

/**
 * Stellt /spenden vom Textblock der Altseite auf den Spenden-Baustein um
 * (KEV-5).
 *
 * Die Altseite hatte PayPal und zwei betterplace-Projekte. Der Import hat
 * Konto und PayPal als Fliesstext übernommen („IBAN: DE79 …“) und die
 * betterplace-iframes gar nicht — sie luden dort ungefragt, das wollten wir
 * nicht kopieren. Jetzt gibt es beides als Baustein: Konto mit QR-Code,
 * PayPal als Link, betterplace nach Zustimmung, Spendenbescheinigung.
 *
 * Alle Sprachfassungen: Die englische ist ein Klon mit denselben Blöcken.
 * Den Text der Spendenbescheinigung nimmt der Baustein aus dem vorhandenen
 * Block — in der Sprache, in der er dort steht.
 *
 * Nur Seiten, die noch aussehen wie der Altbestand (Kontoblock mit dieser
 * IBAN vorhanden, noch kein Spenden-Baustein). Alles andere hat jemand
 * bearbeitet, und das bleibt so.
 */
return new class extends Migration
{
    public function up(): void
    {
        // foreach, nicht ->each(): Der Rückgabewert false („war schon
        // umgestellt“) würde each() abbrechen — und die zweite Sprachfassung
        // bliebe unangetastet.
        foreach (Page::where('slug', 'spenden')->get() as $seite) {
            Spenden::spendenseiteUmstellen($seite);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
