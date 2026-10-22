<?php

namespace App\Http\Controllers;

use App\Models\GlossaryTerm;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Http\Request;

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
    /** Länger tippt niemand eine Abkürzung oder einen Begriff. */
    private const HOECHSTENS_ZEICHEN = 100;

    public function index(Request $request)
    {
        $sprache = Language::aktuell();

        /*
         * Suche im Glossar (KEV-153). Über GET, damit sie ohne JavaScript
         * funktioniert und die Suche der Website direkt hierher verlinken
         * kann (/glossar?suche=SGB). Mit JavaScript filtert die Liste schon
         * beim Tippen (resources/js/glossar.js), ohne die Adresse zu ändern.
         */
        $suche = mb_substr(trim((string) $request->query('suche', '')), 0, self::HOECHSTENS_ZEICHEN);
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
            'suche' => $suche,

            /*
             * Optionale Einleitung aus dem Panel — dasselbe Muster wie bei
             * /veranstaltungen. Gibt es die Seite nicht, steht eben nur die
             * Liste da. Der Text gehört dem Verein, wir erfinden hier keinen.
             *
             * Seit KEV-153 kommen auch Titel, Titelbild und Beschreibung von
             * dort, wenn die Seite sie trägt.
             */
            'einleitung' => Page::veroeffentlicht()
                ->with('blocks')
                ->where('locale', $sprache->code)
                ->where('slug', 'glossar')
                ->first(),
        ]);
    }
}
