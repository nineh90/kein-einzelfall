<?php

namespace App\Support;

use App\Models\Language;

/**
 * Ordnet eine Seite in die Navigation ein.
 *
 * Daraus entstehen Brotkrumen, die Bereichsangabe über der Überschrift und die
 * Liste der Geschwisterseiten am Seitenende. Alles wird aus der bestehenden
 * Navigationsstruktur abgeleitet — es muss nichts zusätzlich gepflegt werden,
 * und es wird auch nichts erfunden.
 */
class Seitenkontext
{
    public function __construct(private readonly string $slug) {}

    public static function fuer(string $slug): self
    {
        return new self($slug);
    }

    /**
     * Die eigene Adresse, so wie sie auch in der Navigation steht.
     *
     * Navigation::haupt() liefert Adressen mit Sprachpräfix. Ohne dieselbe
     * Schreibweise fände sich eine Seite in einer Fremdsprache in keinem
     * Bereich wieder — und verlöre stillschweigend Brotkrumen, Bereichsangabe
     * und Geschwisterseiten.
     */
    private function eigeneAdresse(): string
    {
        return Language::aktuell()->pfad('/'.$this->slug);
    }

    /** Der Navigationspunkt, unter dem diese Seite hängt. */
    public function bereich(): ?array
    {
        foreach (Navigation::haupt() as $punkt) {
            $urls = array_column($punkt['children'] ?? [], 'url');

            if (in_array($this->eigeneAdresse(), $urls, true)) {
                return $punkt;
            }
        }

        return null;
    }

    /**
     * Ist diese Seite selbst die Übersicht eines Bereichs?
     * Dann führt sie nicht zu Geschwistern, sondern zu ihren Unterseiten.
     */
    public function istBereichsUebersicht(): ?array
    {
        foreach (Navigation::haupt() as $punkt) {
            if ($punkt['url'] === $this->eigeneAdresse() && ! empty($punkt['children'])) {
                return $punkt;
            }
        }

        return null;
    }

    /** Name des Bereichs — erscheint als Überzeile über der Überschrift. */
    public function bereichName(): ?string
    {
        return $this->bereich()['label'] ?? null;
    }

    /**
     * Andere Seiten desselben Bereichs.
     *
     * Gibt der Seite einen Ausgang: Wer auf „Satzung" gelandet ist, findet so
     * auch „Mitgliedschaft", ohne ins Menü zurückzumüssen.
     */
    public function geschwister(int $hoechstens = 6): array
    {
        // Übersichtsseiten wie /verein führen zu ihren eigenen Unterseiten,
        // alle anderen zu den Seiten desselben Bereichs.
        $bereich = $this->istBereichsUebersicht() ?? $this->bereich();

        if (! $bereich) {
            return [];
        }

        return collect($bereich['children'] ?? [])
            ->reject(fn ($kind) => $kind['url'] === $this->eigeneAdresse())
            // Die Übersichtsseite des Bereichs steht schon in den Brotkrumen
            ->reject(fn ($kind) => $kind['url'] === $bereich['url'])
            ->take($hoechstens)
            ->values()
            ->all();
    }

    /**
     * Rechtstexte bekommen keinen Kontakt-Aufruf am Seitenende.
     *
     * „Fragen zu diesem Thema?" unter einer Datenschutzerklärung wirkt
     * unpassend — dort will niemand zu einem Gespräch eingeladen werden.
     */
    public function istRechtstext(): bool
    {
        return in_array($this->slug, ['datenschutz', 'impressum', 'barrierefreiheit', 'satzung'], true);
    }

    /** Brotkrumen: Start › Bereich › aktuelle Seite. */
    public function brotkrumen(string $seitentitel): array
    {
        $krumen = [[
            'label' => __('rahmen.start'),
            'url' => Language::aktuell()->pfad('/'),
        ]];

        if ($bereich = $this->bereich()) {
            $krumen[] = ['label' => $bereich['label'], 'url' => $bereich['url']];
        }

        $krumen[] = ['label' => $seitentitel, 'url' => null];

        return $krumen;
    }
}
