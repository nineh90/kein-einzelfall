<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Eine Sprache der Website.
 *
 * Bewusst eine Tabelle und keine Config: Der Verein soll weitere Sprachen
 * selbst anlegen können, ohne dass jemand eine Datei anfasst und neu
 * ausrollt. Deshalb steht hier auch das URL-Präfix — `code` ist beides.
 *
 * Die Standardsprache (Deutsch) bekommt kein Präfix. Das ist die wichtigste
 * Einzelentscheidung der Mehrsprachigkeit: nur so bleiben die 24 bestehenden
 * Adressen und die 26 Weiterleitungen unverändert, und der ausdrückliche
 * Kundenwunsch „SEO darf nicht schlechter werden“ bleibt eingehalten.
 */
class Language extends Model
{
    /**
     * Welche Adressen als Sprachpräfix gelesen werden.
     *
     * Bewusst eng: Das Muster steht im Routing *vor* der Sammelroute /{slug}.
     * Alles, was hier passt, wird als Sprache gelesen — eine deutsche Seite mit
     * so einem Slug wäre unerreichbar. Zwei Buchstaben als Grundform deckt alle
     * gängigen Sprachen ab (de, en, ru, ar, fa, ku, tr, uk, pl), die optionalen
     * Zusätze decken Formen wie pt-br, zh-hans oder de-x-leicht ab.
     *
     * Drei Buchstaben wären auch üblich (ISO 639-3), würden aber Slugs wie
     * „faq“ blockieren — die sind wahrscheinlicher als eine Sprache, die keine
     * zweibuchstabige Kennung hat.
     *
     * `KollidiertNichtMitSprachpraefix` hält Seiten-Slugs von diesem Muster fern.
     */
    public const ADRESS_MUSTER = '[a-z]{2}(-[a-z0-9]{1,8})*';

    protected $fillable = [
        'code', 'label', 'label_deutsch', 'richtung',
        'aktiv', 'position', 'ist_standard', 'fallback_code',
    ];

    protected function casts(): array
    {
        return [
            'aktiv' => 'boolean',
            'ist_standard' => 'boolean',
        ];
    }

    /**
     * Schlüssel des Zwischenspeichers.
     *
     * Sprachen werden pro Seitenaufruf an vielen Stellen gebraucht — im
     * Routing, im Umschalter, in den hreflang-Angaben, in der Navigation.
     * Ohne Memo wären das ein Dutzend identischer Abfragen.
     *
     * Bewusst kein Cache-Treiber: der müsste nach jeder Änderung im Panel
     * geleert werden, und das ist einem Verein nicht zumutbar.
     *
     * Und bewusst keine `static`-Eigenschaft: die überlebt in der Testsuite
     * den Rücksprung von RefreshDatabase und würde Daten eines anderen Tests
     * ausliefern. Der Container wird pro Test neu gebaut, der Speicher
     * verfällt damit von selbst.
     */
    private const MEMO = 'ke.sprachen';

    /** @return Collection<int, self> */
    public static function alle(): Collection
    {
        if (app()->bound(self::MEMO)) {
            return app()->make(self::MEMO);
        }

        // Vor der ersten Migration gibt es die Tabelle noch nicht. Die Website
        // muss trotzdem antworten können — sonst hängt `migrate` an sich selbst.
        $sprachen = Schema::hasTable('languages')
            ? self::query()->orderBy('position')->orderBy('code')->get()
            : new Collection;

        app()->instance(self::MEMO, $sprachen);

        return $sprachen;
    }

    /** @return Collection<int, self> */
    public static function aktive(): Collection
    {
        return self::alle()->where('aktiv', true)->values();
    }

