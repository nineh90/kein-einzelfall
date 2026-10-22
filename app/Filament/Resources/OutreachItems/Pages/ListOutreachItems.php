<?php

namespace App\Filament\Resources\OutreachItems\Pages;

use App\Filament\Resources\OutreachItems\OutreachItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOutreachItems extends ListRecords
{
    protected static string $resource = OutreachItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
