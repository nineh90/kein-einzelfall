<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Englische Fassungen der Kernseiten — für die Vorführung.
 *
 * ⚠️ MASCHINELLE ÜBERSETZUNG. Die Texte in database/seeders/data/uebersetzungen.json
 * sind ein Entwurf, damit der Sprachumschalter etwas zu zeigen hat. Der Verein
 * prüft und korrigiert sie anschliessend im Panel.
 *
 * Russisch gab es hier bis zum 19.09.2026 ebenfalls — gestrichen, weil es
 * niemand gegenlesen konnte (Migration `russisch_entfernen`).
 *
 * Deshalb läuft dieser Seeder **nicht** automatisch beim Deploy und hängt an
 * keiner Migration. Er wird von Hand ausgeführt, bewusst nur auf der Demo-
 * Datenbank:
 *
 *     php artisan db:seed --class=UebersetzungenSeeder
 *
 * So landet kein ungeprüfter Text versehentlich auf dem Server. Solange eine
 * Seite hier fehlt, greift der eingebaute, sichtbare Rückfall: Der Besucher
 * sieht die deutsche Fassung mit einem Hinweis in seiner Sprache.
 *
 * Wie es arbeitet: Jede deutsche Seite wird geklont — gleiche Bausteine,
 * gleiche Reihenfolge, gleiche Adresse mit Sprachpräfix. Übersetzt werden nur
 * die Textfelder, und nur, wenn das Wörterbuch den deutschen Satz kennt.
 * E-Mail-Adressen, IBAN, Icons, Links und Schalter bleiben unangetastet — ein
 * übersetzter Link wäre ein toter Link. Notrufnummern werden nicht erfunden;
 * die deutschen Nummern gelten in Deutschland unabhängig von der Sprache.
 */
class UebersetzungenSeeder extends Seeder
{
    /**
     * Die Kernseiten der Vorführung. Bewusst nicht alle 24: Eine halb
     * übersetzte Seite sieht kaputt aus, eine fehlende fällt sauber auf Deutsch
     * zurück. Lieber wenige Seiten ganz als viele halb.
     */
    private const KERN = ['startseite', 'verein', 'anfragen', 'spenden'];

    /**
     * Felder, die sichtbaren Text tragen. Nur diese werden übersetzt — `url`,
     * `icon`, `variant`, `iban` und so weiter bleiben, wie sie sind.
     */
    private const TEXT_FELDER = [
        'titel', 'sub', 'eyebrow', 'text', 'hand', 'einleitung',
        'zitat', 'notiz', 'hinweis', 'thema', 'alleLabel', 'label',
        'link', 'bild_alt',
    ];

    /** @var array<string, array{en: string}> */
    private array $woerterbuch = [];

    public function run(): void
    {
        // Die Sprachzeilen müssen da sein — auf frischer Datenbank sonst nicht.
        $this->call(SprachenSeeder::class);

        $this->woerterbuch = json_decode(
            file_get_contents(base_path('database/seeders/data/uebersetzungen.json')),
            true,
        ) ?? [];

        $angelegt = 0;

        foreach (self::KERN as $slug) {
            $deutsch = Page::query()
                ->where('locale', Language::standardCode())
                ->where('fassung', Page::FASSUNG_STANDARD)
                ->where('slug', $slug)
                ->with('blocks')
                ->first();

            if (! $deutsch) {
                $this->command?->warn("Deutsche Seite /{$slug} fehlt — erst den Bestand einpflegen. Übersprungen.");

                continue;
            }

            foreach (['en'] as $locale) {
                // Idempotent: eine schon vorhandene Übersetzung nicht anfassen —
                // der Verein könnte sie inzwischen von Hand korrigiert haben.
                $vorhanden = Page::query()
                    ->where('locale', $locale)
                    ->where('fassung', Page::FASSUNG_STANDARD)
                    ->where('slug', $slug)
                    ->exists();

                if ($vorhanden) {
                    continue;
                }

                $this->uebersetzung($deutsch, $locale);
                $angelegt++;
            }
        }

        // Erst jetzt freischalten: Vorher hätte der Umschalter auf leere
        // Fassungen gezeigt. Nach dem Anlegen der Kernseiten ist der Rückfall
        // für den Rest ein regulärer, sichtbarer Zustand.
        Language::where('code', 'en')->update(['aktiv' => true]);
        Language::memoLeeren();

        $this->command?->info("{$angelegt} Übersetzungen angelegt, Englisch freigeschaltet.");
        $this->command?->warn('Hinweis: maschinelle Übersetzungen — vor dem Livegang vom Verein prüfen lassen.');
    }

