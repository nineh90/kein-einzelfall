<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * „Vorlagen & Schreiben“ unter Wissen (KEV-323/324, Taddis Drive „3. Wissen
 * › 5. Vorlagen/Schreiben“): eine Übersicht mit ihrem Text und den zwölf
 * Kategorien als Kacheln, je Kategorie eine Seite mit den Musterschreiben
 * zum Herunterladen, gegliedert wie ihre Unterordner.
 *
 * Die Musterschreiben sind Taddis Google-Docs als Word-Dateien
 * (public/dokumente/vorlagen/…), Titel und Reihenfolge aus den Dateien
 * selbst, die Kategorien und Gruppen aus ihrem Doc „Alle Kategorien“
 * (database/seeders/data/vorlagen.json). Word, weil man die Vorlagen
 * anpassen soll: Die Angaben in eckigen Klammern ersetzt man selbst.
 *
 * Legt nur an, was fehlt. Was der Verein an einer Seite geändert hat, bleibt.
 */
class VorlagenSeeder extends Seeder
{
    public const SLUG = 'vorlagen-und-schreiben';

    public const TITEL = 'Vorlagen & Schreiben';

    /** Taddis Text (Doc „0. Vorlagen u. Schreiben“), Kontaktadresse fett wie bei Klartext. */
    public const UEBERSICHT = [
        'titel' => 'Damit aus deinem Anliegen ein klares Schreiben wird.',
        'absaetze' => [
            'Manchmal weiß man genau, was man sagen möchte – aber nicht, wie man es formulieren soll.',
            'Gerade im Kontakt mit Behörden, Versicherungen, Gerichten oder medizinischen Stellen können Schreiben schnell kompliziert wirken. Fachbegriffe, Fristen und formale Anforderungen machen es oft zusätzlich schwer.',
            'Hier findest Du deshalb Vorlagen und Beispielschreiben für viele typische Situationen – von der Sachstandsanfrage über Widersprüche bis hin zu Schreiben rund um Pflege, GdB, Teilhabe, Soziale Entschädigung, Medizin und Datenschutz.',
            'Unsere Vorlagen sollen Dir dabei helfen, Dein Anliegen klar, verständlich und strukturiert zu formulieren. Du kannst sie an Deine persönliche Situation anpassen und als Orientierung für Dein eigenes Schreiben nutzen.',
            'Denn Wissen bedeutet nicht nur zu verstehen, was passiert – sondern auch zu wissen, wie Du selbst handeln kannst. Wenn Du Hilfe oder Unterstützung bei einem Schreiben brauchst, melde Dich gern bei uns unter *kontakt@kein-einzelfall.de*.',
        ],
        'hand' => 'Damit aus Unsicherheit ein nächster Schritt werden kann.',
        'alles_zeigen' => true,
    ];

    public const UNTERZEILE = 'Orientierung für Deine nächsten Schritte.';

    /**
     * Einleitung jeder Kategorie, von uns (Übergabe-Checkliste A16): wie die
     * Vorlagen aufgebaut sind und der Hinweis, der in jeder Vorlage steht.
     */
    public const KATEGORIE_ABSAETZE = [
        'Jede Vorlage erklärt kurz, worum es geht und wann das Schreiben sinnvoll ist, und enthält einen Beispieltext. Lade sie als Word-Datei herunter, ersetze die Angaben in eckigen Klammern und passe den Text an Deine Situation an.',
        'Unser Informationsmaterial dient ausschließlich der Aufklärung. Es ersetzt keine Rechtsberatung, keine Therapie und keine medizinische Beratung.',
        'Wenn Du Hilfe bei einem Schreiben brauchst, melde Dich gern bei uns unter *kontakt@kein-einzelfall.de*.',
    ];

    public const KATEGORIE_TITEL = 'Musterschreiben zum Anpassen';

    public function run(): void
    {
        self::anlegen();
    }

    /** @return list<array{slug: string, titel: string, gruppen: list<array{titel: string, vorlagen: list<array{titel: string, url: string, bytes: int}>}>}> */
    public static function kategorien(): array
    {
        return json_decode((string) file_get_contents(database_path('seeders/data/vorlagen.json')), true);
    }

    public static function anlegen(): void
    {
        $uebersicht = self::seite(self::SLUG, self::TITEL,
            'Vorlagen und Beispielschreiben für Behörden, Pflege, GdB, Teilhabe, Soziale Entschädigung, Gericht und Datenschutz, zum Anpassen.');

        if ($uebersicht->wasRecentlyCreated) {
            $uebersicht->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => self::UEBERSICHT]);
            $uebersicht->blocks()->create(['typ' => 'unterseiten', 'position' => 1, 'data' => []]);
        }

        foreach (self::kategorien() as $kategorie) {
            $seite = self::seite($kategorie['slug'], $kategorie['titel'],
                'Vorlagen und Beispielschreiben: '.$kategorie['titel'].'. Als Word-Datei zum Anpassen, von KE!N EINZELFALL e.V.');

            if (! $seite->wasRecentlyCreated) {
                continue;
            }

            // Einleitung mit „Auf dieser Seite“ daneben (die Gruppen), darunter
            // alle Gruppen in einem Abschnitt, zwei Spalten.
            $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => [
                'titel' => self::KATEGORIE_TITEL,
                'absaetze' => self::KATEGORIE_ABSAETZE,
                'verzeichnis_daneben' => true,
            ]]);

            $seite->blocks()->create(['typ' => 'download_list', 'position' => 1, 'data' => [
                'gruppen' => array_map(fn ($g) => [
                    'titel' => $g['titel'],
                    'anker' => 'vorlagen-'.\Illuminate\Support\Str::slug($g['titel'], '-', 'de'),
                    'dokumente' => $g['vorlagen'],
                ], $kategorie['gruppen']),
            ]]);
        }

        Titelbilder::setzen();
    }

    private static function seite(string $slug, string $titel, string $beschreibung): Page
    {
        return Page::firstOrCreate(
            ['slug' => $slug, 'locale' => 'de'],
            [
                'titel' => $titel,
                'meta_title' => $titel.' - Kein Einzelfall e.V.',
                'meta_description' => $beschreibung,
                'published_at' => now(),
            ],
        );
    }
}
