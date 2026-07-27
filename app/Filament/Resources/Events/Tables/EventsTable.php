<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('beginnt_am')->label('Beginn')->sortable()
                    ->formatStateUsing(fn ($record) => $record?->zeitraum())
                    ->description(fn ($record) => $record?->beginnt_am?->isPast()
                        ? 'vorbei' : $record?->beginnt_am?->diffForHumans()),

                TextColumn::make('titel')->label('Titel')->searchable()->wrap()
                    ->description(fn ($record) => $record?->online ? 'Online' : $record?->ort),

                TextColumn::make('art')->label('Art')->badge()->placeholder('—'),

                IconColumn::make('published_at')->label('Sichtbar')->alignCenter()->boolean()
                    ->getStateUsing(fn ($record) => $record?->published_at?->isPast() === true),
            ])
            ->defaultSort('beginnt_am', 'desc')
            ->filters([
                Filter::make('kommend')->label('Nur kommende')
                    ->query(fn ($query) => $query->kommend())
                    ->default(),
            ])
            ->recordActions([
                Action::make('ansehen')->label('Ansehen')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => url('/veranstaltungen/'.$record?->slug))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record?->published_at?->isPast() === true),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('Keine Termine')
            ->emptyStateDescription('Lege den ersten Termin an — er erscheint dann auf /veranstaltungen.');
    }
}
