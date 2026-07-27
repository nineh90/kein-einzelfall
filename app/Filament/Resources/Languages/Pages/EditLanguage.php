<?php

namespace App\Filament\Resources\Languages\Pages;

use App\Filament\Resources\Languages\LanguageResource;
use App\Models\Language;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLanguage extends EditRecord
{
    protected static string $resource = LanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Ohne Standardsprache hätte keine Seite mehr eine Sprache.
            DeleteAction::make()->hidden(fn (Language $record) => $record->ist_standard),
        ];
    }
}
