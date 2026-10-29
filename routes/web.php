<?php

use App\Http\Controllers\AnfrageController;
use App\Http\Controllers\BeschwerdeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GruppeController;
use App\Http\Controllers\GruppenAnmeldungController;
use App\Http\Controllers\OeffentlichkeitsarbeitController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SucheController;
use App\Http\Middleware\SpracheSetzen;
use App\Models\Language;
use App\Support\Dokument;
use App\Support\Medien;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
        // Nur gegen Massenversand. Wie oft jemand eine Nachricht schicken
        // kann, regelt App\Support\Formularbremse, und zwar freundlich.
        ->middleware('throttle:30,10')
        ->name('anfrage.senden');

    /*
     * Formular auf /beschwerdemanagement (KEV-98): Kritik geht wie eine
     * Anfrage in den Verwaltungsbereich, eine Beschwerde über den Verein per
     * E-Mail an die unabhängige Ombudsstelle. Begründung im Controller.
     */
    Route::post('/beschwerde', [BeschwerdeController::class, 'store'])
        ->middleware('throttle:30,10')
        ->name('beschwerde.senden');

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
     * Eine Seite je Selbsthilfegruppe (KEV-73, Wunsch von Taddi). Unter
     * /selbsthilfegruppen/ und nicht flach: /buerokratie-labyrinth ist schon
     * die Seite zum gleichnamigen Vortragsabend.
     */
    /*
     * Anmeldung zu einer Selbsthilfegruppe (KEV-226). Tempolimit nur gegen
     * Massenversand, wie bei /anfrage; wie oft jemand sich anmelden kann,
     * regelt App\Support\Formularbremse.
     */
    Route::post('/selbsthilfegruppen/{slug}/anmeldung', [GruppenAnmeldungController::class, 'store'])
        ->middleware('throttle:30,10')
        ->where('slug', '[a-z0-9-]+')
        ->name('gruppen.anmelden');

    Route::get('/selbsthilfegruppen/{slug}', [GruppeController::class, 'show'])
        ->where('slug', '[a-z0-9-]+')
        ->name('gruppen.show');

    /*
     * Eine Seite je Messe, Petition, Presseartikel (KEV-134). Auf der
     * Übersicht /oeffentlichkeitsarbeit steht nur die Karte.
     */
    Route::get('/oeffentlichkeitsarbeit/{slug}', [OeffentlichkeitsarbeitController::class, 'show'])
        ->where('slug', '[a-z0-9-]+')
        ->name('oeffentlichkeitsarbeit.show');

    /*
     * Eine Seite je Arbeitsgruppe (KEV-84), wie bei den Selbsthilfegruppen.
     * Die Karte auf /arbeitsgruppen behält ihren Anker (#ag-01-ser), ältere
     * Links dorthin funktionieren weiter.
     */
    Route::get('/arbeitsgruppen/{slug}', [GruppeController::class, 'arbeitsgruppe'])
        ->where('slug', '[a-z0-9-]+')
        ->name('arbeitsgruppen.show');

    /*
     * Suche (KEV-23). Steht vor der Sammelroute, sonst griffe /{slug}.
     *
     * GET, damit die Anfrage in der Adresszeile steht und weitergegeben werden
     * kann. Begründung und was bewusst nicht protokolliert wird: SucheController.
     */
    Route::get('/suche', SucheController::class)->name('suche');

    /*
     * Das Glossar hat seit KEV-155 keine eigene Route mehr: /glossar ist eine
     * Seite mit dem Baustein „Glossar“, wie /behoerden-und-bescheide. Jeder
     * Begriff bleibt einzeln verlinkbar („siehe /glossar#gdb“).
     */

    /*
     * Leichte Sprache.
     *
     * Eine eigene Adresse und kein aufklappbarer Kasten: Nur so ist die Fassung
     * verlinkbar, als Lesezeichen speicherbar und für Suchmaschinen auffindbar.
     * BITV 2.0 § 4 erwartet genau das — einen eigenen, von der Startseite aus
     * erreichbaren Bereich.
     *
     * Sie ist *keine* Sprache: Das lang-Attribut bleibt `de`, es gibt kein
     * hreflang dafür. Begründung in der Migration `add_fassung_to_pages_table`.
     */
    Route::get('/leichte-sprache/{slug}', [PageController::class, 'leichteSprache'])
        ->where('slug', '[a-z0-9-]+')
        ->name('leichte-sprache');

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
 * Dokument-Adressen der WordPress-Altseite.
 *
 * Sie sind indexiert, extern verlinkt und stehen in PDFs, die der Verein
 * verschickt hat — sie duerfen nicht sterben. Eine Regel statt 121 Zeilen in
 * der Weiterleitungstabelle: Dort waeren sie nur Rauschen fuer die Redaktion.
 *
 * Kein Schrägstrich-Wildcard im Ziel ohne Pruefung: Der Pfad kommt aus der
 * Adresszeile und wird gegen den Dokumentordner geprueft, bevor irgendetwas
 * ausgeliefert wird.
 */
Route::get('/wp-content/uploads/{pfad}', function (string $pfad) {
    $ziel = Dokument::pfad('/wp-content/uploads/'.$pfad);

    abort_unless(Dokument::vorhanden('/wp-content/uploads/'.$pfad), 404);

    return redirect($ziel, 301);
})->where('pfad', '.*')->name('altdokument');

/*
 * Im Panel hochgeladene Bilder und Dokumente (App\Support\Medien).
 *
 * Normalerweise liefert der Webserver sie direkt aus, über die Verknüpfung
 * public/storage. Fehlt die (auf dem Server ist das ein Schritt im Image,
 * den wir nicht sehen), käme hier eine 404, und jedes hochgeladene Bild
 * wäre kaputt. Dann springt diese Route ein.
 *
 * Nur Dateien in storage/app/public: Medien::dateipfad() weist „..“ ab, und
 * realpath() hält auch Verknüpfungen in den Ordner hinein draußen. Die
 * Dateinamen sind eindeutig, deshalb lange zwischenspeichern.
 */
Route::get('/storage/{pfad}', function (string $pfad) {
    $datei = Medien::dateipfad('/storage/'.$pfad);
    $echt = $datei ? realpath($datei) : false;
    $ordner = realpath(Storage::disk('public')->path(''));

    abort_unless($echt && $ordner && str_starts_with($echt, $ordner.DIRECTORY_SEPARATOR) && is_file($echt), 404);

    return response()->file($echt, [
        'Cache-Control' => 'public, max-age=31536000, immutable',
        'X-Content-Type-Options' => 'nosniff',
    ]);
})->where('pfad', '.*')->name('medien');

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
