<?php

namespace App\Filament\Resources\Pages\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titel')
                    ->label('Titel')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => '/'.$record->slug),

                TextColumn::make('blocks_count')
                    ->label('Bausteine')
                    ->counts('blocks')
                    ->alignCenter(),

                IconColumn::make('published_at')
                    ->label('Sichtbar')
                    ->alignCenter()
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->published_at !== null
                        && $record->published_at->isPast()),

                IconColumn::make('noindex')
                    ->label('Ausgeschlossen')
                    ->alignCenter()
                    ->boolean()
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray')
                    ->trueIcon('heroicon-o-eye-slash')
                    ->trueColor('warning')
                    ->tooltip('Von Suchmaschinen ausgeschlossen'),

                TextColumn::make('updated_at')
                    ->label('Zuletzt geändert')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('titel')
            // Die Website hat gut zwei Dutzend Seiten. Blättern wäre hier nur
            // ein zusätzlicher Klick ohne Nutzen.
            ->defaultPaginationPageOption(50)
            ->filters([
                TernaryFilter::make('published_at')
                    ->label('Sichtbarkeit')
                    ->placeholder('Alle')
                    ->trueLabel('Nur veröffentlichte')
                    ->falseLabel('Nur Entwürfe')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('published_at'),
                        false: fn ($query) => $query->whereNull('published_at'),
                    ),
            ])
            ->recordActions([
                // Direkt zur echten Seite springen — beim Pflegen will man sehen,
                // was man tut.
                Action::make('ansehen')
                    ->label('Ansehen')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => url('/'.$record->slug))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
