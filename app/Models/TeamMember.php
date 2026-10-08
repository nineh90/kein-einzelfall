<?php

namespace App\Models;

use App\Models\Concerns\Uebersetzbar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TeamMember extends Model
{
    use Uebersetzbar;

    protected $fillable = [
        'name', 'rolle', 'untertitel', 'kurzprofil', 'profil',
        'foto_pfad', 'foto_alt', 'bereich', 'position', 'published_at', 'uebersetzungen',
    ];

    /** Sprachfassungen (Uebersetzbar). Nicht `bereich`: danach wird gefiltert. */
    protected array $uebersetzbar = ['rolle', 'untertitel', 'kurzprofil', 'profil', 'foto_alt'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'uebersetzungen' => 'array'];
    }

    public function scopeVeroeffentlicht(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** Hat diese Person mehr zu sagen, als das Kurzprofil schon zeigt? */
    public function hatProfil(): bool
    {
        return filled(trim(strip_tags($this->profilFortsetzung())));
    }

    /**
     * Der ausführliche Text ab dort, wo das Kurzprofil aufhört (KEV-63).
     *
     * Vorher begann „Mehr über … lesen“ den Text von vorn, der sichtbare
     * Anfang stand also zweimal da. Wie beim „Weiterlesen“ der Textbausteine
     * geht es jetzt fortlaufend weiter:
     *
     *  - Das Kurzprofil ist ein ganzer Absatz: weiter mit dem nächsten.
     *  - Es ist mitten im Absatz abgeschnitten („…“): weiter an dieser
     *    Stelle, ab dem Anfang des abgeschnittenen Wortes, mit „…“ davor. Der Rest dieses Absatzes steht dann als
     *    reiner Text da, Hervorhebungen darin gehen verloren.
     *  - Absätze vor dem Kurzprofil (Franziska Künstler beginnt mit drei
     *    Stichpunkten) kommen danach, sie gehen nicht verloren.
     *
     * Verglichen wird ohne Leerraum, weil Import und Panel Zeilenumbrüche
     * und Leerzeichen unterschiedlich setzen. Findet sich das Kurzprofil im
     * Text nicht (etwa nach einer Änderung im Panel), steht der ganze Text da
     * wie bisher. Lieber einmal doppelt als ein Stück verschluckt.
     */
    public function profilFortsetzung(): string
    {
        $profil = (string) $this->profil;
        $kurz = self::ohneLeerraum(preg_replace('/(\.\.\.|…)\s*$/u', '', (string) $this->kurzprofil));

        if ($profil === '' || $kurz === '') {
            return $profil;
        }

        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8"><body>'.$profil.'</body>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $knoten = collect(iterator_to_array($dom->getElementsByTagName('body')->item(0)?->childNodes ?? []))
            ->filter(fn ($k) => trim($k->textContent) !== '')
            ->values();

        $texte = $knoten->map(fn ($k) => self::ohneLeerraum($k->textContent))->all();

        foreach ($texte as $start => $_) {
            $rest = $kurz;
            $ende = $start;

            // Das Kurzprofil kann über mehrere Absätze reichen.
            while ($ende < count($texte) && $rest !== '') {
                $text = $texte[$ende];

                // Genau dieser Absatz: ganz gezeigt, weiter mit dem nächsten.
                if ($text === $rest) {
                    $rest = '';
                    $ende++;
                    break;
                }
                if (str_starts_with($text, $rest)) {
                    break;
                }
                if (! str_starts_with($rest, $text)) {
                    continue 2;
                }

                $rest = substr($rest, strlen($text));
                $ende++;
            }

            if ($rest !== '' && $ende >= count($texte)) {
                continue;
            }

            $html = fn ($k) => $dom->saveHTML($k);
            $teile = [];

            // Mitten in einem Absatz: den Rest dieses Absatzes zeigen.
            if ($rest !== '') {
                $weiter = self::nachZeichen($knoten[$ende]->textContent, mb_strlen($rest));
                // „…“ nur, wo das Kurzprofil selbst mit „…“ abbricht. Nach
                // einem ganzen Satz geht es einfach mit dem nächsten weiter.
                $abgebrochen = (bool) preg_match('/(\.\.\.|…)\s*$/u', (string) $this->kurzprofil);
                if ($weiter !== '') {
                    $teile[] = '<p>'.($abgebrochen ? '…' : '').e($weiter).'</p>';
                }
                $ende++;
            }

            foreach ($knoten->slice($ende) as $k) {
                $teile[] = $html($k);
            }
            foreach ($knoten->take($start) as $k) {
                $teile[] = $html($k);
            }

            return implode("\n", $teile);
        }

        return $profil;
    }

    private static function ohneLeerraum(string $text): string
    {
        return preg_replace('/\s+/u', '', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /** Der Text nach den ersten $anzahl Zeichen, Leerraum nicht mitgezählt. */
    private static function nachZeichen(string $text, int $anzahl): string
    {
        $gezaehlt = 0;
        $laenge = mb_strlen($text);

        for ($i = 0; $i < $laenge && $gezaehlt < $anzahl; $i++) {
            if (! preg_match('/\s/u', mb_substr($text, $i, 1))) {
                $gezaehlt++;
            }
        }

        // Mitten im Wort abgeschnitten („entste...“): am Wortanfang weiter,
        // sonst begänne die Fortsetzung mit „…hen“.
        // Endet das Kurzprofil an einer Wortgrenze (ganzer Satz), bleibt es
        // dabei, sonst stünde das letzte Wort zweimal da.
        $imWort = $i < $laenge && ! preg_match('/\s/u', mb_substr($text, $i, 1));

        while ($imWort && $i > 0 && ! preg_match('/\s/u', mb_substr($text, $i - 1, 1))) {
            $i--;
        }

        return trim(mb_substr($text, $i));
    }

    /**
     * Wie die Person im Knopf „Mehr über … lesen“ heisst.
     *
     * Normalerweise der Vorname („Mehr über Tatjana lesen“). Das erste Wort
     * passt aber nicht immer: Aus „Herr und Frau Unbekannt“ wurde „Mehr über
     * Herr lesen“. Beginnt der Name mit einer Anrede oder nennt er mehrere
     * Personen („und“, „&“), steht deshalb der ganze Name da.
     */
    public function rufname(): string
    {
        $name = trim($this->name);
        $anrede = preg_match('/^(Herr|Frau|Dr\.|Prof\.)\s/u', $name);
        $mehrere = preg_match('/\s(und|&)\s/u', $name);

        return $anrede || $mehrere ? $name : Str::before($name, ' ');
    }

    /** Sprungziel, damit sich einzelne Profile verlinken lassen. */
    public function anker(): string
    {
        return 'person-'.Str::slug($this->name);
    }
}
