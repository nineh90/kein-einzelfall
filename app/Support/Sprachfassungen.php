<?php

namespace App\Support;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Http\Request;

/**
 * Welche Sprachfassungen es von der gerade angezeigten Seite gibt.
 *
 * Zwei Fälle, weil die Website zwei Arten von Adressen hat:
 *
 *  - Inhaltsseiten aus der Datenbank haben je Sprache einen eigenen Slug.
 *    `/verein` heisst auf Englisch vielleicht `/en/about-us`. Die Zuordnung
 *    steht in der Übersetzungsgruppe.
 *  - Feste Adressen (Startseite, /aktuelles, /veranstaltungen) haben in jeder
 *    Sprache denselben Pfad, nur mit Präfix.
 *
 * Das Ergebnis speist den Sprachumschalter und die hreflang-Angaben. Beide
 * müssen dieselbe Liste sehen — sonst schickt der Umschalter Menschen auf
 * Adressen, die Google gar nicht kennt.
 */
class Sprachfassungen
{
    public function __construct(
        private readonly Request $request,
        private readonly ?Page $page = null,
    ) {}

    public static function fuer(Request $request, ?Page $page = null): self
    {
        return new self($request, $page);
    }

    /**
     * Adresse je aktiver Sprache.
     *
     * @return array<string, string> Sprachcode => Pfad
     */
    public function adressen(): array
    {
        /*
         * Leichte Sprache bekommt keine hreflang-Angaben. Sie ist Deutsch, nur
         * anders geschrieben — ein eigener Sprachcode dafür existiert nicht, und
         * ein Private-Use-Subtag wie `de-x-leicht` würde von Google als Fehler
         * gemeldet statt verstanden.
         */
        if ($this->page?->istLeichteSprache()) {
            return [];
        }

        $adressen = [];

        foreach (Language::aktive() as $sprache) {
            if ($pfad = $this->adresseIn($sprache)) {
                $adressen[$sprache->code] = $pfad;
            }
        }

        return $adressen;
    }

    private function adresseIn(Language $sprache): ?string
    {
        if ($this->page) {
            /*
             * Die Startseite heisst in jeder Sprache `/` — ihr Slug taucht in
             * keiner Adresse auf. Ohne diesen Zweig verwiesen Umschalter und
             * hreflang auf `/en/startseite`, wo nur eine Weiterleitung steht.
             */
            if ($this->page->istStartseite()) {
                return $sprache->pfad('/');
            }

            $fassung = $this->page->inSprache($sprache->code);

            /*
             * Keine Übersetzung? Trotzdem verlinken — aber unter der Adresse
             * der *Standardfassung*, denn genau die liefert dort den sichtbaren
             * Rückfall aus. Ein Umschalter, der Sprachen ausblendet, sobald eine
             * Seite fehlt, wäre für die Zielgruppe schlechter: Er sähe auf jeder
             * zweiten Seite anders aus.
             */
            $slug = $fassung?->slug ?? $this->standardSlug();

            return $slug ? $sprache->pfad('/'.$slug) : null;
        }

        return $sprache->pfad($this->pfadOhnePraefix());
    }

    /** Der Slug, unter dem die Standardfassung erreichbar ist. */
    private function standardSlug(): ?string
    {
        if ($this->page->locale === Language::standardCode()) {
            return $this->page->slug;
        }

        return $this->page->inSprache(Language::standardCode())?->slug;
    }

    /** Der aufgerufene Pfad ohne Sprachpräfix, mit führendem Schrägstrich. */
    private function pfadOhnePraefix(): string
    {
        $pfad = '/'.trim($this->request->path(), '/');
        $praefix = Language::aktuell()->praefix();

        if ($praefix !== '' && str_starts_with($pfad, $praefix.'/')) {
            return substr($pfad, strlen($praefix));
        }

        if ($pfad === $praefix) {
            return '/';
        }

        return $pfad;
    }
}
