<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;

/**
 * Schreibwerkstatt „Zwischen den Zeilen“: Taddis letzter Satz „Manches lässt
 * sich nicht sagen, aber vielleicht schreiben.“ als zweite Zeile des
 * Schlusssatzes (KEV-231), auf Deutsch und Englisch. Nur wo noch der
 * bisherige Satz steht.
 */
return new class extends Migration
{
    private const ALT = [
        'de' => 'Das Wichtigste steht nicht immer in den großen Worten – sondern oft zwischen den Zeilen.',
        'en' => 'The most important things are not always found in big words – but often between the lines.',
    ];

    private const NEU = [
        'de' => 'Manches lässt sich nicht sagen, aber vielleicht schreiben.',
        'en' => 'Some things cannot be said, but perhaps they can be written.',
    ];

    public function up(): void
    {
        $gruppe = Group::where('slug', 'schreibwerkstatt')->first();

        if (! $gruppe) {
            return;
        }

        $uebersetzungen = $gruppe->uebersetzungen ?? [];

        if (($uebersetzungen['en']['schlusssatz'] ?? null) === self::ALT['en']) {
            $uebersetzungen['en']['schlusssatz'] = self::ALT['en']."\n".self::NEU['en'];
        }

        $gruppe->forceFill([
            'schlusssatz' => $gruppe->getRawOriginal('schlusssatz') === self::ALT['de']
                ? self::ALT['de']."\n".self::NEU['de']
                : $gruppe->getRawOriginal('schlusssatz'),
            'uebersetzungen' => $uebersetzungen,
        ])->save();
    }

    public function down(): void
    {
        // Bewusst nichts — wie bei den übrigen Inhalts-Migrationen.
    }
};
