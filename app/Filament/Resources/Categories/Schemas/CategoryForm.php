<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Name')->required()->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create'
                    ? $set('slug', Str::slug($state))
                    : null),

            TextInput::make('slug')->label('Adresse')->required()
                ->unique(ignoreRecord: true)->rules(['regex:/^[a-z0-9-]+$/']),

            Textarea::make('beschreibung')->label('Beschreibung')->rows(3)
                ->helperText('Erscheint über der Beitragsliste dieser Kategorie.'),
        ]);
    }
}