    /**
     * Die Sprache ohne Präfix.
     *
     * Der Rückfall auf eine erfundene Zeile ist kein Schönheitsfehler, sondern
     * Absicht: Die Website muss auch dann ausliefern, wenn die Tabelle noch
     * leer ist — etwa in einem Test, der nur eine Seite anlegt.
     */
    public static function standard(): self
    {
        return self::alle()->firstWhere('ist_standard', true)
            ?? self::alle()->first()
            ?? new self([
                'code' => config('app.locale'),
                'label' => 'Deutsch',
                'label_deutsch' => 'Deutsch',
                'richtung' => 'ltr',
                'aktiv' => true,
                'ist_standard' => true,
            ]);
    }

    public static function standardCode(): string
    {
        return self::standard()->code;
    }

    public static function finden(string $code): ?self
    {
        return self::alle()->firstWhere('code', $code);
    }

    /** Die Sprache dieses Aufrufs. */
    public static function aktuell(): self
    {
        return self::finden(app()->getLocale()) ?? self::standard();
    }

    /** Nur aktive Sprachen sind öffentlich erreichbar. */
    public static function aktivFinden(string $code): ?self
    {
        return self::aktive()->firstWhere('code', $code);
    }

    /** @return array<int, string> */
    public static function aktiveCodes(): array
    {
        return self::aktive()->pluck('code')->all();
    }

    public function istStandard(): bool
    {
        return $this->code === self::standardCode();
    }

    /** Präfix inklusive führendem Schrägstrich, für die Standardsprache leer. */
    public function praefix(): string
    {
        return $this->istStandard() ? '' : '/'.$this->code;
    }

    /**
     * Absoluter Pfad in dieser Sprache.
     *
     * `$pfad` ist der Pfad ohne Sprachpräfix, mit führendem Schrägstrich.
     * `/` bleibt in der Standardsprache `/` und wird sonst zu `/en`.
     */
    public function pfad(string $pfad = '/'): string
    {
        $pfad = '/'.ltrim($pfad, '/');

        if ($pfad === '/') {
            return $this->praefix() ?: '/';
        }

        return $this->praefix().$pfad;
    }

    /**
     * Sprachangabe für Open Graph — dort gilt „de_DE“, nicht „de“.
     *
     * Ohne gepflegte Länderangabe wird der Sprachcode verdoppelt (de → de_DE,
     * ru → ru_RU). Das trifft für die gängigen Fälle zu und ist allemal besser
     * als eine Angabe, die für jede Sprache Deutschland behauptet.
     */
    public function ogLocale(): string
    {
        [$basis, $region] = array_pad(explode('-', $this->code, 2), 2, null);

        return $basis.'_'.strtoupper($region ?: $basis);
    }

    /**
     * Sprache, auf die zurückgefallen wird, wenn eine Übersetzung fehlt.
     * Kein Rückfall auf sich selbst — das wäre eine Endlosschleife.
     */
    public function fallback(): ?self
    {
        $code = $this->fallback_code ?: self::standardCode();

        return $code === $this->code ? null : self::finden($code);
    }

    protected static function booted(): void
    {
        // Die Standardsprache ist die Sprache ohne Präfix. Wäre sie unsichtbar,
        // wäre die ganze Website unsichtbar.
        static::saving(function (self $sprache) {
            if ($sprache->ist_standard) {
                $sprache->aktiv = true;
                $sprache->fallback_code = null;   // kein Rückfall auf sich selbst
            }
        });

        static::saved(function (self $sprache) {
            // Genau eine Standardsprache. Ein zweites Häkchen nimmt der ersten
            // ihres — sonst hinge es davon ab, welche Zeile zuerst gelesen wird.
            // Über den Query Builder, damit sich das Ereignis nicht selbst auslöst.
            if ($sprache->ist_standard) {
                self::query()->whereKeyNot($sprache->getKey())
                    ->where('ist_standard', true)
                    ->update(['ist_standard' => false]);
            }

            self::memoLeeren();
        });

        static::deleted(fn () => self::memoLeeren());
    }

    public static function memoLeeren(): void
    {
        app()->forgetInstance(self::MEMO);
    }
}
