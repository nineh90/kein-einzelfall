<?php

namespace App\Rules;

use App\Models\Language;
use App\Models\Page;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Verhindert, dass ein Seiten-Slug und ein Sprachpräfix dieselbe Adresse
 * beanspruchen.
 *
 * Hintergrund: Die Routen sind flach, `/{slug}` fängt alles ab, was oben nicht
 * gepasst hat. Eine deutsche Seite mit dem Slug „ru“ und die russische
 * Sprachfassung unter `/ru` wären damit dieselbe Adresse — und welche gewinnt,
 * hinge an der Reihenfolge der Routendatei. Das ist keine Fehlermeldung wert,
 * die erst im Betrieb auffällt.
 *
 * Deshalb wird die Kollision beim Speichern abgelehnt, in beide Richtungen:
 * beim Anlegen einer Sprache und beim Anlegen einer Seite.
 */
class KollidiertNichtMitSprachpraefix implements ValidationRule
{
    /**
     * @param  'sprache'|'slug'  $seite  Was gerade gespeichert wird.
     */
    public function __construct(private readonly string $seite) {}

    public static function fuerSprachcode(): self
    {
        return new self('sprache');
    }

    public static function fuerSeitenSlug(): self
    {
        return new self('slug');
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $wert = (string) $value;

        if ($this->seite === 'sprache') {
            $treffer = Page::query()
                ->where('locale', Language::standardCode())
                ->where('slug', $wert)
                ->exists();

            if ($treffer) {
                $fail("Es gibt bereits eine Seite mit der Adresse „/{$wert}“. "
                    .'Ein Sprachcode darf nicht so heissen, sonst wäre die Adresse doppelt belegt.');
            }

            return;
        }

        // Slug einer Seite. Geprüft wird nicht gegen die vorhandenen Sprachen,
        // sondern gegen das Adressmuster selbst: Die Sprach-Routen stehen vor
        // der Sammelroute /{slug}. Eine Seite, deren Slug wie ein Sprachpräfix
        // aussieht, wäre auch dann unerreichbar, wenn es die Sprache noch gar
        // nicht gibt — und niemand käme auf die Idee, das zu vermuten.
        if (preg_match('/^'.Language::ADRESS_MUSTER.'$/', $wert) !== 1) {
            return;
        }

        $sprache = Language::finden($wert);

        $fail($sprache
            ? "„{$wert}“ ist das Adresspräfix der Sprache {$sprache->label_deutsch}."
                .' Bitte einen anderen Slug wählen.'
            : "„{$wert}“ sieht aus wie eine Sprachkennung und ist deshalb als Adresse reserviert."
                .' Bitte einen längeren Slug wählen, zum Beispiel mit einem beschreibenden Wort.');
    }
}
