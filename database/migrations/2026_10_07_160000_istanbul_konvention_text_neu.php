<?php

use App\Models\Page;
use Database\Seeders\AltseiteSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Istanbul-Konvention (KEV-101): neuer Text von Taddi, die Konvention selbst
 * als Link vor „Unsere Haltung“, die Liste heisst „Zum Nachlesen“ statt
 * „Dokumente zum Herunterladen“, neue Beschreibung für Suchmaschinen.
 * Alles nur, wo noch der Stand der Altseite steht.
 */
return new class extends Migration
{
    private const SLUG = 'istanbul-konvention';

    private const ALT = 'Gewalt gegen Frauen, Mädchen und alle von geschlechtsspezifischer Gewalt betroffenen Menschen i'
        .'st kein Einzelfall – sie ist ein strukturelles Problem. Die Istanbul Konvention ist das stärkst'
        .'e internationale Schutzinstrument, das Betroffene davor bewahren soll, übersehen, nicht gehört '
        .'oder allein gelassen zu werden.Als KE!N EINZELFALL e. V. erkennen wir die Istanbul Konvention a'
        .'usdrücklich an und verstehen ihre Grundsätze als zentrale Orientierung für unsere Arbeit. Sie s'
        .'tärkt die Rechte der Betroffenen, verpflichtet zu Schutz, Prävention, Unterstützung und Sensibi'
        .'lisierung – Werte, die tief in unserer Vereinsarbeit verankert sind.Mit unserer öffentlichen Un'
        .'terstützung der Istanbul Konvention machen wir deutlich:Betroffene verdienen Schutz, Respekt, S'
        .'icherheit und verlässliche Hilfsstrukturen. Wir setzen uns dafür ein, Lücken im Hilfesystem sic'
        .'htbar zu machen und überall dort zu füllen, wo staatliche Angebote noch fehlen oder unzureichen'
        .'d sind.Hier findest du unseren vollständigen Text zur Istanbul Konvention und warum sie für uns'
        .'ere Arbeit so wichtig ist.';

    private const ALTE_BESCHREIBUNG = 'Wir setzen uns für soziale Gerechtigkeit und die Unterstützung von Opfern von Gewalt und Missbrauch ein und fordert mehr Sichtbarkeit und Gehör für Opfer.';

    public function up(): void
    {
        $neu = AltseiteSeeder::DOKUMENTE_DAVOR[self::SLUG];

        foreach (Page::where('slug', self::SLUG)->get() as $seite) {
            foreach ($seite->blocks as $block) {
                $data = $block->data ?? [];

                if ($block->typ === 'text' && blank($data['titel'] ?? null) && ($data['absaetze'] ?? null) === [self::ALT]) {
                    $block->update(['data' => array_replace($data, ['absaetze' => AltseiteSeeder::NEUE_TEXTE[self::SLUG]['']])]);
                }

                // Nur in die Liste der Altseite, und nur einmal.
                $urls = array_column($data['dokumente'] ?? [], 'url');
                if ($block->typ === 'download_list' && $urls !== [] && ! in_array($neu[0]['url'], $urls, true)) {
                    $data = array_replace($data, ['dokumente' => [...$neu, ...$data['dokumente']]]);

                    if (($data['titel'] ?? null) === 'Dokumente zum Herunterladen') {
                        $data['titel'] = AltseiteSeeder::DOKUMENTE_TITEL[self::SLUG];
                    }

                    $block->update(['data' => $data]);
                }
            }
        }

        Page::where('slug', self::SLUG)->where('meta_description', self::ALTE_BESCHREIBUNG)
            ->update(['meta_description' => AltseiteSeeder::NEUE_BESCHREIBUNGEN[self::SLUG]]);
    }

    public function down(): void
    {
        $url = AltseiteSeeder::DOKUMENTE_DAVOR[self::SLUG][0]['url'];

        foreach (Page::where('slug', self::SLUG)->get() as $seite) {
            foreach ($seite->blocks as $block) {
                $data = $block->data ?? [];

                if ($block->typ === 'text' && ($data['absaetze'] ?? null) === AltseiteSeeder::NEUE_TEXTE[self::SLUG]['']) {
                    $block->update(['data' => array_replace($data, ['absaetze' => [self::ALT]])]);
                }

                if ($block->typ === 'download_list') {
                    $rest = array_values(array_filter($data['dokumente'] ?? [], fn ($d) => ($d['url'] ?? null) !== $url));
                    $titel = ($data['titel'] ?? null) === AltseiteSeeder::DOKUMENTE_TITEL[self::SLUG]
                        ? 'Dokumente zum Herunterladen' : ($data['titel'] ?? null);
                    $block->update(['data' => array_replace($data, ['dokumente' => $rest, 'titel' => $titel])]);
                }
            }
        }

        Page::where('slug', self::SLUG)->where('meta_description', AltseiteSeeder::NEUE_BESCHREIBUNGEN[self::SLUG])
            ->update(['meta_description' => self::ALTE_BESCHREIBUNG]);
    }
};
