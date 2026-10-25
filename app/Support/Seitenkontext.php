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

    /**
     * Der Navigationspunkt, unter dem diese Seite hängt.
     *
     * Seit KEV-238 auch eine Ebene tiefer: /ehrenamtsmessen hängt unter
     * „Öffentlichkeitsarbeit“, das selbst unter „Gruppen & Veranstaltungen“
     * steht. Bereich ist dann „Öffentlichkeitsarbeit“, die Geschwister sind
     * Petitionen und Presse.
     */
    public function bereich(): ?array
    {
        foreach (Navigation::haupt() as $punkt) {
            $urls = array_column($punkt['children'] ?? [], 'url');

            if (in_array($this->eigeneAdresse(), $urls, true)) {
                return $punkt;
            }
        }

        foreach (Navigation::haupt() as $punkt) {
            foreach ($punkt['children'] ?? [] as $kind) {
                if (in_array($this->eigeneAdresse(), array_column($kind['children'] ?? [], 'url'), true)) {
                    return $kind;
                }
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

            // Eine Ebene tiefer (KEV-238): „Öffentlichkeitsarbeit“ führt zu
            // ihren Themenseiten.
            foreach ($punkt['children'] ?? [] as $kind) {
                if ($kind['url'] === $this->eigeneAdresse() && ! empty($kind['children'])) {
                    return $kind;
                }
            }
        }

        return null;
    }

    /**
     * Gehört die Seite zum Bereich „Verein“ — als Übersicht oder Unterseite?
     *
     * Dort heisst die Weiterführung am Seitenende „Mehr über KE!N EINZELFALL
     * e.V.“ statt „Mehr zu „Gemeinnütziger Verein““ (KEV-70, Wunsch von Taddi).
     */
    public function istVereinsbereich(): bool
    {
        $bereich = $this->istBereichsUebersicht() ?? $this->bereich();

        return ($bereich['url'] ?? null) === Language::aktuell()->pfad('/verein');
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
            // Auf der Übersicht selbst alle: Sie ist der Weg zu den
            // Unterseiten, „Gruppen & Veranstaltungen“ hat sieben (KEV-72).
            // Sonst zuerst die Seiten mit `vorn` (KEV-246: unter „Wissen“
            // Taddis eigene Kategorien vor den Seiten der alten Website),
            // im Übrigen in der Reihenfolge des Menüs.
            ->when(! $this->istBereichsUebersicht(), fn ($kinder) => $kinder
                ->sortBy(fn ($kind, $i) => [empty($kind['vorn']) ? 1 : 0, $i])
                ->take($hoechstens))
            ->values()
            ->all();
    }

    /** Der Hauptbereich über einem Unterbereich, sonst null. */
    private function ueberbereich(array $bereich): ?array
    {
        foreach (Navigation::haupt() as $punkt) {
            if (in_array($bereich['url'], array_column($punkt['children'] ?? [], 'url'), true)) {
                return $punkt;
            }
        }

        return null;
    }

    /**
     * Rechtstexte bekommen keinen Kontakt-Aufruf am Seitenende.
     *
     * „Du möchtest uns etwas mitteilen?" unter einer Datenschutzerklärung wirkt
     * unpassend — dort will niemand zu einem Gespräch eingeladen werden.
     */
    public function istRechtstext(): bool
    {
        return in_array($this->slug, ['datenschutz', 'impressum', 'barrierefreiheit', 'satzung'], true);
    }

    /**
     * Brotkrumen: Start › Bereich › aktuelle Seite.
     *
     * Eine Ebene tiefer mit dem Hauptbereich davor (KEV-254): Start › Wissen ›
     * Klartext statt Fachsprache › Gericht & Verfahren. Sonst fehlte der Weg
     * zurück zu „Wissen“.
     */
    public function brotkrumen(string $seitentitel): array
    {
        $krumen = [[
            'label' => __('rahmen.start'),
            'url' => Language::aktuell()->pfad('/'),
        ]];

        if ($bereich = $this->bereich()) {
            if ($ueber = $this->ueberbereich($bereich)) {
                $krumen[] = ['label' => $ueber['label'], 'url' => $ueber['url']];
            }

            $krumen[] = ['label' => $bereich['label'], 'url' => $bereich['url']];
        } elseif (($uebersicht = $this->istBereichsUebersicht()) && ($ueber = $this->ueberbereich($uebersicht))) {
            // Die Übersicht der Unterebene selbst: Start › Wissen › Klartext …
            $krumen[] = ['label' => $ueber['label'], 'url' => $ueber['url']];
        }

        $krumen[] = ['label' => $seitentitel, 'url' => null];

        return $krumen;
    }
}
