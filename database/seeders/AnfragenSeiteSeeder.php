<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * /anfragen bekommt sein Formular (Prüfung der Firma, 08.10.2026).
 *
 * Seit dem Import der Altseite standen dort unter „Kontaktformular“ nur die
 * Feldbeschriftungen von Elementor als Text: „Dein Name / Deine E-Mail Adresse
 * / Betreff / Deine Nachricht an uns (optional)“. Ein Formular gab es nirgends,
 * obwohl alle Knöpfe „Anfrage stellen“ hierher führen.
 *
 * Gleichzeitig die Krisennummern: Die Altseite nannte dieselbe Nummer zweimal
 * als verschiedene Stellen, den Krisendienst Bayern ohne Hinweis darauf und
 * versprach „jederzeit“, obwohl eine Hotline feste Zeiten hat. Statt zweier
 * Textblöcke steht hier jetzt der Baustein „Hilfe-Nummern“. Er liest aus
 * config/hilfe.php, derselben geprüften Liste wie Startseite und Fusszeile.
 *
 * Reihenfolge danach: Einleitung, Ablauf, Formular, Hilfe-Nummern.
 *
 * Erkennt die Altseiten-Blöcke an ihrem Inhalt, nicht an der Position. Was der
 * Verein schon umgebaut hat, bleibt.
 */
class AnfragenSeiteSeeder extends Seeder
{
    /** KEV-274: Titel wie im Menü, Unterzeile von Taddi. */
    public const TITEL = 'Anfragen & Austausch';

    public const UNTERZEILE_ALT = 'Persönlicher Austausch zu Entschädigung, Schwerbehinderung und Pflegegrad.';

    public const UNTERZEILE = 'Was dich bewegt, bewegt auch uns.';

    public const EINLEITUNG_TITEL = 'Der Anfrage- und Austausch-Service von KE!N EINZELFALL e.V.';

    public const EINLEITUNG_ALT = [
        'Opferhilfe für soziale Gerechtigkeit bedeutet auch, Raum für Austausch zu schaffen. Du wünschst dir '
            .'einen persönlichen Austausch in Bezug auf das Soziale Entschädigungsrecht (SER), den '
            .'Schwerbehindertenausweis oder den Pflegegrad? Hast sonstige Fragen oder möchtest uns etwas '
            .'mitteilen? Dann bist du hier richtig.',
    ];

    /** KEV-275, Taddis Dokument „Aufbau Kontakt“, Absätze wie dort. */
    public const EINLEITUNG = [
        'Opferhilfe für soziale Gerechtigkeit bedeutet für uns auch, zuzuhören, Wissen zu teilen und miteinander '
            .'neue Wege zu finden.',
        'Du wünschst dir einen persönlichen Austausch zum Sozialen Entschädigungsrecht (SER), zum '
            .'Schwerbehindertenausweis, zum Pflegegrad oder zu anderen Hilfesystemen?',
        'Dann bist du hier richtig! Ein wichtiger Hinweis: Wenn du uns persönliche Unterlagen zusenden möchtest, '
            .'bitten wir dich, vorher die unten aufgeführte „Freigabe zur Verarbeitung und Speicherung '
            .'übermittelter Unterlagen“ auszufüllen und mit zu übermitteln. Ohne diese Freigabe können wir deine '
            .'Unterlagen aus Datenschutzgründen leider nicht entgegennehmen oder bearbeiten.',
        'Auch deine Ideen sind bei uns willkommen! Vielleicht hast du Anregungen, die unsere Homepage wachsen '
            .'lassen, möchtest dein Wissen oder deine Kreativität einbringen oder hast einfach den Wunsch, '
            .'gemeinsam mit uns etwas zu bewegen. Wir freuen uns über Menschen, die mitdenken, mitgestalten und '
            .'neue Perspektiven eröffnen.',
        'Ganz gleich, ob du Unterstützung suchst, Fragen stellst oder etwas verändern möchtest – wir freuen uns '
            .'auf den kostenfreien und nicht an eine Mitgliedschaft gebundenen Austausch mit dir.',
    ];

    public const ABLAUF_TITEL = 'Wie funktioniert der Service?';

