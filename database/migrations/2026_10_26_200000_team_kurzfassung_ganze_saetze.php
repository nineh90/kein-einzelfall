<?php

use App\Models\TeamMember;
use Database\Seeders\TeamUndGruppenSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Kurzfassungen im Team nach dem letzten ganzen Satz statt mitten im Wort
 * (KEV-187, Taddi bei „Herr und Frau Unbekannt“; genauso bei Tanja Häfner
 * und André Bauer). Nur wo die Kurzfassung noch der abgeschnittene Anfang
 * des ausführlichen Textes ist, also nicht im Panel umgeschrieben wurde.
 * Deutsch und Englisch.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (TeamMember::all() as $person) {
            $person->kurzprofil = $this->kuerzen($person->getRawOriginal('kurzprofil'), $person->getRawOriginal('profil'));

            $uebersetzungen = $person->uebersetzungen ?? [];
            foreach ($uebersetzungen as $sprache => $felder) {
                if (isset($felder['kurzprofil'], $felder['profil'])) {
                    $uebersetzungen[$sprache]['kurzprofil'] = $this->kuerzen($felder['kurzprofil'], $felder['profil']);
                }
            }
            $person->uebersetzungen = $uebersetzungen;

            $person->save();
        }
    }

    /** Neu gekürzt, wenn die Kurzfassung „…“ endet und der Anfang des Textes ist. Sonst unverändert. */
    private function kuerzen(?string $kurz, ?string $profil): ?string
    {
        if (! $kurz || ! $profil || ! preg_match('/(\.\.\.|…)\s*$/u', $kurz)) {
            return $kurz;
        }

        $anfang = preg_replace('/(\.\.\.|…)\s*$/u', '', $kurz);
        $text = trim(html_entity_decode(strip_tags(str_replace('</p>', "</p>\n", $profil)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $absatz = collect(preg_split('/\n+/u', $text))->map(fn ($a) => trim($a))->first(fn ($a) => str_starts_with($a, $anfang));

        return $absatz ? TeamUndGruppenSeeder::kurzfassung($absatz) : $kurz;
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
