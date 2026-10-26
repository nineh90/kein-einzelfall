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

    public const AUSFUELLHILFE_PDF = '/dokumente/2026/03/1.5.1.1.-MA-AH-0126.pdf';

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

    /*
     * KEV-198: Taddis Ankündigung der Ausfüllhilfe als eigener Abschnitt,
     * direkt nach „Mitglied werden“ (KEV-197: Wir brauchen dich, Antrag,
     * Ausfüllhilfe, Beitrags- und Mitgliederordnung). Mit Knopf zum PDF.
     */
    public const AUSFUELLHILFE = [
        'titel' => 'Ausfüllhilfe zum Mitgliedsantrag',
        'absaetze' => [
            'Du möchtest Mitglied bei *KE!N EINZELFALL* werden, bist dir beim Ausfüllen des Antrags aber an '
                .'manchen Stellen unsicher?',
            'Das ist völlig in Ordnung. Unsere Ausfüllhilfe begleitet dich Schritt für Schritt durch den '
                .'Mitgliedsantrag und erklärt dir verständlich, was wo eingetragen, angekreuzt oder unterschrieben '
                .'werden muss.',
            'Du musst dich nicht allein durch das Formular arbeiten. Wenn etwas unklar bleibt oder du beim '
                .'Ausfüllen Unterstützung brauchst, melde dich gerne bei uns unter kontakt@kein-einzelfall.de.',
            'Wir möchten, dass dein Weg zur Mitgliedschaft so einfach und verständlich wie möglich ist.',
        ],
        'cta' => ['label' => 'Ausfüllhilfe (PDF)', 'url' => self::AUSFUELLHILFE_PDF, 'variant' => 'primary'],
    ];

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
        self::ausfuellhilfeEinfuegen($seite);
    }

    /** KEV-198: direkt hinter „Mitglied werden“, nur einmal. */
    private static function ausfuellhilfeEinfuegen(Page $seite): void
    {
        $bloecke = $seite->blocks()->orderBy('position')->get();
        $antrag = $bloecke->first(fn ($b) => $b->typ === 'text' && ($b->data['titel'] ?? null) === self::ANTRAG_TITEL);

        if (! $antrag || $bloecke->contains(fn ($b) => ($b->data['titel'] ?? null) === self::AUSFUELLHILFE['titel'])) {
            return;
        }

        $bloecke->where('position', '>', $antrag->position)
            ->each(fn ($b) => $b->update(['position' => $b->position + 1]));

        $seite->blocks()->create([
            'typ' => 'text',
            'position' => $antrag->position + 1,
            'data' => self::AUSFUELLHILFE,
        ]);
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
