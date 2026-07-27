<?php

namespace App\Filament\Resources\Redirects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RedirectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('von')->label('Alte Adresse')->searchable()->sortable()
                    ->formatStateUsing(fn ($state) => '/'.$state),

                TextColumn::make('nach')->label('Ziel')->searchable(),

                TextColumn::make('status')->label('Art')->badge()
                    ->color(fn ($state) => $state === 301 ? 'success' : 'warning'),

                // Nach dem Go-Live die wichtigste Spalte: greift die Regel überhaupt?
                // Eine Regel mit 0 Treffern nach Wochen ist entweder überflüssig
                // oder falsch geschrieben.
                TextColumn::make('treffer')->label('Aufrufe')->sortable()->alignCenter(),

                TextColumn::make('zuletzt_genutzt_at')->label('Zuletzt')
                    ->dateTime('d.m.Y H:i')->placeholder('nie')->sortable(),

                TextColumn::make('notiz')->label('Notiz')->toggleable()->wrap(),
            ])
            ->defaultSort('treffer', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
