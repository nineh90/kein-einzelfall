<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Seeder;

/**
 * Schutz- und Wertekonzept und Red Flags unter Verein (KEV-97), Text von
 * Taddi, Absätze von uns gesetzt. Im Menü „Verein“ an Position 4 und 5, so
 * wollte es Taddi („weil wir uns wichtig nehmen wollen“).
 *
 * Das Schutz- und Wertekonzept füllt den Entwurf „Schutzkonzept“, der seit
 * dem 05.08.2026 leer bereitlag (NeueBereicheSeeder), und bekommt dabei
 * Taddis Titel und die passende Adresse. Veröffentlicht war er nie, eine
 * Weiterleitung braucht es also nicht. Das vollständige Konzept als PDF liegt
 * uns noch nicht vor (Rückfrage in der Übergabe-Checkliste); der Verein hängt
 * es im Panel als Baustein „Dokumente“ an.
 *
 * Red Flags ist neu. Daran hängt der Leitfaden der Altseite („6.1.2. Must
 * haves u. Red Flaggs für Betroffene“, 01/26): Er ist genau der Leitfaden,
 * den Taddis Text ankündigt. „Red Flags“ statt „Red Flaggs“, wie im PDF
 * selbst.
 *
 * Beide mit Platzhalterbild, bis Taddi Bilder schickt. Füllt nur, was fehlt
 * oder noch leer ist.
 */
class SchutzkonzeptSeeder extends Seeder
{
    /** Slug des leeren Entwurfs vom 05.08.2026. */
    public const ALTER_SLUG = 'schutzkonzept';

    public const SLUG = 'schutz-und-wertekonzept';

    public const TITEL = 'Schutz- und Wertekonzept';

    public const ABSAETZE = [
        'Schutz, Würde und Selbstbestimmung sind Grundlagen unserer Arbeit.',
        'Unser Schutz- und Wertekonzept beschreibt, wie wir sichere, respektvolle und verlässliche '
            .'Rahmenbedingungen schaffen. Es hält fest, welche Werte und Grenzen bei KE!N EINZELFALL e.V. gelten '
            .'und wie wir mit Beschwerden, Grenzverletzungen und Schutzbedarfen umgehen.',
        'Für uns gilt dabei: Der Mensch steht vor dem Verfahren. Immer.',
        'Hier kannst du unser vollständiges Schutz- und Wertekonzept einsehen.',
    ];

    public const META_DESCRIPTION = 'Das Schutz- und Wertekonzept von KE!N EINZELFALL e.V.: welche Werte und '
        .'Grenzen bei uns gelten und wie wir mit Beschwerden, Grenzverletzungen und Schutzbedarfen umgehen.';

    public const RED_FLAGS_SLUG = 'red-flags';

    public const RED_FLAGS_TITEL = 'Red Flags';

    public const RED_FLAGS_ABSAETZE = [
        'Gemeinsam gelingt Zusammenarbeit am besten, wenn sie auf gegenseitigem Respekt, Vertrauen und klaren '
            .'Grenzen basiert. In diesem Leitfaden erfährst du, welche Grundsätze uns im Umgang miteinander wichtig '
            .'sind, welche Verhaltensweisen eine gute Zusammenarbeit fördern und wo unsere Grenzen liegen.',
        'Diese Hinweise sollen nicht abschrecken – sie schaffen einen sicheren Rahmen für dich und für unsere '
            .'ehrenamtlichen Mitarbeitenden, die fast alle selbst betroffen sind.',
        'Denn nur wenn wir respektvoll miteinander umgehen und aufeinander achten, können wir langfristig '
            .'Sichtbarkeit, Gehör und Unterstützung für Betroffene schaffen.',
    ];

    public const RED_FLAGS_META_DESCRIPTION = 'Red Flags bei KE!N EINZELFALL e.V.: Grundsätze, Grenzen und '
        .'No-Gos für eine respektvolle Zusammenarbeit mit unseren ehrenamtlichen Mitarbeitenden. Zum Herunterladen.';

    public const RED_FLAGS_DOKUMENTE = [
        'titel' => 'Leitfaden herunterladen',
        'dokumente' => [[
            'titel' => 'Leitfaden für die Zusammenarbeit: Grenzen, Red Flags und No-Gos',
            'url' => '/dokumente/2026/07/6.1.2.-Must-haves-u.-Red-Flaggs-fuer-Betroffene.pdf',
            'bytes' => 165792,
        ]],
    ];

    public function run(): void
    {
        self::anlegen();
    }

    public static function anlegen(): void
    {
        self::schutzkonzept();
        self::redFlags();

        Titelbilder::platzhalterSetzen();
    }

    private static function schutzkonzept(): void
    {
        $seite = Page::where('locale', 'de')->whereIn('slug', [self::SLUG, self::ALTER_SLUG])->first()
            ?? Page::create([
                'slug' => self::SLUG,
                'locale' => 'de',
                'titel' => self::TITEL,
                'meta_title' => self::TITEL.' - Kein Einzelfall e.V.',
            ]);

        $leer = $seite->blocks()->get()->every(fn ($b) => $b->typ === 'text' && blank($b->data));

        // Hat der Verein schon etwas eingetragen, gehört die Seite ihm.
        if (! $leer) {
            return;
        }

        $seite->blocks()->delete();
        $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => self::ABSAETZE]]);

        $seite->forceFill([
            'slug' => self::SLUG,
            'titel' => self::TITEL,
            'meta_title' => self::TITEL.' - Kein Einzelfall e.V.',
            'meta_description' => $seite->meta_description ?: self::META_DESCRIPTION,
            'published_at' => $seite->published_at ?? now(),
        ])->save();
    }

    private static function redFlags(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => self::RED_FLAGS_SLUG, 'locale' => 'de'],
            [
                'titel' => self::RED_FLAGS_TITEL,
                'meta_title' => self::RED_FLAGS_TITEL.' - Kein Einzelfall e.V.',
                'meta_description' => self::RED_FLAGS_META_DESCRIPTION,
                'published_at' => now(),
            ],
        );

        if ($seite->wasRecentlyCreated) {
            $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => ['absaetze' => self::RED_FLAGS_ABSAETZE]]);
            $seite->blocks()->create(['typ' => 'download_list', 'position' => 1, 'data' => self::RED_FLAGS_DOKUMENTE]);
        }
    }
}
