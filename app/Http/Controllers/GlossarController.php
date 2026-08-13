<?php

namespace App\Http\Controllers;

use App\Models\GlossaryTerm;
use App\Models\Language;
use App\Models\Page;

/**
 * Glossar — Abkürzungen und Fachbegriffe.
 *
 * Wunsch aus der Besprechung vom 02.08.2026. Für diese Zielgruppe ist das kein
 * Nachschlagewerk, sondern Grundausstattung: Wer einen Bescheid mit „GdB 50“
 * und „SGB XIV“ in der Hand hält, muss das lesen können, ohne jemanden fragen
 * zu müssen.
 */
class GlossarController extends Controller
{
    public function index()
    {
        $sprache = Language::aktuell();
        $ersatzsprache = null;

        $gruppen = GlossaryTerm::nachBuchstaben($sprache->code);

        /*
         * Sichtbarer Rückfall auf die Standardsprache — dieselbe Entscheidung
         * wie bei den Inhaltsseiten, und hier aus einem zusätzlichen Grund:
         * Die Begriffe stammen aus deutschen Gesetzen. Wer sie auf Russisch
         * sucht und nichts findet, braucht trotzdem die deutsche Abkürzung,
         * denn genau die steht in seinem Bescheid.
         */
        if ($gruppen->isEmpty() && $rueckfall = $sprache->fallback()) {
            $ersatz = GlossaryTerm::nachBuchstaben($rueckfall->code);

            if ($ersatz->isNotEmpty()) {
                $gruppen = $ersatz;
                $ersatzsprache = $rueckfall;
            }
        }

        return view('glossar.index', [
            'gruppen' => $gruppen,
            'ersatzsprache' => $ersatzsprache,

            /*
             * Optionale Einleitung aus dem Panel — dasselbe Muster wie bei
             * /veranstaltungen. Gibt es die Seite nicht, steht eben nur die
             * Liste da. Der Text gehört dem Verein, wir erfinden hier keinen.
             */
            'einleitung' => Page::veroeffentlicht()
                ->with('blocks')
                ->where('locale', $sprache->code)
                ->where('slug', 'glossar')
                ->first(),
        ]);
    }
}
