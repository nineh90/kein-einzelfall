<?php

namespace App\Filament\Verein\Resources\TeamsLinks\Pages;

use App\Filament\Verein\Resources\TeamsLinks\TeamsLinkResource;
use Filament\Resources\Pages\ListRecords;

class ListTeamsLinks extends ListRecords
{
    protected static string $resource = TeamsLinkResource::class;

    protected static ?string $title = 'Teams-Links der Selbsthilfegruppen';

    protected ?string $subheading = 'Den Link aus der Teams-Einladung der Gruppe einfügen. Er geht nach der Freigabe per E-Mail an die Person.';
}
