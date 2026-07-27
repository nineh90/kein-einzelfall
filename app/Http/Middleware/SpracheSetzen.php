<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Ermittelt die Sprache eines Aufrufs aus dem Adresspräfix.
 *
 * Die Standardsprache hat kein Präfix. Das ist die wichtigste Einzelentscheidung
 * der Mehrsprachigkeit: die 24 bestehenden Adressen und die 26 Weiterleitungen
 * bleiben unverändert, und „SEO darf nicht schlechter werden" bleibt eingehalten.
 *
 * Bewusst wird hier gegen die Datenbank geprüft und nicht über ein Routen-Muster:
 * Der Verein legt Sprachen im Panel an. Stünde die Liste im Routen-Muster, müsste
 * nach jeder neuen Sprache jemand `route:clear` aufrufen — das ist niemandem
 * zumutbar, der kein Terminal hat.
 */
class SpracheSetzen
{
    public function handle(Request $request, Closure $next): Response
    {
        $praefix = $request->route()?->parameter('locale');

        // Der Präfix ist eine Adressangabe, kein Argument für den Controller.
        // Ohne dieses Vergessen bekämen alle Controller einen Parameter mehr.
        $request->route()?->forgetParameter('locale');

        $sprache = $praefix === null
            ? Language::standard()
            : Language::aktivFinden($praefix);

        if ($sprache === null) {
            throw new NotFoundHttpException("Unbekannte oder nicht freigeschaltete Sprache: {$praefix}");
        }

        // /de/verein wäre dieselbe Seite wie /verein — zwei Adressen für einen
        // Inhalt kostet genau die SEO-Substanz, die wir schützen sollen.
        if ($praefix !== null && $sprache->istStandard()) {
            $ohnePraefix = '/'.ltrim(substr($request->path(), strlen($praefix)), '/');

            // Suchparameter mitnehmen — sonst verliert eine geteilte
            // Blog-Suche beim Weiterleiten ihren Suchbegriff.
            $frage = $request->getQueryString();

            return redirect($ohnePraefix.($frage ? '?'.$frage : ''), 301);
        }

        app()->setLocale($sprache->code);

        // Für Datums- und Zahlenausgaben (Carbon, NumberFormatter).
        app()->setFallbackLocale(Language::standardCode());

        // Views brauchen die Sprache an vielen Stellen: html-lang, Umschalter,
        // hreflang, Navigation. Einmal teilen statt überall neu nachschlagen.
        view()->share('sprache', $sprache);

        return $next($request);
    }
}
