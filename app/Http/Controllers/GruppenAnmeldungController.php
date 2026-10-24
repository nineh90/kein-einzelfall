<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnfrageRequest;
use App\Http\Requests\GruppenAnmeldungRequest;
use App\Models\Group;
use App\Support\Formularbremse;
use App\Support\GruppenAnmeldung;

/**
 * Das Anmeldeformular auf der Seite einer Selbsthilfegruppe (KEV-226).
 * Ablauf und Gründe in App\Support\GruppenAnmeldung.
 */
class GruppenAnmeldungController extends Controller
{
    public function store(GruppenAnmeldungRequest $request, string $slug)
    {
        $gruppe = Group::veroeffentlicht()->vomTyp('selbsthilfe')->where('slug', $slug)->firstOrFail();

        // Gruppe läuft (noch) nicht: zurück, der Hinweis steht schon auf der Seite.
        if (! $gruppe->anmeldungMoeglich()) {
            return redirect(AnfrageRequest::zurueck($request));
        }

        if (Formularbremse::gesperrt($request, 'gruppenanmeldung')) {
            return Formularbremse::zurueckZumFormular($request);
        }

        $ergebnis = GruppenAnmeldung::annehmen(
            $gruppe,
            $request->string('email')->trim()->value(),
            $request->filled('name') ? $request->string('name')->trim()->value() : null,
        );
        Formularbremse::zaehlen($request, 'gruppenanmeldung');

        return redirect(AnfrageRequest::zurueck($request))
            ->with('versendet_von', $request->input('formular'))
            ->with('anmeldung_ergebnis', $ergebnis);
    }
}
