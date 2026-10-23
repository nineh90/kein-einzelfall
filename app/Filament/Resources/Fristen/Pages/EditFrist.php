<?php

namespace App\Filament\Resources\Fristen\Pages;

use App\Filament\Resources\Fristen\FristResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFrist extends EditRecord
{
    protected static string $resource = FristResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
