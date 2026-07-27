<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Page;
use App\Models\Redirect;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    /**
     * Startseite.
     *
     * Noch eine feste Blade-Datei und kein Datensatz — die Texte stammen wörtlich
     * vom Verein und wandern später in `pages`. Bis dahin greift für andere
     * Sprachen derselbe sichtbare Rückfall wie bei jeder anderen Seite.
     */
    public function start()
    {
        return view('home', [
            'ersatzsprache' => Language::aktuell()->istStandard() ? null : Language::standard(),
        ]);
    }

    public function show(string $slug)
    {
        $sprache = Language::aktuell();

        $page = $this->seite($sprache->code, $slug);

        if ($page) {
            return view('page', ['page' => $page, 'ersatzsprache' => null]);
        }

        /*
         * Fehlende Übersetzung: dieselbe Adresse in der Rückfallsprache zeigen,
         * sichtbar gekennzeichnet.
         *
         * Das ist Absicht und kein Notbehelf. Es geht um Opferrechte, Fristen
         * und Notfallnummern — eine Seite, die still verschwindet, ist für diese
         * Zielgruppe schlechter als eine Seite in einer anderen Sprache mit dem
         * Hinweis, dass sie noch nicht übersetzt ist.
         */
        if ($rueckfall = $sprache->fallback()) {
            if ($page = $this->seite($rueckfall->code, $slug)) {
                return view('page', ['page' => $page, 'ersatzsprache' => $rueckfall]);
            }
        }

        // Kein Treffer heißt nicht automatisch 404: Adressen wie /impressum-2
        // sehen aus wie ein gültiger Slug, sind aber Altlasten der WordPress-Seite
        // und müssen dauerhaft weitergeleitet werden.
        return $this->weiterleitenOder404($slug, $sprache);
    }

    private function seite(string $locale, string $slug): ?Page
    {
        return Page::veroeffentlicht()
            ->with('blocks')
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->first();
    }

    private function weiterleitenOder404(string $pfad, Language $sprache)
    {
        $regel = Redirect::whereIn('von', [$pfad, $pfad.'/'])->first();

        if (! $regel) {
            throw new NotFoundHttpException;
        }

        $regel->benutzt();

        // Die Weiterleitungen stammen aus der deutschen Altseite und zeigen auf
        // Pfade ohne Präfix. Innerhalb einer Sprachfassung darf eine
        // Weiterleitung die Sprache nicht abwerfen — sonst landet jemand
        // mitten im Lesen unvermittelt auf Deutsch.
        $ziel = str_starts_with($regel->nach, '/')
            ? $sprache->pfad($regel->nach)
            : $regel->nach;

        return redirect($ziel, $regel->status);
    }
}
