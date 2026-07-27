<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Seite "Barrierefreiheit".
 *
 * Anders als die übrigen Inhalte stammt dieser Text nicht vom Verein: Er
 * beschreibt, was wir technisch umgesetzt haben — das können nur wir wissen.
 * Der Verein kann ihn über das Panel jederzeit anpassen.
 *
 * Der Abschnitt zum Notausgang ist der wichtigste. Wir dürfen nicht den
 * Eindruck erwecken, ein Klick mache den Besuch spurlos — der Browser-Verlauf
 * lässt sich aus einer Webseite heraus nicht löschen. Bei dieser Zielgruppe
 * wäre ein zu großes Versprechen gefährlich.
 */
class BarrierefreiheitSeeder extends Seeder
{
    public function run(): void
    {
        $seite = Page::updateOrCreate(['slug' => 'barrierefreiheit'], [
            'titel' => 'Barrierefreiheit',
            'meta_title' => 'Barrierefreiheit - Kein Einzelfall e.V.',
            'meta_description' => 'Wie diese Seite bedienbar ist: Darstellung anpassen, '
                .'Tastaturbedienung und der Notausgang zum schnellen Verlassen.',
            'published_at' => now(),
        ]);

        $seite->blocks()->delete();

        foreach ($this->abschnitte() as $position => [$titel, $absaetze]) {
            $seite->blocks()->create([
                'typ' => 'text',
                'position' => $position,
                'data' => ['titel' => $titel, 'absaetze' => $absaetze],
            ]);
        }

        $this->command?->info('Seite „Barrierefreiheit" angelegt.');
    }

    private function abschnitte(): array
    {
        return [
            ['Diese Seite schnell verlassen', [
                'Oben rechts findest du den Knopf „Notausgang". Auf dem Handy ist er '
                .'unten in der Leiste immer erreichbar. Ein Druck bringt dich sofort '
                .'auf eine unverfängliche Seite.',

                'Du kannst auch dreimal hintereinander die Taste ESC drücken. Das '
                .'funktioniert auf jeder Seite, ohne dass du erst etwas suchen musst.',

                'Ehrlich dazu: Der Notausgang bringt dich sofort weg und hinterlässt '
                .'für diese eine Seite keinen Eintrag im Verlauf. Frühere Seiten bleiben '
                .'aber im Browser-Verlauf stehen — das können wir von hier aus nicht '
                .'löschen. Wenn niemand sehen soll, dass du hier warst, öffne die Seite '
                .'am besten in einem privaten Fenster (oft Strg+Umschalt+N oder '
                .'Strg+Umschalt+P) oder lösche den Verlauf danach selbst.',
            ]],

            ['Darstellung anpassen', [
                'Neben dem Notausgang findest du ein rundes Symbol. Dahinter kannst du '
                .'die Seite so einstellen, wie du sie am besten lesen kannst:',

                'Schriftgröße in vier Stufen, Zeilen- und Buchstabenabstand, eine gut '
                .'lesbare Schriftart, eine Schrift für Menschen mit Legasthenie, vier '
                .'Kontrast-Einstellungen einschließlich dunkler Ansicht und Graustufen, '
                .'eine Leselinie, die dem Mauszeiger folgt, ein größerer Mauszeiger, '
                .'hervorgehobene Links sowie die Möglichkeit, Bewegungen zu stoppen '
                .'oder Bilder auszublenden.',

                'Deine Einstellungen bleiben auf diesem Gerät gespeichert und gelten '
                .'auch beim nächsten Besuch. Sie liegen nur bei dir im Browser — wir '
                .'erfahren nichts davon.',
            ]],

            ['Bedienung mit der Tastatur', [
                'Die ganze Seite lässt sich ohne Maus bedienen. Mit der Tabulator-Taste '
                .'springst du von Element zu Element, mit der Eingabetaste wählst du aus.',

                'Ganz am Anfang steht ein Sprunglink „Zum Inhalt springen“, mit dem du '
                .'die Navigation überspringen kannst. Auf langen Seiten findest du oben '
                .'ein Inhaltsverzeichnis.',
            ]],

            ['Was wir noch nicht geschafft haben', [
                'Wir arbeiten daran, mindestens die Stufe AA der Richtlinien für '
                .'barrierefreie Webinhalte (WCAG 2.1) zu erfüllen, und gehen an vielen '
                .'Stellen darüber hinaus.',

                'Noch offen: Texte in Leichter Sprache liegen bisher nur für einzelne '
                .'Seiten vor. Auch Beschreibungen für alle Bilder sind noch nicht '
                .'vollständig.',
            ]],

            ['Etwas funktioniert nicht?', [
                'Wenn dir etwas auffällt, das dich beim Lesen oder Bedienen behindert, '
                .'sag uns bitte Bescheid. Wir bessern nach.',
            ]],
        ];
    }
}
