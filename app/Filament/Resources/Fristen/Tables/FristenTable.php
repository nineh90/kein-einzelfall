<?php

namespace App\Filament\Resources\Fristen\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FristenTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titel')->label('Titel')->searchable()->sortable()->weight('medium')->wrap()
                    ->description(fn ($record) => $record?->frist),

                TextColumn::make('schlagworte')->label('Schlagworte')->badge()->separator(','),

                IconColumn::make('published_at')->label('Sichtbar')->alignCenter()->boolean()
                    ->getStateUsing(fn ($record) => $record?->published_at?->isPast() === true),
            ])
            // Wie auf der Seite: von A bis Z.
            ->defaultSort('titel')
            ->defaultPaginationPageOption(50)
            ->filters([
                TernaryFilter::make('published_at')
                    ->label('Sichtbar')
                    ->nullable()
                    ->trueLabel('Nur sichtbare')
                    ->falseLabel('Nur Entwürfe'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('Noch keine Fristen');
    }
}
