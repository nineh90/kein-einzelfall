<?php

namespace App\Filament\Resources\Languages\Tables;

use App\Models\Language;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LanguagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Sprache')->searchable()
                    ->description(fn (Language $record) => $record->label_deutsch),

                TextColumn::make('code')->label('Adresse')->badge()
                    ->formatStateUsing(fn (Language $record) => $record->istStandard()
                        ? 'ohne Präfix'
                        : '/'.$record->code),

                IconColumn::make('aktiv')->label('Sichtbar')->boolean(),

                IconColumn::make('ist_standard')->label('Standard')->boolean(),

                TextColumn::make('richtung')->label('Richtung')
                    ->formatStateUsing(fn ($state) => $state === 'rtl' ? 'rechts nach links' : 'links nach rechts')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('position')->label('Reihenfolge')->sortable()->alignCenter(),
            ])
            ->defaultSort('position')
            ->recordActions([
                EditAction::make(),
                // Die Standardsprache zu löschen würde jede Seite ihrer Sprache
                // berauben. Der Knopf verschwindet, statt hinterher zu meckern.
                DeleteAction::make()->hidden(fn (Language $record) => $record->ist_standard),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
