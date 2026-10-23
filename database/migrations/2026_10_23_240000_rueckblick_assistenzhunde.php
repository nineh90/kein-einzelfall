<?php

use App\Models\Page;
use Database\Seeders\RueckblickSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Rückblick: „Unsere Veranstaltungen rund um Assistenzhunde“ (KEV-138),
 * zwischen „Trauma, Bindung und Beziehung“ und FSM. Legt nur an, was fehlt.
 *
 * Damit die Bilder weiter abwechseln, tauschen FSM (bisher links) und
 * aus-unserer-sicht (bisher rechts) die Seite, aber nur, wenn der Abschnitt
 * hier neu dazukommt und der Verein die beiden nicht schon umgestellt hat.
 * Danach die englische Fassung (nur auf einer eingerichteten Datenbank).
 */
return new class extends Migration
{
    /** Bildseite vorher → nachher */
    private const SEITEN = [
        RueckblickSeeder::FSM_BILD => ['links', 'rechts'],
        RueckblickSeeder::AUS_BILD => ['rechts', 'links'],
    ];

    public function up(): void
    {
        $neu = ! $this->bloecke()->contains(fn ($b) => ($b->data['bild'] ?? null) === RueckblickSeeder::ASSISTENZ_BILD);

        RueckblickSeeder::anlegen();

        if ($neu) {
            $this->seitenTauschen(0, 1);
        }

        if (! Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            return;
        }

        Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
    }

    public function down(): void
    {
        $this->seitenTauschen(1, 0);

        Page::where('slug', RueckblickSeeder::SLUG)->each(function (Page $seite) {
            $seite->blocks->filter(fn ($b) => ($b->data['bild'] ?? null) === RueckblickSeeder::ASSISTENZ_BILD)
                ->each->delete();
        });
    }

    /**
     * Die Bausteine des deutschen Rückblicks. Die englische Fassung baut der
     * UebersetzungenSeeder danach neu auf; eine geprüfte gehört dem Verein.
     */
    private function bloecke()
    {
        return Page::where('slug', RueckblickSeeder::SLUG)->where('locale', 'de')->with('blocks')->get()->flatMap->blocks;
    }

    private function seitenTauschen(int $von, int $nach): void
    {
        $this->bloecke()
            ->filter(fn ($b) => isset(self::SEITEN[$b->data['bild'] ?? null])
                && ($b->data['bild_seite'] ?? null) === self::SEITEN[$b->data['bild']][$von])
            ->each(fn ($b) => $b->update(['data' => array_merge($b->data, [
                'bild_seite' => self::SEITEN[$b->data['bild']][$nach],
            ])]));
    }
};
