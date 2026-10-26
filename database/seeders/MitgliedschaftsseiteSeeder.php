<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Die Seite „Mitgliedschaft“ nach Taddis Tickets (KEV-196 bis KEV-203).
 *
 * Aufgerufen von den Migrationen (bestehende Datenbanken) und am Ende des
 * AltseiteSeeders (frische): Die Seite legt erst der an. Ändert nur, was
 * noch so dasteht wie vorher.
 */
class MitgliedschaftsseiteSeeder extends Seeder
{
    /** Die PDFs der Seite, wie sie unter „Dokumente zum Herunterladen“ stehen. */
    public const ANTRAG_PDF = '/dokumente/2026/03/1.5.1.-MA-0126.pdf';

    /*
     * KEV-199: Taddis Text aus dem Drive, mit der anonymen Mitgliedschaft.
     * Ihre erste Zeile „Mitglied werden“ ist die Überschrift, jede weitere
     * ein Absatz. Der Vereinsname fett wie auf der Startseite (KEV-168).
     * Dazu ein Knopf zum Antrag: Der Text sagt „beginnt dein Weg genau hier
     * mit unserem Mitgliedsantrag“, und dann soll er auch da sein.
     */
    public const ANTRAG_TITEL = 'Mitglied werden';

    public const ANTRAG_ABSAETZE = [
        'Du möchtest Mitglied bei *KE!N EINZELFALL e.V.* werden?',
        'Dann beginnt dein Weg genau hier mit unserem Mitgliedsantrag.',
        'Uns ist wichtig, Mitgliedschaft so niedrigschwellig wie möglich zu gestalten. Deshalb kannst du bei uns '
            .'auch anonym Mitglied werden. Wenn du deinen Klarnamen nicht angeben möchtest, kannst du einen '
            .'Nickname wählen. Auch die Zahlung deines Mitgliedsbeitrags ist bei einer anonymen Mitgliedschaft über '
            .'PayPal mit deinem Nicknamen möglich und nicht zurückzuverfolgen.',
        'Mit der Möglichkeit einer anonymen Mitgliedschaft möchten wir auch Menschen aus dem Dunkelfeld erreichen '
            .'und die Hürde, sich uns anzuschließen, so niedrig wie möglich halten. Du entscheidest selbst, wie '
            .'sichtbar du bei uns sein möchtest.',
        'Genauso entscheidest du selbst, wie viel du dich einbringen möchtest. Vielleicht fühlst du dich mit '
            .'unserer Vision verbunden und möchtest mit deiner Mitgliedschaft einfach zeigen, dass du hinter '
            .'*KE!N EINZELFALL* und unserer Arbeit stehst. Das ist genauso wertvoll wie aktives Mitwirken. Eine '
            .'Mitgliedschaft bei uns setzt kein aktives Engagement voraus.',
        'Dein Mitgliedsbeitrag hilft uns gleichzeitig dabei, unsere Arbeit verlässlich zu finanzieren, Angebote '
            .'kostenfrei zu halten und neue Projekte möglich zu machen.',
        'Im Mitgliedsantrag trägst du die für deine gewählte Mitgliedschaft notwendigen Angaben ein und wählst die '
            .'passende Mitgliedsform aus. Sobald dein Antrag vollständig bei uns eingegangen ist, können wir deine '
            .'Aufnahme in den Verein prüfen und bearbeiten. Du kannst ihn auch direkt an verwaltung@kein-einzelfall.de '
            .'senden.',
        'Weiter unten findest du außerdem eine Ausfüllhilfe, die dich Schritt für Schritt durch den Antrag begleitet.',
        '*KE!N EINZELFALL* lebt von Menschen, die mittragen, mitdenken und mitgestalten – aktiv oder einfach '
            .'dadurch, dass sie hinter unserer Arbeit stehen.',
    ];

    public const ANTRAG_KNOPF = ['label' => 'Mitgliedsantrag (PDF)', 'url' => self::ANTRAG_PDF, 'variant' => 'primary'];

    public function run(): void
    {
        self::fuellen();
    }

    public static function fuellen(): void
    {
        $seite = Page::query()
            ->where('slug', 'mitgliedschaft')
            ->where('locale', Language::standardCode())
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->first();

        if (! $seite) {
            return;
        }

        self::antragErneuern($seite);
    }

    /**
     * KEV-199: der Abschnitt „Antrag auf Mitgliedschaft“, solange darin noch
     * ein früherer Text steht (KEV-94 oder der der Altseite, beide beginnen
     * mit einem dieser Sätze).
     */
    private static function antragErneuern(Page $seite): void
    {
        $block = $seite->blocks()->where('typ', 'text')->get()->first(fn ($b) => ($b->data['titel'] ?? null) === 'Antrag auf Mitgliedschaft'
            && preg_match('/^(Du möchtest Mitglied bei KE!N EINZELFALL e\.V\. werden\? Dann|Für alle zukünftigen Mitglieder)/u', $b->data['absaetze'][0] ?? ''));

        $block?->update(['data' => array_replace($block->data, [
            'titel' => self::ANTRAG_TITEL,
            'absaetze' => self::ANTRAG_ABSAETZE,
            'cta' => self::ANTRAG_KNOPF,
            'alles_zeigen' => true,
        ])]);
    }
}
