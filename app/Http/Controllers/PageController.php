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
     * Seit sie ein Datensatz ist, gilt hier dieselbe Mechanik wie für jede
     * andere Seite — inklusive des sichtbaren Rückfalls, wenn es die
     * Sprachfassung noch nicht gibt. Eigen ist nur die Ansicht: Die Startseite
     * trägt ihre Überschrift im Aufmacher und braucht weder Seitenkopf noch
     * Brotkrumen.
     *
     * Fehlt der Datensatz, ist das ein 404 wie überall sonst. Bewusst kein
     * stiller Rückfall auf fest verdrahtete Texte — der gäbe der Startseite auf
     * Dauer wieder zwei Quellen, und genau das war der Zustand vorher.
     */
    public function start()
    {
        $sprache = Language::aktuell();

        if ($page = $this->startseite($sprache->code)) {
            return view('home', ['page' => $page, 'ersatzsprache' => null]);
        }

        if ($rueckfall = $sprache->fallback()) {
            if ($page = $this->startseite($rueckfall->code)) {
                return view('home', ['page' => $page, 'ersatzsprache' => $rueckfall]);
            }
        }

        throw new NotFoundHttpException;
    }

    public function show(string $slug)
    {
        $sprache = Language::aktuell();

        /*
         * Die Startseite wohnt unter `/`. Ihr Slug bleibt trotzdem erreichbar —
         * als Weiterleitung, nicht als zweite Adresse: Derselbe Inhalt unter
         * `/` und `/startseite` wäre genau der doppelte Inhalt, den zu vermeiden
         * dem Kunden zugesagt ist.
         */
        if ($slug === Page::STARTSEITE_SLUG) {
            return redirect($sprache->pfad('/'), 301);
        }

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

    /**
     * Seite in Leichter Sprache.
     *
     * Eigene Adresse statt aufklappbarer Kasten: Nur so ist sie verlinkbar,
     * als Lesezeichen speicherbar und auffindbar. Kein Rückfall auf die schwere
     * Fassung — wer hier landet, braucht Leichte Sprache. Ihm stattdessen den
     * schweren Text zu zeigen wäre schlechter als ein ehrliches 404 mit den
     * Auswegen der Fehlerseite.
     */
    public function leichteSprache(string $slug)
    {
        $sprache = Language::aktuell();

        $page = $this->seite($sprache->code, $slug, Page::FASSUNG_LEICHTE_SPRACHE);

        if (! $page) {
            throw new NotFoundHttpException;
        }

        return view('page', ['page' => $page, 'ersatzsprache' => null]);
    }

    private function startseite(string $locale): ?Page
    {
        return $this->seite($locale, Page::STARTSEITE_SLUG);
    }

    private function seite(
        string $locale,
        string $slug,
        string $fassung = Page::FASSUNG_STANDARD,
    ): ?Page {
        return Page::veroeffentlicht()
            ->with('blocks')
            ->where('locale', $locale)
            ->where('fassung', $fassung)
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
