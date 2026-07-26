<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'));

/*
 * Interne Vorschau der Inhaltsmodule. Nicht Teil der Website, per noindex
 * ausgenommen. Wird entfernt, sobald die echten Seiten stehen.
 */
Route::get('/module-demo', function () {
    $manifest = json_decode(file_get_contents(base_path('docs/dokumente-manifest.json')), true);

    $dokumente = collect($manifest)
        ->filter(fn ($d) => in_array('/erwerbsminderungsrente/', $d['verlinkt_auf']))
        ->sortBy('titel')
        ->map(fn ($d) => [
            'titel' => $d['titel'],
            // Zeigt vorerst auf die Altseite. Nach dem Umzug: lokaler Pfad.
            'url' => 'https://kein-einzelfall.de'.$d['alt_url'],
            'bytes' => $d['bytes'],
        ])
        ->values()
        ->all();

    return view('module-demo', compact('dokumente'));
});