    public const ABLAUF_ALT = [
        'Du beschreibst uns dein Anliegen kurz (per E-Mail oder über das Formular unten). So können wir deine '
            .'Nachricht an die richtige Ansprechperson in unserem Team weiterleiten. Danach melden wir uns so '
            .'schnell wie möglich – spätestens innerhalb von 48 Stunden – bei dir zurück.',
    ];

    /**
     * KEV-276. „per E-Mail an kontakt@…“: Das „an“ fehlte bei Taddi. Die
     * 48 Stunden sind raus, das Team antwortet, sobald es geht.
     */
    public const ABLAUF = [
        'Du beschreibst uns dein Anliegen kurz per E-Mail an kontakt@kein-einzelfall.de oder über das '
            .'untenstehende Formular. So können wir deine Nachricht an die passende Ansprechperson in unserem Team '
            .'weiterleiten.',
        'Bitte denke daran: Wenn du uns persönliche Unterlagen übermittelst, benötigen wir die unten aufgeführte '
            .'„Freigabe zur Verarbeitung und Speicherung übermittelter Unterlagen“. Ohne diese können wir deine '
            .'Unterlagen leider nicht entgegennehmen oder bearbeiten.',
        'Wir melden uns bei dir, sobald es uns möglich ist. Bitte hab Verständnis dafür, dass eine Antwort '
            .'manchmal etwas Zeit braucht. Unser Team besteht fast ausschließlich aus Menschen, die selbst von '
            .'belastenden Erfahrungen betroffen sind und neben ihrem Engagement eigene Herausforderungen '
            .'bewältigen.',
        'Jedes Anliegen ist uns wichtig, und wir nehmen uns die Zeit, die es verdient.',
    ];

    /**
     * KEV-275: die Freigabe, auf die beide Texte verweisen, direkt unter dem
     * Ablauf. Nicht daneben: Einleitung und Ablauf stehen als ein Artikel mit
     * Verzeichnis, daneben ist kein Platz.
     */
    public const FREIGABE = [
        'titel' => 'Freigabe für deine Unterlagen',
        'dokumente' => [[
            'titel' => 'Freigabe zur Verarbeitung und Speicherung übermittelter Unterlagen (ausfüllbar)',
            'url' => '/dokumente/2026/10/Freigabe-Verarbeitung-und-Speicherung.pdf',
        ]],
    ];

    /**
     * KEV-275: Die Hilfe-Nummern heißen „Krisendienste“ und stehen vor dem
     * Formular. Auf der Demo stand die Überschrift noch als leerer Abschnitt
     * aus der Altseite da, die Nummern erst unter dem Formular.
     */
    public const KRISEN_TITEL = 'Krisendienste';

    public function run(): void
    {
        foreach (Page::where('slug', 'anfragen')->get() as $seite) {
            self::umbauen($seite);
        }

        self::feinschliff();
    }

