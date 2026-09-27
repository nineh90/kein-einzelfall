<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Startseite, „Was wir gemeinsam bewegen“: die Lucide-Zeichen, die sich der Verein
 * gewünscht hat (KEV-53). Zugeordnet über das Ziel der Karte, nicht über
 * den Titel, der je Sprache anders heißt.
 *
 * Nur wo noch das alte Zeichen steht. Hat der Verein im Panel schon ein
 * anderes gewählt, bleibt es.
 */
return new class extends Migration
{
    /** Ziel der Karte => [altes Zeichen, neues Zeichen] */
    private const ZEICHEN = [
        '/selbsthilfegruppen' => ['users', 'user-group'],
        '/arbeitsgruppen' => ['message', 'network'],
        '/anfragen' => ['shield', 'notebook-pen'],
        '/spenden' => ['heart', 'hand-coins'],
    ];

    public function up(): void
    {
        $this->tauschen(0, 1);
    }

    public function down(): void
    {
        $this->tauschen(1, 0);
    }

    private function tauschen(int $von, int $nach): void
    {
        foreach (Page::where('slug', Page::STARTSEITE_SLUG)->get() as $seite) {
            foreach ($seite->blocks()->where('typ', 'quick_access')->get() as $block) {
                $data = $block->data;
                $geaendert = false;

                foreach ($data['karten'] ?? [] as $i => $karte) {
                    $paar = self::ZEICHEN[$karte['url'] ?? ''] ?? null;

                    if ($paar && ($karte['icon'] ?? null) === $paar[$von]) {
                        $data['karten'][$i]['icon'] = $paar[$nach];
                        $geaendert = true;
                    }
                }

                if ($geaendert) {
                    $block->update(['data' => $data]);
                }
            }
        }
    }
};
