<?php

use App\Http\Controllers\AnfrageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('start');

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
            'url' => 'https://kein-einzelfall.de'.$d['alt_url'],
            'bytes' => $d['bytes'],
        ])
        ->values()
        ->all();

    return view('module-demo', compact('dokumente'));
});

/*
 * Kontaktformular. Steht vor der Sammelroute, sonst würde /{slug} greifen.
 * Das Tempolimit ist bewusst großzügig — es soll Massenversand bremsen, nicht
 * jemanden aussperren, der eine Nachricht noch einmal abschickt.
 */
Route::post('/anfrage', [AnfrageController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('anfrage.senden');

/*
 * Inhaltsseiten aus der Datenbank. Steht bewusst am Ende: die Route fängt
 * alles ab, was oben nicht gepasst hat.
 *
 * Der Slug lässt keine Schrägstriche zu — die Seitenstruktur ist flach, so wie
 * auf der Altseite. Das ist eine SEO-Entscheidung: gleiche URLs, keine Umzüge.
 */
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('page');

/*
 * Letzte Instanz: Adressen der WordPress-Altseite dauerhaft weiterleiten,
 * statt sie ins Leere laufen zu lassen.
 */
Route::fallback(RedirectController::class);
