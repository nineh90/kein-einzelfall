<?php

namespace App\Filament\Resources\Fristen\Pages;

use App\Filament\Resources\Fristen\FristResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFristen extends ListRecords
{
    protected static string $resource = FristResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
