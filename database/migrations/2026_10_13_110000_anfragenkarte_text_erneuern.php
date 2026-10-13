<?php

use App\Models\Page;
use Database\Seeders\StartseiteSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Startseite, „Was wir gemeinsam bewegen“: neuer Text der Karte „Anfragen &
 * Austausch“ von Taddi, der von ihr markierte Satz fett (KEV-51). Nur wo
 * noch der alte Text steht. Der Abschlussblock unten auf der Startseite
 * behält seinen Text, das Ticket meint nur die Karte. Danach die englische
 * Fassung neu, wie bei allen Seiten seit 08.10.2026.
 */
return new class extends Migration
{
    private const ALT = 'Du wünschst einen persönlichen Austausch in Bezug auf das Soziale '
        .'Entschädigungsrecht (OEG/SGB XIV), den Schwerbehindertenausweis und/oder den Pflegegrad, '
        .'oder hast Fragen zu anderen Hilfesystemen, oder möchtest uns etwas mitteilen?';

    public function up(): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->where('locale', 'de')->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'quick_access')->get() as $block) {
                $data = $block->data;
                $geaendert = false;

                foreach ($data['karten'] ?? [] as $i => $karte) {
                    if (($karte['url'] ?? null) === '/anfragen' && ($karte['text'] ?? null) === self::ALT) {
                        $data['karten'][$i]['text'] = StartseiteSeeder::ANFRAGEN_KARTE_TEXT;
                        $geaendert = true;
                    }
                }

                if ($geaendert) {
                    $block->update(['data' => $data]);
                }
            }
        }

        if (Page::where('slug', 'verein')->where('locale', 'de')->exists()) {
            Artisan::call('db:seed', ['--class' => UebersetzungenSeeder::class, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
