<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Die Seite „Satzung“ nach Taddis Tickets (KEV-194 ff.).
 *
 * Aufgerufen von der Migration (bestehende Datenbanken) und am Ende des
 * AltseiteSeeders (frische): Die Seite legt erst der an. Ändert nur, was
 * noch so dasteht wie vorher.
 */
class SatzungsseiteSeeder extends Seeder
{
    /*
     * Abschnitt „Satzung lesen“ mit Taddis Überschrift und Text (KEV-194).
     * „kontakt@kein.einezfall.de“ aus ihrem Ticket zur echten Adresse
     * korrigiert. „du“ klein wie im Rest der Seite. Der Knopf zum PDF
     * (KEV-89) bleibt.
     */
    public const LESEN_TITEL = 'Die Satzung lesen';

    public const LESEN_ABSAETZE = [
        'Unsere Satzung – die Grundlage für unser gemeinsames Handeln und unsere Arbeit für mehr soziale Gerechtigkeit.',
        'Hier kannst du unsere Satzung vollständig einsehen.',
        'Wenn du Fragen dazu hast oder eine barrierefreie Version benötigst, melde dich gerne bei uns unter '
            .'kontakt@kein-einzelfall.de.',
    ];

    public function run(): void
    {
        self::fuellen();
    }

    public static function fuellen(): void
    {
        $seite = Page::query()
            ->where('slug', 'satzung')
            ->where('locale', Language::standardCode())
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->first();

        if (! $seite) {
            return;
        }

        self::lesenErneuern($seite);
        self::downloadUeberschrift($seite);
    }

    /** KEV-195: „Unsere Satzung zum Downloaden“ statt der Überschrift von KEV-90. */
    private static function downloadUeberschrift(Page $seite): void
    {
        foreach ($seite->blocks()->where('typ', 'download_list')->get() as $block) {
            if (($block->data['titel'] ?? null) === 'Aktuelle Satzung zum Herunterladen') {
                $block->update(['data' => array_replace($block->data, ['titel' => AltseiteSeeder::DOKUMENTE_TITEL['satzung']])]);
            }
        }
    }

    /**
     * KEV-194: nur der alte Abschnitt der Altseite, erkannt an Überschrift
     * und erstem Satz. Der Rest weicht je nach Stand ab (auf der Demo stand
     * „Satzung Gemeinsam für eine gerechtere Zukunft.“).
     */
    private static function lesenErneuern(Page $seite): void
    {
        $block = $seite->blocks()->where('typ', 'text')->get()->first(fn ($b) => ($b->data['titel'] ?? null) === 'Satzung lesen'
            && ($b->data['absaetze'][0] ?? null) === 'Hier kannst du unsere Satzung vollständig einsehen.');

        $block?->update(['data' => array_replace($block->data, [
            'titel' => self::LESEN_TITEL,
            'absaetze' => self::LESEN_ABSAETZE,
        ])]);
    }
}
