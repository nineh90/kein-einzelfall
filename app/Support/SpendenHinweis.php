<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Entscheidet, ob eine Seite den Spendenhinweis überhaupt mitbringt.
 *
 * Die Entscheidung fällt auf dem Server, nicht im Browser: Auf einer
 * ausgenommenen Seite steht der Kasten gar nicht erst im HTML — dann kann ihn
 * auch kein Skriptfehler und kein Zähler dort hervorholen. Ob er auf einer
 * erlaubten Seite dann *erscheint*, entscheidet allein der Browser anhand
 * seines Zählers (resources/js/spendenhinweis.js).
 *
 * Die Regeln stehen in config/spendenhinweis.php.
 */
class SpendenHinweis
{
    public static function erlaubtAuf(Request $request): bool
    {
        $route = $request->route();

        // Ohne Route: Weiterleitungen, Fehler vor dem Routing. Nichts, worauf
        // man jemanden um eine Spende bitten sollte.
        if (! $route) {
            return false;
        }

        // Die Sprachfassungen heissen „sprache.page“ usw. — dieselbe Seite.
        $name = Str::after((string) $route->getName(), 'sprache.');

        // Nur die öffentlichen Seiten, keine Vorschau, kein Fallback.
        if (! in_array($name, self::ROUTEN, true)) {
            return false;
        }

        $slug = $route->parameter('slug');

        return ! ($slug && in_array($slug, config('spendenhinweis.ausgenommen', []), true));
    }

    /**
     * Routen, auf denen der Hinweis stehen darf.
     *
     * Eine Liste dessen, was erlaubt ist — nicht dessen, was verboten ist:
     * Eine neue Route ist dann erst einmal ohne Hinweis, und jemand entscheidet
     * bewusst, ob sie einen bekommt. Andersherum hätte die nächste Fehler- oder
     * Formularseite ihn, ohne dass es jemand gemerkt hat.
     */
    private const ROUTEN = [
        'start', 'page', 'leichte-sprache', 'glossar',
        'blog.index', 'blog.show', 'events.index', 'events.show',
    ];
}
