<?php

use App\Models\Page;
use App\Models\TeamMember;
use App\Support\Textpflege;
use Illuminate\Database\Migrations\Migration;

/**
 * Tippfehler, zusammengeklebte Sätze und Zeilen, veraltete Gesetzesangaben
 * (Prüfung der Firma, 08.10.2026). Die Liste steht in App\Support\Textpflege.
 *
 * Ersetzt nur exakt den alten Wortlaut; was der Verein im Panel schon
 * geändert hat, bleibt. Läuft über alle Sprachfassungen: In den englischen
 * stehen noch deutsche Absätze mit denselben Fehlern.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Page::with('blocks')->get() as $seite) {
            foreach ($seite->blocks as $block) {
                $neu = Textpflege::bausteinDaten($block->data ?? [], $seite->slug);

                if ($neu !== ($block->data ?? [])) {
                    $block->update(['data' => $neu]);
                }
            }
        }

        foreach (TeamMember::all() as $person) {
            $person->update([
                'kurzprofil' => $person->kurzprofil ? Textpflege::text($person->kurzprofil) : $person->kurzprofil,
                'profil' => $person->profil ? Textpflege::text($person->profil) : $person->profil,
            ]);
        }
    }

    public function down(): void
    {
        // Bewusst nichts: Tippfehler stellt niemand wieder her.
    }
};
