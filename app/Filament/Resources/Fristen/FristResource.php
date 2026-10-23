<?php

namespace App\Filament\Resources\Fristen;

use App\Filament\Resources\Fristen\Pages\CreateFrist;
use App\Filament\Resources\Fristen\Pages\EditFrist;
use App\Filament\Resources\Fristen\Pages\ListFristen;
use App\Filament\Resources\Fristen\Schemas\FristForm;
use App\Filament\Resources\Fristen\Tables\FristenTable;
use App\Models\Frist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/** Die Fristenübersicht unter /fristen (KEV-152), siehe App\Models\Frist. */
class FristResource extends Resource
{
    protected static ?string $model = Frist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Fristen';

    protected static ?string $modelLabel = 'Frist';

    protected static ?string $pluralModelLabel = 'Fristen';

    // Neben dem Glossar: beides Nachschlagewerke unter „Wissen“.
    protected static ?int $navigationSort = 8;

    protected static ?string $slug = 'fristen';

    protected static ?string $recordTitleAttribute = 'titel';

    public static function form(Schema $schema): Schema
    {
        return FristForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FristenTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFristen::route('/'),
            'create' => CreateFrist::route('/create'),
            'edit' => EditFrist::route('/{record}/edit'),
        ];
    }
}