    private function uebersetzung(Page $deutsch, string $locale): void
    {
        $seite = Page::create([
            'locale' => $locale,
            // Dieselbe Übersetzungsgruppe: darüber finden Umschalter, hreflang
            // und Menü die Fassungen zusammen.
            'uebersetzungs_gruppe' => $deutsch->uebersetzungs_gruppe,
            // Gleicher Slug, nur mit Sprachpräfix (/en/verein). Für die Demo
            // reicht das und kann sich mit keiner deutschen Adresse beissen —
            // eindeutig ist der Slug je Sprache.
            'slug' => $deutsch->slug,
            'titel' => $this->tr($deutsch->titel, $locale),
            // Das Titelbild zeigt keinen Text, es gilt für jede Sprache.
            'titelbild' => $deutsch->titelbild,
            'titelbild_alt' => $this->tr($deutsch->titelbild_alt, $locale),
            'untertitel' => $this->tr($deutsch->untertitel, $locale),
            'meta_title' => $this->tr($deutsch->meta_title, $locale),
            'meta_description' => $this->tr($deutsch->meta_description, $locale),
            'published_at' => now(),
        ]);

        foreach ($deutsch->blocks as $block) {
            $seite->blocks()->create([
                'typ' => $block->typ,
                'position' => $block->position,
                'data' => $this->uebersetzeData($block->data ?? [], $locale),
            ]);
        }
    }

    /**
     * Läuft die Bausteindaten durch und übersetzt nur die Textfelder.
     *
     * @param  array<string|int, mixed>  $data
     * @return array<string|int, mixed>
     */
    private function uebersetzeData(array $data, string $locale): array
    {
        $ergebnis = [];

        foreach ($data as $schluessel => $wert) {
            if ($schluessel === 'absaetze' && is_array($wert)) {
                // Liste von Textabsätzen.
                $ergebnis[$schluessel] = array_map(
                    fn ($absatz) => is_string($absatz) ? $this->tr($absatz, $locale) : $absatz,
                    $wert,
                );
            } elseif (is_array($wert)) {
                // Verschachtelt: Karten, Knöpfe, Bank … je Eintrag weiterreichen.
                $ergebnis[$schluessel] = array_is_list($wert)
                    ? array_map(
                        fn ($eintrag) => is_array($eintrag) ? $this->uebersetzeData($eintrag, $locale) : $eintrag,
                        $wert,
                    )
                    : $this->uebersetzeData($wert, $locale);
            } elseif (is_string($wert) && in_array($schluessel, self::TEXT_FELDER, true)) {
                $ergebnis[$schluessel] = $this->tr($wert, $locale);
            } else {
                // Links, Icons, Schalter, Zahlen: unverändert.
                $ergebnis[$schluessel] = $wert;
            }
        }

        return $ergebnis;
    }

    /**
     * Ein einzelner Text. Kennt das Wörterbuch den deutschen Satz nicht, bleibt
     * er deutsch stehen — sichtbar unübersetzt ist ehrlicher als falsch.
     */
    private function tr(?string $deutsch, string $locale): ?string
    {
        if ($deutsch === null || $deutsch === '') {
            return $deutsch;
        }

        return $this->woerterbuch[trim($deutsch)][$locale] ?? $deutsch;
    }
}
