<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Die Seite „Gemeinnütziger Verein“ nach Taddis Tickets (KEV-173 ff.).
 *
 * Aufgerufen von der Migration (bestehende Datenbanken) und am Ende des
 * AltseiteSeeders (frische): Die Seite legt erst der an. Ändert nur, was
 * noch so dasteht wie vorher. Hat der Verein im Panel schon etwas geändert,
 * bleibt es.
 */
class VereinsseiteSeeder extends Seeder
{
    /**
     * Der Text der Altseite, wie er bis KEV-173 dastand.
     */
    public const ALTE_ABSAETZE = [
        'Der KE!N EINZELFALL e.V. wurde 2024 gegründet – aus einer persönlichen Betroffenheit heraus und mit dem '
            .'Ziel, von schädigenden Taten betroffene Menschen nicht länger allein zu lassen.',
        'Unsere Aufgabe: Wir schaffen eine Austausch- und Informationsplattform für Opfer, Mitopfer, Angehörige, '
            .'Interessierte und Fachpersonen. Ein zentrales Netzwerk aus Expertise im Betroffenenkontext mit '
            .'Austausch auf Augenhöhe. Wir leisten Aufklärung und geben Betroffenen eine Stimme. Für mehr '
            .'Sichtbarkeit und Gehör.',
        'Unsere Vorstandsebene und unsere Mitglieder bestehen aus Persönlichkeiten, die sich nicht nur durch ihre '
            .'fachliche Kompetenz auszeichnen, sondern auch durch ihre persönliche Verbundenheit. Sie bringen '
            .'unterschiedliche Perspektiven, Erfahrungen und Stärken mit – vereint durch die Motivation: '
            .'Opferhilfe für soziale Gerechtigkeit!',
        'Der KE!N EINZELFALL e.V. baut sein Netzwerk und seine Kooperationen mit anderen Akteuren beständig aus. '
            .'Unter anderem kooperiert er mit Bundesverband ANUAS e.V., Gegen Missbrauch e.V., Tour 41 und KISS '
            .'Hamburg.',
        'Für Sichtbarkeit, Gehör und Unterstützung. Gemeinsam KE!N EINZELFALL!',
    ];

    /*
     * Text von Taddi (KEV-173). Der Vereinsname fett wie auf der Startseite
     * (KEV-168), „e. V.“ ohne Leerzeichen wie überall auf der Seite, ein
     * doppeltes Leerzeichen vor „sie sind auch“ entfernt.
     */
    public const ABSAETZE = [
        '*KE!N EINZELFALL e.V.* wurde 2024 aus persönlicher Betroffenheit heraus gegründet. Aus Erfahrung wurde '
            .'Wissen und aus Wissen wurde Betroffenenexpertise.',
        'Heute schaffen wir Räume für Austausch, teilen Wissen und machen sichtbar, was Betroffene bewegt. Wir '
            .'greifen Themen auf, die im Alltag, in Hilfesystemen und in gesellschaftlichen Strukturen oft zu wenig '
            .'gesehen oder verstanden werden.',
        'Wir schaffen Verbindungen zwischen Betroffenen, Angehörigen und Interessierten und fördern den Austausch '
            .'auf Augenhöhe mit Fachpersonen. Dabei geht es uns nicht darum, nur über Betroffene zu sprechen, '
            .'sondern gemeinsam mit ihnen Wissen weiterzuentwickeln, Erfahrungen einzuordnen und neue Perspektiven '
            .'sichtbar zu machen.',
        'Ein wichtiger Teil unserer Arbeit sind Selbsthilfegruppen, Arbeitsgruppen und gemeinsame Projekte. Sie '
            .'schaffen Raum für Austausch, gegenseitige Unterstützung und aktive Mitgestaltung. Denn Selbsthilfe '
            .'bedeutet für uns nicht nur, mit Belastungen umzugehen, sondern auch eigene Ressourcen zu entdecken, '
            .'Wissen zu teilen und wieder mehr Einfluss auf das eigene Leben zu gewinnen.',
        'Wir möchten Selbstwirksamkeit stärken: durch verständliche Informationen, durch Beteiligung, durch '
            .'Austausch und durch die Erfahrung, mit dem eigenen Wissen und den eigenen Fähigkeiten etwas bewegen '
            .'zu können.',
        'Gleichzeitig wollen wir den Blick verändern: Betroffene dürfen nicht nur über ihre '
            .'Traumafolgestörungen, ihre Hilfebedürftigkeit, Diagnosen oder Akten wahrgenommen werden. Sie sind '
            .'mehr – sie sind auch Menschen mit Ressourcen, Fähigkeiten, Wissen, Erfahrungen und eigenen Stärken. '
            .'Denn viel zu oft rückt die Hilfebedürftigkeit mit ihren Diagnosen in den Vordergrund, während der '
            .'Blick auf vorhandene Ressourcen und Stärken verloren geht.',
    ];

    /*
     * Die beiden Schlusszeilen als Leitsatz in Handschrift, wie „Für
     * Sichtbarkeit. Für eine Stimme. Für Unterstützung.“ auf der Startseite.
     */
    public const HAND = 'Für Sichtbarkeit. Für eine Stimme. Für Unterstützung. Gemeinsam KE!N EINZELFALL.';

    public function run(): void
    {
        self::fuellen();
    }

    public static function fuellen(): void
    {
        $seite = self::seite();

        if (! $seite) {
            return;
        }

        self::textErneuern($seite);
    }

    private static function seite(): ?Page
    {
        return Page::query()
            ->where('slug', 'verein')
            ->where('locale', Language::standardCode())
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->first();
    }

    /** KEV-173: Taddis neuer Text, nur wo noch der der Altseite steht. */
    private static function textErneuern(Page $seite): void
    {
        $block = $seite->blocks()->where('typ', 'text')->get()
            ->first(fn ($b) => ($b->data['absaetze'] ?? null) === self::ALTE_ABSAETZE);

        $block?->update(['data' => array_replace($block->data, [
            'absaetze' => self::ABSAETZE,
            'hand' => self::HAND,
            'alles_zeigen' => true,
        ])]);
    }
}
