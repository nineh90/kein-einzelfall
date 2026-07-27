<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titel')->label('Titel')->searchable()->sortable()
                    ->weight('medium')->wrap()
                    ->description(fn ($record) => '/aktuelles/'.$record->slug),

                TextColumn::make('category.name')->label('Kategorie')->badge()->sortable(),

                IconColumn::make('published_at')->label('Sichtbar')->alignCenter()->boolean()
                    ->getStateUsing(fn ($record) => $record->published_at?->isPast() === true)
                    ->tooltip(fn ($record) => match (true) {
                        $record->published_at === null => 'Entwurf',
                        $record->published_at->isFuture() => 'Geplant für '.$record->published_at->format('d.m.Y H:i'),
                        default => 'Veröffentlicht',
                    }),

                TextColumn::make('published_at')->label('Datum')
                    ->dateTime('d.m.Y')->sortable()->placeholder('—'),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('category')->label('Kategorie')
                    ->relationship('category', 'name')->preload(),
            ])
            ->recordActions([
                Action::make('ansehen')
                    ->label('Ansehen')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => url('/aktuelles/'.$record->slug))
                    ->openUrlInNewTab()
                    // Entwürfe sind öffentlich nicht erreichbar
                    ->visible(fn ($record) => $record->published_at?->isPast() === true),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('Noch keine Beiträge')
            ->emptyStateDescription('Der Blog wird neu aufgebaut — die Altseite hatte keine Beiträge.');
    }
}
