<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\WasWartet;
use Filament\Pages\Dashboard;

/**
 * Die erste Seite nach dem Anmelden.
 *
 * Vorher Filaments Standard: „Dashboard“ mit „Willkommen“ und einem Kasten
 * „filament v4 · Dokumentation · GitHub“. Für den Verein nichtssagend
 * (Durchlauf der Gruppenanmeldung, 30.10.2026). Jetzt steht hier, was auf
 * jemanden wartet, mit einem Knopf dorthin.
 */
class Start extends Dashboard
{
    protected static ?string $title = 'Start';

    protected static ?string $navigationLabel = 'Start';

    public function getWidgets(): array
    {
        return [WasWartet::class];
    }

    public function getColumns(): int|array
    {
        return 1;
    }
}
