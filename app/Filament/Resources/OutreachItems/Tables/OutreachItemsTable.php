<?php

namespace App\Filament\Resources\OutreachItems\Tables;

use App\Models\OutreachItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OutreachItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titel')->label('Titel')->searchable()->sortable()
                    ->weight('medium')->wrap()
                    ->description(fn ($record) => $record?->ort),

                TextColumn::make('art')->label('Art')->badge()
                    ->formatStateUsing(fn ($state) => OutreachItem::ARTEN[$state] ?? $state),

                TextColumn::make('datum')->label('Datum')->date('d.m.Y')->sortable()->placeholder('—'),

                IconColumn::make('published_at')->label('Sichtbar')->alignCenter()->boolean()
                    ->getStateUsing(fn ($record) => $record?->published_at?->isPast() === true),
            ])
            ->defaultSort('datum', 'desc')
            ->filters([
                SelectFilter::make('art')->label('Art')->options(OutreachItem::ARTEN),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('Noch keine Einträge');
    }
}
