<?php

namespace App\Filament\Resources\TeamMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')->label('#')->sortable()->alignCenter(),
                TextColumn::make('name')->label('Name')->searchable()->sortable()->weight('medium')
                    ->description(fn ($r) => $r->rolle),
                TextColumn::make('bereich')->label('Bereich')->badge()->placeholder('—'),
                IconColumn::make('profil')->label('Ausführlich')->alignCenter()->boolean()
                    ->getStateUsing(fn ($r) => $r->hatProfil())
                    ->tooltip('Hat einen aufklappbaren Text'),
                IconColumn::make('published_at')->label('Sichtbar')->alignCenter()->boolean()
                    ->getStateUsing(fn ($r) => $r->published_at?->isPast() === true),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('Noch niemand angelegt');
    }
}