    /**
     * Taddis Tickets KEV-274 bis KEV-276 auf der deutschen Seite. Tauscht nur,
     * wo noch der alte Wortlaut steht; der Rest läuft einmal und nur, wenn die
     * Freigabe noch fehlt.
     */
    public static function feinschliff(): void
    {
        $seite = Page::where('slug', 'anfragen')->where('locale', 'de')->where('fassung', Page::FASSUNG_STANDARD)->first();

        if (! $seite) {
            return;
        }

        if ($seite->titel === 'Anfragen') {
            $seite->titel = self::TITEL;
        }
        if ($seite->meta_title === 'Anfragen - Kein Einzelfall e.V.') {
            $seite->meta_title = self::TITEL.' - Kein Einzelfall e.V.';
        }
        if ($seite->untertitel === self::UNTERZEILE_ALT) {
            $seite->untertitel = self::UNTERZEILE;
        }
        $seite->save();

        $bloecke = $seite->blocks()->orderBy('position')->get();

        foreach ([[self::EINLEITUNG_TITEL, self::EINLEITUNG_ALT, self::EINLEITUNG], [self::ABLAUF_TITEL, self::ABLAUF_ALT, self::ABLAUF]] as [$titel, $alt, $neu]) {
            $block = $bloecke->first(fn ($b) => $b->typ === 'text' && ($b->data['titel'] ?? null) === $titel
                && ($b->data['absaetze'] ?? null) === $alt);
            $block?->update(['data' => array_replace($block->data, ['absaetze' => $neu])]);
        }

        if ($bloecke->contains(fn ($b) => $b->typ === 'download_list'
            && collect($b->data['dokumente'] ?? [])->contains('url', self::FREIGABE['dokumente'][0]['url']))) {
            return;
        }

        // Die leere Überschrift der Altseite weg, die Nummern bekommen sie.
        $bloecke->filter(fn ($b) => $b->typ === 'text' && ($b->data['titel'] ?? null) === self::KRISEN_TITEL
            && count($b->data['absaetze'] ?? []) <= 1)->each->delete();

        $hilfe = $bloecke->firstWhere('typ', 'hilfe_box');
        if ($hilfe && ($hilfe->data['titel'] ?? null) === 'Für den Notfall') {
            $hilfe->update(['data' => array_replace($hilfe->data, ['titel' => self::KRISEN_TITEL])]);
        }

        $ablauf = $bloecke->first(fn ($b) => $b->typ === 'text' && ($b->data['titel'] ?? null) === self::ABLAUF_TITEL);
        $freigabe = $seite->blocks()->create([
            'typ' => 'download_list',
            'position' => ($ablauf?->position ?? 0) + 1,
            'data' => self::FREIGABE,
        ]);

        // Reihenfolge: alles bis zum Ablauf, die Freigabe darunter, dann die
        // Krisendienste, dann das Formular und was sonst folgt.
        $uebrige = $seite->blocks()->reorder()->orderBy('position')->get()
            ->reject(fn ($b) => $b->is($freigabe) || ($hilfe && $b->is($hilfe)));
        [$davor, $danach] = $uebrige->partition(fn ($b) => $ablauf && $b->position <= $ablauf->position);

        collect([...$davor, $freigabe, ...($hilfe ? [$hilfe->fresh()] : []), ...$danach])
            ->each(fn ($b, $i) => $b->update(['position' => $i]));
    }

    /** @return bool true, wenn umgebaut */
    public static function umbauen(Page $seite): bool
    {
        $bloecke = $seite->blocks()->orderBy('position')->get();

        $formular = $bloecke->first(fn ($b) => $b->typ === 'text'
            && in_array($b->data['absaetze'] ?? null, [
                ['Dein Name', 'Deine E-Mail Adresse', 'Betreff', 'Deine Nachricht an uns (optional)'],
                ['Your name', 'Your e-mail address', 'Subject', 'Your message to us (optional)'],
            ], true));

        if (! $formular) {
            return false;
        }

        $notfall = $bloecke->filter(fn ($b) => $b->typ === 'text' && (
            str_starts_with($b->data['absaetze'][0] ?? '', 'Polizei: 110')
            || collect($b->data['absaetze'] ?? [])->contains(fn ($a) => str_contains($a, '0800 / 6553000'))
        ));

        $englisch = $seite->locale === 'en';

        // Der Satz zum Ablauf verwies auf ein Formular „auf anderen Seiten“.
        foreach ($bloecke as $block) {
            $absaetze = $block->data['absaetze'] ?? null;
            if (! is_array($absaetze)) {
                continue;
            }
            $neu = str_replace(
                ['(per E-Mail oder Formular auf anderen Seiten)', '(by e-mail or via a form on other pages)'],
                ['(per E-Mail oder über das Formular unten)', '(by e-mail or using the form below)'],
                $absaetze,
            );
            if ($neu !== $absaetze) {
                $block->update(['data' => array_replace($block->data, ['absaetze' => $neu])]);
            }
        }

        $formular->update([
            'typ' => 'contact_form',
            'data' => ['titel' => $formular->data['titel'] ?? 'Kontaktformular'],
        ]);

        $titel = $englisch ? 'In an emergency' : 'Für den Notfall';
        $notfall->each->delete();
        $seite->blocks()->create(['typ' => 'hilfe_box', 'position' => 0, 'data' => ['titel' => $titel]]);

        // Lückenlos neu durchnummerieren, die Hilfe-Nummern ans Ende.
        // reorder(): Die Relation sortiert schon nach Position.
        $seite->blocks()->reorder()->orderByRaw("typ = 'hilfe_box'")->orderBy('position')->get()
            ->each(fn ($b, $i) => $b->update(['position' => $i]));

        return true;
    }
}
