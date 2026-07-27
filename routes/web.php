<?php

use App\Http\Controllers\AnfrageController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\SpracheSetzen;
use App\Models\Language;
use Illuminate\Support\Facades\Route;

/*
 * Die öffentlichen Routen stehen genau einmal hier und werden zweimal
 * registriert:
 *
 *   1. ohne Präfix, unter den bisherigen Namen  → die Standardsprache
 *   2. mit Präfix /{locale}, unter "sprache.…"  → alle weiteren Sprachen
 *
 * Deutsch behält damit Adresse *und* Routennamen unverändert — die 24 Seiten
 * und die 26 Weiterleitungen bleiben unangetastet. Der Helfer `sprachlink()`
 * wählt in den Views die passende Variante.
 *
 * Warum nicht ein einziger Satz Routen mit optionalem Präfix: Ein optionaler
 * Präfix-Parameter erzeugt für die Standardsprache Adressen mit doppeltem
 * Schrägstrich, und `URL::defaults()` würde Deutsch ein Präfix verpassen.
 * Beides bricht genau die Zusage, die wir dem Kunden gegeben haben.
 */
$oeffentlicheRouten = function () {

    Route::get('/', [PageController::class, 'start'])->name('start');

    /*
     * Kontaktformular. Steht vor der Sammelroute, sonst würde /{slug} greifen.
     * Das Tempolimit ist bewusst großzügig — es soll Massenversand bremsen, nicht
     * jemanden aussperren, der eine Nachricht noch einmal abschickt.
     */
    Route::post('/anfrage', [AnfrageController::class, 'store'])
        ->middleware('throttle:5,10')
        ->name('anfrage.senden');

    /*
     * Blog / Aktuelles.
     *
     * Suche und Kategoriefilter laufen über GET-Parameter statt über Livewire:
     * Jeder Stand hat eine eigene Adresse, die man teilen und als Lesezeichen
     * speichern kann und die eine Suchmaschine erfasst.
     */
    Route::get('/aktuelles', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/aktuelles/{slug}', [BlogController::class, 'show'])
        ->where('slug', '[a-z0-9-]+')
        ->name('blog.show');

    /*
     * Veranstaltungen. Ersetzt das WordPress-Plugin "The Events Calendar".
     *
     * /veranstaltungen war auf der Altseite eine Inhaltsseite — der Text bleibt
     * als Einleitung erhalten und wird über den Kalender gestellt.
     */
    Route::get('/veranstaltungen', [EventController::class, 'index'])->name('events.index');
    Route::get('/veranstaltungen/kalender.ics', [EventController::class, 'ical'])->name('events.ical');
    Route::get('/veranstaltungen/{slug}', [EventController::class, 'show'])
        ->where('slug', '[a-z0-9-]+')
        ->name('events.show');
    Route::get('/veranstaltungen/{slug}/kalender.ics', [EventController::class, 'icalEinzeln'])
        ->where('slug', '[a-z0-9-]+')
        ->name('events.ical.einzeln');

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
};

/*
 * Sprachfassungen zuerst. Müsste die Sammelroute /{slug} vorher stehen, würde
 * sie "/en" als deutschen Seiten-Slug lesen und die englische Startseite nie
 * erreicht. Welche Codes gültig sind, entscheidet die Datenbank (Middleware),
 * nicht dieses Muster — sonst bräuchte jede neue Sprache ein `route:clear`.
 */
Route::prefix('{locale}')
    ->where(['locale' => Language::ADRESS_MUSTER])
    ->middleware(SpracheSetzen::class)
    ->name('sprache.')
    ->group($oeffentlicheRouten);

/*
 * Interne Vorschau der Inhaltsmodule. Nicht Teil der Website, per noindex
 * ausgenommen. Wird entfernt, sobald die echten Seiten stehen.
 * Bewusst nur auf Deutsch — kein Kundeninhalt.
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
 * Suchmaschinen-Wegweiser. Kennt alle Sprachfassungen und verweist sie
 * gegenseitig über xhtml:link — ohne das zählt Google Übersetzungen als
 * doppelten Inhalt.
 */
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

/* Die Standardsprache: unveränderte Adressen, unveränderte Routennamen. */
Route::middleware(SpracheSetzen::class)->group($oeffentlicheRouten);

/*
 * Letzte Instanz: Adressen der WordPress-Altseite dauerhaft weiterleiten,
 * statt sie ins Leere laufen zu lassen.
 */
Route::fallback(RedirectController::class);
