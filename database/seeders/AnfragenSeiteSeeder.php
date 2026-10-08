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
    public function run(): void
    {
        foreach (Page::where('slug', 'anfragen')->get() as $seite) {
            self::umbauen($seite);
        }
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
