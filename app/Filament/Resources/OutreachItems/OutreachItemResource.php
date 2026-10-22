<?php

namespace App\Filament\Resources\OutreachItems;

use App\Filament\Resources\OutreachItems\Pages\CreateOutreachItem;
use App\Filament\Resources\OutreachItems\Pages\EditOutreachItem;
use App\Filament\Resources\OutreachItems\Pages\ListOutreachItems;
use App\Filament\Resources\OutreachItems\Schemas\OutreachItemForm;
use App\Filament\Resources\OutreachItems\Tables\OutreachItemsTable;
use App\Models\OutreachItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/** Messen, Petitionen, Presse (KEV-134), siehe App\Models\OutreachItem. */
class OutreachItemResource extends Resource
{
    protected static ?string $model = OutreachItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $navigationLabel = 'Öffentlichkeitsarbeit';

    protected static ?string $modelLabel = 'Eintrag der Öffentlichkeitsarbeit';

    protected static ?string $pluralModelLabel = 'Öffentlichkeitsarbeit';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'titel';

    public static function form(Schema $schema): Schema
    {
        return OutreachItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OutreachItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOutreachItems::route('/'),
            'create' => CreateOutreachItem::route('/create'),
            'edit' => EditOutreachItem::route('/{record}/edit'),
        ];
    }
}
