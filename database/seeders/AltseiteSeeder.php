<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Redirect;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Pflegt den Bestand der Altseite ein — aus docs/altseite-inhalt.json,
 * erzeugt von `php artisan altseite:holen`.
 *
 * Kein Text wird hier umformuliert. Vertraglich gilt: Inhalte stellt der Verein,
 * wir pflegen sie nur ein.
 */
class AltseiteSeeder extends Seeder
{
    /**
     * Slug-Zuordnung alt → neu.
     *
     * Grundsatz: URLs bleiben gleich, sonst verliert die Seite ihre Positionen
     * bei Google. Einzige Ausnahme ist das Impressum — "impressum-2" ist ein
     * WordPress-Unfall (der Slug "impressum" war belegt), den wir nicht mit
     * in die neue Seite schleppen.
     */
    private const SLUG_ANPASSUNG = [
        'impressum-2' => 'impressum',
    ];

    /** Diese Seiten haben eine eigene Route und werden nicht als Page angelegt. */
    private const EIGENE_ROUTE = ['/'];

    public function run(): void
    {
        $datei = base_path('docs/altseite-inhalt.json');

        if (! file_exists($datei)) {
            $this->command->error('docs/altseite-inhalt.json fehlt — erst `php artisan altseite:holen` ausführen.');

            return;
        }

        $inhalt = json_decode(file_get_contents($datei), true);
        $dokumente = collect(json_decode(file_get_contents(base_path('docs/dokumente-manifest.json')), true));

        $angelegt = 0;

        foreach ($inhalt as $altPfad => $daten) {
            if (in_array($altPfad, self::EIGENE_ROUTE, true)) {
                continue;
            }

            $altSlug = trim($altPfad, '/');
            $slug = self::SLUG_ANPASSUNG[$altSlug] ?? $altSlug;

            $page = Page::updateOrCreate(['slug' => $slug], [
                'titel' => $daten['titel'] ?: Str::headline($slug),
                'meta_title' => $daten['meta_title'],
                'meta_description' => $daten['meta_description'],
                'published_at' => now(),
            ]);

            $page->blocks()->delete();
            $position = 0;

            foreach ($daten['bloecke'] as $block) {
                // Blöcke ohne Fließtext sind Layout-Reste aus Elementor
                if (empty($block['absaetze'])) {
                    continue;
                }

                $page->blocks()->create([
                    'typ' => 'text',
                    'position' => $position++,
                    'data' => [
                        'titel' => $block['titel'],
                        'absaetze' => $block['absaetze'],
                    ],
                ]);
            }

            // Verlinkte Dokumente als eigener Block ans Seitenende.
            // Titel und Größe kommen aus dem Manifest — der Linktext der Altseite
            // ist als Bezeichnung deutlich brauchbarer als der Dateiname.
            if ($daten['pdfs']) {
                $liste = collect($daten['pdfs'])->map(function ($pdf) use ($dokumente) {
                    $bekannt = $dokumente->firstWhere('alt_url', $pdf['url']);

                    return [
                        'titel' => $pdf['titel'] ?: ($bekannt['titel'] ?? basename($pdf['url'])),
                        'url' => $pdf['url'],
                        'bytes' => $bekannt['bytes'] ?? null,
                    ];
                })->unique('url')->values()->all();

                $page->blocks()->create([
                    'typ' => 'download_list',
                    'position' => $position++,
                    'data' => ['titel' => 'Dokumente zum Herunterladen', 'dokumente' => $liste],
                ]);
            }

            // WordPress liefert jede Seite mit Schrägstrich am Ende aus.
            // Ohne diese Weiterleitung wäre jede indexierte URL ein 404.
            Redirect::updateOrCreate(
                ['von' => $altSlug.'/'],
                ['nach' => '/'.$slug, 'status' => 301, 'notiz' => 'WordPress-Slash']
            );

            if ($slug !== $altSlug) {
                Redirect::updateOrCreate(
                    ['von' => $altSlug],
                    ['nach' => '/'.$slug, 'status' => 301, 'notiz' => 'Slug bereinigt']
                );
            }

            $angelegt++;
        }

        // Die beiden auf der Altseite kaputten Links (liefern dort 404).
        // Extern verlinkt oder gebookmarkt können sie trotzdem sein.
        foreach ([
            'selbsthilfegruppen-2' => '/selbsthilfegruppen',
            'kontaktformular' => '/anfragen',
        ] as $von => $nach) {
            Redirect::updateOrCreate(
                ['von' => $von],
                ['nach' => $nach, 'status' => 301, 'notiz' => 'War auf der Altseite ein 404']
            );
        }

        $this->command->info("{$angelegt} Seiten und ".Redirect::count().' Weiterleitungen eingepflegt.');
    }
}
