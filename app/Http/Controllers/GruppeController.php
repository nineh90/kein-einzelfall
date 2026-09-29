<?php

namespace App\Http\Controllers;

use App\Models\Group;

/**
 * Die Seite einer Selbsthilfegruppe (KEV-73).
 *
 * Auf der Übersicht steht nur die Karte. Taddis Texte je Gruppe mit Wann,
 * Wo, Kosten und Kontakt sind dafür zu lang, und eine eigene Adresse lässt
 * sich weitergeben („schau mal hier“). Gruppen in Planung haben auch eine,
 * nur ohne Termin.
 */
class GruppeController extends Controller
{
    public function show(string $slug)
    {
        $gruppe = Group::veroeffentlicht()
            ->vomTyp('selbsthilfe')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('gruppen.show', ['gruppe' => $gruppe]);
    }
}
