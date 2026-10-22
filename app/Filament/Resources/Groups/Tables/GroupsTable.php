<?php

namespace App\Filament\Resources\Groups\Tables;

use App\Models\Group;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kuerzel')->label('Kürzel')->placeholder('—'),

                TextColumn::make('name')->label('Name')->searchable()->sortable()
                    ->weight('medium')->wrap()
                    ->description(fn ($record) => $record?->wannUndWo() ?: null),

                TextColumn::make('typ')->label('Art')->badge()
                    ->formatStateUsing(fn ($state) => Group::TYPEN[$state] ?? $state),

                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($state) => Group::STATUS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'offen' => 'success',
                        'geplant' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('published_at')->label('Sichtbar')->alignCenter()->boolean()
                    ->getStateUsing(fn ($record) => $record?->published_at?->isPast() === true),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->filters([
                SelectFilter::make('typ')->label('Art')->options(Group::TYPEN),
                SelectFilter::make('status')->label('Status')->options(Group::STATUS),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('Noch keine Gruppen');
    }
}
