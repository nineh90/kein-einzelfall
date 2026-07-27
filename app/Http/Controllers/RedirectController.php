<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Letzte Instanz vor dem 404: prüft, ob die angefragte Adresse von der
 * WordPress-Altseite stammt, und leitet dauerhaft weiter.
 *
 * Bewusst als Fallback-Route und nicht als Middleware — Laravel wandelt eine
 * nicht gefundene Route außerhalb der Middleware-Kette in eine Antwort um,
 * eine Middleware bekäme den 404 also gar nicht zu sehen.
 */
class RedirectController extends Controller
{
    public function __invoke(Request $request)
    {
        $pfad = trim($request->path(), '/');

        // Mit und ohne abschließenden Schrägstrich nachschlagen: WordPress hat
        // /verein/ ausgeliefert, verlinkt und gebookmarkt wird beides.
        $regel = Redirect::whereIn('von', [$pfad, $pfad.'/'])->first();

        if (! $regel) {
            throw new NotFoundHttpException;
        }

        $regel->benutzt();

        return redirect($regel->nach, $regel->status);
    }
}
