<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Database\Seeders\AbkuerzungenSexuellerKontextSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Abkürzungen im sexuellen Kontext (KEV-256): „… dessen Begriffe einem noch
 * nicht vertraut sind“ ohne „noch“, dazu die Unterzeile „Kürzel verstehen,
 * benennen und darüber sprechen.“ und die grüne Überschrift „Was hinter den
 * Abkürzungen in Sexarbeit und Beratung steckt“ über der Einleitung.
 *
 * Die Einleitung nur, wo noch der alte Wortlaut steht, die Überschrift nur
 * in einen Abschnitt ohne Überschrift, die Unterzeile nur, wo keine steht.
 * Danach die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', AbkuerzungenSexuellerKontextSeeder::SLUG)->where('locale', 'de')->first();

        if (! $seite) {
            return;
        }

        $einleitung = $seite->blocks()->where('typ', 'text')->orderBy('position')->first();

        if ($einleitung) {
            $data = $einleitung->data;

            if (($data['absaetze'] ?? null) === AbkuerzungenSexuellerKontextSeeder::ABSAETZE_BIS_25_10) {
                $data['absaetze'] = AbkuerzungenSexuellerKontextSeeder::ABSAETZE;
            }

            if (blank($data['titel'] ?? null)) {
                // Überschrift vor den Absätzen, in der Reihenfolge des Formulars
                $data = ['titel' => AbkuerzungenSexuellerKontextSeeder::TEXT_TITEL] + $data;
            }

            if ($data !== $einleitung->data) {
                $einleitung->update(['data' => $data]);
            }
        }

        Titelbilder::setzen();

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
