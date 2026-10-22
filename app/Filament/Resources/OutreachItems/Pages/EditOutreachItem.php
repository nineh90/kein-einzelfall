<?php

namespace App\Filament\Resources\OutreachItems\Pages;

use App\Filament\Resources\OutreachItems\OutreachItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOutreachItem extends EditRecord
{
    protected static string $resource = OutreachItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
