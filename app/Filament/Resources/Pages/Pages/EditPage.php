<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Die Startseite lässt sich nicht löschen. Sie ist die Adresse der
            // Website; ohne ihren Datensatz liefert „/“ ein 404, und
            // wiederherstellen liesse sie sich im Panel nicht. Andere Seiten
            // darf die Redaktion löschen — für die gibt es Weiterleitungen.
            DeleteAction::make()
                ->hidden(fn ($record) => $record->istStartseite()),
        ];
    }
}
