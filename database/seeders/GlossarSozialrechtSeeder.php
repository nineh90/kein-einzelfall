<?php

namespace Database\Seeders;

use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Glossar mit Taddis Abkürzungsverzeichnis „Abkürzungen Sozialrecht“
 * (KEV-153).
 *
 * Zwei Teile:
 *
 * - Die Seite „glossar“ trägt ihre Einleitung. Der GlossarController zeigt
 *   sie über der Liste; Titel, Titelbild und Text pflegt der Verein damit im
 *   Panel wie bei jeder Seite. Was im Dokument fett war, ist fett
 *   (*Sternchen*), Absätze wie bei ihr. Das Titelbild ist Taddis
 *   „Abkürzungen SER“ aus dem Drive (Zettelkasten).
 * - Die Abkürzungen (data/glossar-sozialrecht.json) werden Glossareinträge,
 *   nur mit Auflösung: Taddi erklärt sie nicht weiter, und eine Erklärung
 *   erfinden wir nicht. Offensichtliche Tippfehler und falsche Auflösungen
 *   sind korrigiert (Liste in der Übergabe-Checkliste); was sich nicht klären
 *   ließ, bleibt Entwurf.
 *
 * GdB, Pflegegrad und Persönliches Budget gab es schon, als unsere Entwürfe
 * mit Erklärung (GlossarSeeder). Taddis Liste nennt sie mit Abkürzung; sie
 * werden damit sichtbar, unsere ungeprüfte Erklärung fällt weg. Was der
 * Verein an einem Eintrag schon geändert hat, bleibt.
 *
 * Legt nur an, was fehlt.
 */
class GlossarSozialrechtSeeder extends Seeder
{
    public const SLUG = 'glossar';

    public const TITEL = 'Glossar';

    public const UEBERSCHRIFT = 'Abkürzungen im Sozialen Entschädigungsrecht (SER)';

    public const ABSAETZE = [
        'SGB XIV, OEG, GdS, BSA, AusglV … und Du fragst Dich irgendwann nur noch: '
            .'*Was soll das eigentlich alles heißen?*',
        'Im Sozialen Entschädigungsrecht begegnen einem jede Menge Abkürzungen und Fachbegriffe. Hier sammeln '
            .'und erklären wir sie verständlich, damit Du nicht jedes Mal selbst danach suchen musst.',
        '*Denn verstehen sollte nicht schon an drei Buchstaben scheitern.*',
        'Wenn Dir weitere Abkürzungen bekannt sind, hilf uns, dass unsere Datenbank wächst, und sende sie uns '
            .'an kontakt@kein-einzelfall.de.',
        // Im Dokument zwei Zeilen. Als ein Absatz, sonst klappte der
        // Textbaustein (ab sechs Absätzen) den Gruß unter „Weiterlesen“ ein.
        'Das Team von *KE!N EINZELFALL e.V.* – Opferhilfe für Soziale Gerechtigkeit',
    ];

    public const META_DESCRIPTION = 'Glossar von KE!N EINZELFALL e.V.: Abkürzungen und Fachbegriffe aus dem '
        .'Sozialen Entschädigungsrecht, von SGB XIV bis GdS, verständlich aufgelöst.';

    /** So beginnen unsere ungeprüften Entwürfe aus dem GlossarSeeder. */
    private const UNSER_ENTWURF = ['ENTWURF — bitte prüfen.', 'DRAFT — please check.'];

    public function run(): void
    {
        self::anlegen();
    }

    public static function anlegen(): void
    {
        self::seite();

        foreach (self::eintraege() as $eintrag) {
            if (($eintrag['vorhanden'] ?? false) && self::ergaenzen($eintrag)) {
                continue;
            }

            self::neu($eintrag);
        }
    }

    /** @return list<array{slug: string, kuerzel: string, begriff: string, entwurf?: bool, vorhanden?: bool}> */
    public static function eintraege(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/glossar-sozialrecht.json')), true);
    }

    private static function seite(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => self::SLUG, 'locale' => 'de'],
            [
                'titel' => self::TITEL,
                'meta_title' => self::TITEL.' - Kein Einzelfall e.V.',
                'meta_description' => self::META_DESCRIPTION,
                'published_at' => now(),
            ],
        );

        if ($seite->wasRecentlyCreated) {
            $seite->blocks()->create([
                'typ' => 'text',
                'position' => 0,
                'data' => ['titel' => self::UEBERSCHRIFT, 'absaetze' => self::ABSAETZE],
            ]);
        }

        // Taddis Bild „Abkürzungen SER“ (Zettelkasten), Titelbilder::SEITEN.
        Titelbilder::setzen();
    }

    /**
     * Einen Eintrag, den es schon gibt, um Taddis Abkürzung ergänzen.
     *
     * @return bool ob es ihn gab
     */
    private static function ergaenzen(array $eintrag): bool
    {
        $deutsch = GlossaryTerm::where('locale', 'de')->where('slug', $eintrag['slug'])->first();

        if (! $deutsch) {
            return false;
        }

        GlossaryTerm::where('uebersetzungs_gruppe', $deutsch->uebersetzungs_gruppe)->get()
            ->each(function (GlossaryTerm $fassung) use ($eintrag) {
                $aenderung = [];

                if (blank($fassung->kuerzel)) {
                    $aenderung['kuerzel'] = $eintrag['kuerzel'];
                }

                if ($fassung->published_at === null && self::unserEntwurf($fassung->erklaerung)) {
                    $aenderung += ['published_at' => now(), 'erklaerung' => null];
                }

                if ($aenderung) {
                    $fassung->update($aenderung);
                }
            });

        return true;
    }

    private static function neu(array $eintrag): void
    {
        // Hat der Verein dieselbe Abkürzung mit derselben Auflösung schon
        // selbst eingetragen, nicht noch einmal.
        $schonDa = GlossaryTerm::where('locale', 'de')
            ->where('kuerzel', $eintrag['kuerzel'])
            ->where('begriff', $eintrag['begriff'])
            ->exists();

        if ($schonDa) {
            return;
        }

        GlossaryTerm::firstOrCreate(
            ['locale' => 'de', 'slug' => $eintrag['slug']],
            [
                'kuerzel' => $eintrag['kuerzel'],
                'begriff' => $eintrag['begriff'],
                'erklaerung' => null,
                'published_at' => ($eintrag['entwurf'] ?? false) ? null : now(),
            ],
        );
    }

    private static function unserEntwurf(?string $erklaerung): bool
    {
        // Leer gilt auch: Dann geht beim Veröffentlichen nichts verloren.
        return $erklaerung === null || Str::startsWith($erklaerung, self::UNSER_ENTWURF);
    }
}
