<?php

use App\Models\PageBlock;
use Illuminate\Database\Migrations\Migration;

/**
 * Zwei PDFs haben einen neuen hinterlegten Titel bekommen (Schutzkonzept und
 * Teilnahmevereinbarung für Veranstaltungen, Nils' Review) und sind dadurch
 * ein paar hundert Bytes größer. Die Größe steht im Linktext, sie muss zur
 * Datei passen (DokumenteTest). In allen Sprachen.
 */
return new class extends Migration
{
    private const DATEIEN = [
        '/dokumente/2026/10/Schutz-und-Wertekonzept.pdf',
        '/dokumente/2026/07/1.5.6.6.-Tu.V-VA-aktuell-ohne.docx-Google-Docs.pdf',
    ];

    public function up(): void
    {
        foreach (PageBlock::where('typ', 'download_list')->get() as $block) {
            $data = $block->data;
            $geaendert = false;

            foreach ($data['dokumente'] ?? [] as $i => $dokument) {
                $datei = public_path(ltrim($dokument['url'] ?? '', '/'));

                if (in_array($dokument['url'] ?? null, self::DATEIEN, true) && ! empty($dokument['bytes']) && is_file($datei)) {
                    $data['dokumente'][$i]['bytes'] = filesize($datei);
                    $geaendert = true;
                }
            }

            if ($geaendert) {
                $block->update(['data' => $data]);
            }
        }
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
