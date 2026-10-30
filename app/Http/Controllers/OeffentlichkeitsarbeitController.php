<?php

namespace App\Http\Controllers;

use App\Models\OutreachItem;

/**
 * Die Seite eines Eintrags der Öffentlichkeitsarbeit (KEV-134): eine Messe,
 * später Petitionen und Presse.
 *
 * Auf /oeffentlichkeitsarbeit steht nur die kleine Karte. Der ganze Text und
 * alle Fotos stehen hier, unter einer eigenen Adresse, die sich weitergeben
 * lässt.
 */
class OeffentlichkeitsarbeitController extends Controller
{
    public function show(string $slug)
    {
        $eintrag = OutreachItem::veroeffentlicht()->where('slug', $slug)->firstOrFail();

        $weitere = OutreachItem::veroeffentlicht()
            ->whereKeyNot($eintrag->getKey())
            ->neuesteZuerst()
            ->limit(5)
            ->get();

        // Übersetzt wie die Gruppen: auch auf Englisch in den Index (layouts/app)
        return view('oeffentlichkeitsarbeit.show', compact('eintrag', 'weitere') + ['uebersetzt' => true]);
    }
}
