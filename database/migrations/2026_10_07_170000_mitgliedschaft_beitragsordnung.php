<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Mitgliedschaft (KEV-95): neuer Abschnitt „Beitrags- und Mitgliederordnung“
 * hinter „Antrag auf Mitgliedschaft“, direkt über der Dokumentenliste. Dazu
 * „Hlfe zum Ausfüllen“ in der Liste korrigiert.
 *
 * Fügt nur ein, wo der Abschnitt fehlt und der Bezugsabschnitt noch da ist.
 */
return new class extends Migration
{
    private const SLUG = 'mitgliedschaft';

    public function up(): void
    {
        $neu = AltseiteSeeder::NEUE_ABSCHNITTE[self::SLUG][0];

        foreach (Page::where('slug', self::SLUG)->where('locale', 'de')->get() as $seite) {
            $bloecke = $seite->blocks()->orderBy('position')->get();
            $davor = $bloecke->first(fn ($b) => $b->typ === 'text' && ($b->data['titel'] ?? null) === $neu['nach']);
            $vorhanden = $bloecke->contains(fn ($b) => ($b->data['titel'] ?? null) === $neu['titel'] && $b->typ === 'text');

            if ($davor && ! $vorhanden) {
                // Platz schaffen: alles dahinter rückt eins weiter.
                $seite->blocks()->where('position', '>', $davor->position)->increment('position');
                $seite->blocks()->create([
                    'typ' => 'text',
                    'position' => $davor->position + 1,
                    'data' => ['titel' => $neu['titel'], 'absaetze' => $neu['absaetze']],
                ]);
            }

            foreach ($seite->blocks()->where('typ', 'download_list')->get() as $liste) {
                $dokumente = array_map(function ($d) {
                    $d['titel'] = AltseiteSeeder::DOKUMENT_KORREKTUREN[$d['titel'] ?? ''] ?? ($d['titel'] ?? null);

                    return $d;
                }, $liste->data['dokumente'] ?? []);

                if ($dokumente !== ($liste->data['dokumente'] ?? [])) {
                    $liste->update(['data' => array_replace($liste->data, ['dokumente' => $dokumente])]);
                }
            }
        }
    }

    public function down(): void
    {
        $neu = AltseiteSeeder::NEUE_ABSCHNITTE[self::SLUG][0];

        foreach (Page::where('slug', self::SLUG)->where('locale', 'de')->get() as $seite) {
            $block = $seite->blocks()->where('typ', 'text')->get()
                ->first(fn ($b) => ($b->data['titel'] ?? null) === $neu['titel'] && ($b->data['absaetze'] ?? null) === $neu['absaetze']);

            if ($block) {
                $seite->blocks()->where('position', '>', $block->position)->decrement('position');
                $block->delete();
            }
        }
    }
};
