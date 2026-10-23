<?php

namespace App\Support;

use App\Models\Language;
use App\Models\Page;

/**
 * Eine eigene Triggerwarnung vor einem ganzen Bereich (KEV-156).
 *
 * Taddi zum „Raum für Ungesagtes“: „Als erstes muss hier noch mal ne
 * Triggerwarnung hin, bevor man auf dieser Seite weiter kommt.“ Der
 * allgemeine Hinweis (App\Support\Triggerwarnung) gilt für die ganze Website
 * und lässt sich dauerhaft abbestellen. Dieser hier benennt, was in einem
 * Bereich konkret vorkommt, und kommt bei jedem neuen Besuch wieder.
 *
 * Gepflegt wird er an der Seite selbst (Feld `triggerwarnung` im Panel). Steht
 * er an der Übersicht eines Bereichs, gilt er für alle Seiten, die im Menü
 * darunter hängen; die Zugehörigkeit kommt aus der Navigation, wie Brotkrumen
 * und Geschwisterseiten (Seitenkontext). So kann der Verein ihn ändern oder
 * einem anderen Bereich geben, ohne uns.
 */
class Bereichswarnung
{
    /**
     * Die Seite, deren Warnung vor $seite steht: sie selbst, sonst die
     * Übersicht ihres Bereichs. Null, wenn keine gepflegt ist.
     *
     * Fehlt die Warnung in der Sprache der Seite, gilt die deutsche: Lieber auf
     * Deutsch gewarnt als gar nicht (wie beim allgemeinen Hinweis).
     */
    public static function fuer(Page $seite): ?Page
    {
        if (filled($seite->triggerwarnung)) {
            return $seite;
        }

        $bereich = Seitenkontext::fuer($seite->slug)->bereich();

        // Die Adresse aus der Konfiguration, nicht die aufgelöste: Die trägt
        // in Fremdsprachen das Präfix und den Slug der Übersetzung.
        $adresse = collect(config('navigation.main'))
            ->firstWhere('schluessel', $bereich['schluessel'] ?? null)['url'] ?? null;

        if (! $adresse || ! str_starts_with($adresse, '/')) {
            return null;
        }

        $deutsch = Page::veroeffentlicht()
            ->standardfassung()
            ->where('locale', Language::standardCode())
            ->where('slug', trim($adresse, '/'))
            ->first();

        if (! $deutsch) {
            return null;
        }

        $uebersetzung = $seite->locale === $deutsch->locale
            ? $deutsch
            : Page::veroeffentlicht()
                ->standardfassung()
                ->where('uebersetzungs_gruppe', $deutsch->uebersetzungs_gruppe)
                ->where('locale', $seite->locale)
                ->first();

        foreach ([$uebersetzung, $deutsch] as $kandidat) {
            if (filled($kandidat?->triggerwarnung)) {
                return $kandidat;
            }
        }

        return null;
    }
}
