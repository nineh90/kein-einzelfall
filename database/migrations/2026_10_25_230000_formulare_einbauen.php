<?php

use App\Models\Page;
use Database\Seeders\FormulareSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Formulare (KEV-250): Taddis Formulare in vier Listen, darüber unsere
 * Anleitung. Nur solange die Seite noch im Zustand von KEV-150 ist (Text und
 * eine leere Liste); hat der Verein schon Formulare hochgeladen, bleibt alles
 * stehen. Danach für Suchmaschinen offen und die englische Fassung neu.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::where('slug', FormulareSeeder::SLUG)->where('locale', 'de')->first();

        if (! $seite) {
            return;
        }

        $bloecke = $seite->blocks()->orderBy('position')->get();
        $leer = $bloecke->count() === 2
            && $bloecke[0]->typ === 'text'
            && $bloecke[1]->typ === 'download_list'
            && empty($bloecke[1]->data['dokumente'] ?? []);

        if ($leer) {
            $einleitung = $bloecke[0];
            $bloecke[1]->delete();

            foreach (array_slice(FormulareSeeder::bausteine(), 1) as $i => $baustein) {
                $seite->blocks()->create($baustein + ['position' => $i + 1]);
            }

            $einleitung->update(['position' => 0]);
            $seite->update(['noindex' => false]);
        }

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts, wie bei den übrigen Inhalts-Migrationen.
    }
};
