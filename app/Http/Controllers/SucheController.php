<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Support\Suche;
use Illuminate\Http\Request;

/**
 * Suche (KEV-23).
 *
 * ── Warum GET und kein POST ────────────────────────────────────────────────
 *
 * Die Anfrage steht damit in der Adresszeile und lässt sich weitergeben,
 * neu laden und als Lesezeichen sichern. Für eine Beratungsstelle, die
 * jemandem „schau mal hier" schickt, ist das der Unterschied zwischen einem
 * Link und einer Anleitung.
 *
 * Der Preis: Die Anfrage landet in der Adresszeile und damit im Verlauf des
 * Browsers. Das ist bei dieser Zielgruppe nicht nichts — und der Notausgang
 * kann es NICHT heilen: Aus einer Webseite heraus lässt sich der Verlauf nicht
 * löschen, `location.replace()` ersetzt nur den aktuellen Eintrag (siehe
 * exit-script.blade.php, das genau das offenlegt).
 *
 * Deshalb steht auf der Suchseite ein Hinweis darauf, mit Verweis auf die
 * Anleitung unter /barrierefreiheit. Ein Sicherheitsversprechen ohne Deckung
 * wäre hier schlimmer als gar keins.
 *
 * ── Was hier bewusst NICHT passiert ────────────────────────────────────────
 *
 * Kein Protokoll der Suchanfragen. Keine Statistik, kein „meistgesucht", kein
 * Zähler. Was jemand hier eintippt, verrät mehr über ihn als jede andere Zeile
 * dieser Website — es wäre die eine Stelle, an der aus Ratsuchenden Datensätze
 * werden. Wenn der Verein später wissen will, was gesucht wird, gehört das
 * ausdrücklich beauftragt und in die Datenschutzerklärung, nicht nebenbei
 * eingebaut.
 */
class SucheController extends Controller
{
    /** Länger tippt niemand eine Suchanfrage — und es begrenzt den Aufwand. */
    private const HOECHSTENS_ZEICHEN = 200;

    public function __invoke(Request $request, Suche $suche)
    {
        $anfrage = trim((string) $request->query('q', ''));

        if (mb_strlen($anfrage) > self::HOECHSTENS_ZEICHEN) {
            $anfrage = mb_substr($anfrage, 0, self::HOECHSTENS_ZEICHEN);
        }

        $ergebnis = $anfrage === ''
            ? ['treffer' => [], 'krise' => false, 'begriffe' => []]
            : $suche->suchen($anfrage, Language::aktuell()->code);

        return view('suche.index', [
            'anfrage' => $anfrage,
            'treffer' => $ergebnis['treffer'],
            'krise' => $ergebnis['krise'],
        ]);
    }
}
