<?php

namespace App\Filament\Resources\Inquiries\Tables;

use App\Models\Inquiry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Eingegangen')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->description(fn ($record) => $record?->created_at?->diffForHumans()),

                // Betreff und Nachricht sind verschlüsselt: nicht sortierbar und
                // nicht durchsuchbar. In der Liste nur angerissen — der volle
                // Inhalt gehört auf die Detailseite, nicht in eine Übersicht,
                // die jemand offen auf dem Bildschirm liegen lässt.
                TextColumn::make('betreff')
                    ->label('Betreff')
                    ->limit(60)
                    ->wrap(),

                IconColumn::make('email')
                    ->label('Antwort möglich')
                    ->alignCenter()
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record !== null && ! $record->istAnonym())
                    ->trueIcon('heroicon-o-envelope')
                    ->falseIcon('heroicon-o-no-symbol')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record?->istAnonym()
                        ? 'Ohne Absender eingegangen'
                        : 'Absender vorhanden'),

                TextColumn::make('status')
                    ->label('Stand')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Inquiry::STATUS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'offen' => 'warning',
                        'in_bearbeitung' => 'info',
                        'erledigt' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('herkunft')
                    ->label('Über')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Stand')
                    ->options(Inquiry::STATUS),
            ])
            ->recordActions([EditAction::make()->label('Öffnen')])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('Keine Anfragen')
            ->emptyStateDescription('Über das Kontaktformular ist bisher nichts eingegangen.');
    }
}
