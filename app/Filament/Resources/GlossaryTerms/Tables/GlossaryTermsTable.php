<?php

namespace App\Filament\Resources\GlossaryTerms\Tables;

use App\Models\Language;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GlossaryTermsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kuerzel')->label('Abkürzung')->searchable()->placeholder('—'),

                TextColumn::make('begriff')->label('Begriff')->searchable()->sortable()
                    ->weight('medium')->wrap()
                    // Die ersten Worte der Erklärung als Vorschau: Ohne sie
                    // sähe die Liste bei „SGB XIV“ und „SGB IX“ gleich aus.
                    ->description(fn ($r) => \Illuminate\Support\Str::limit($r?->erklaerung, 90)),

                TextColumn::make('locale')->label('Sprache')->badge()
                    ->formatStateUsing(fn ($s) => Language::finden($s)?->label_deutsch ?? $s),

                IconColumn::make('published_at')->label('Sichtbar')->alignCenter()->boolean()
                    ->getStateUsing(fn ($r) => $r?->published_at?->isPast() === true),
            ])
            // Nach dem, was auf der Seite vorn steht — sonst sucht die
            // Redaktion einen Eintrag in einer anderen Reihenfolge, als sie ihn
            // im Glossar sieht.
            ->defaultSort('begriff')
            ->filters([
                SelectFilter::make('locale')
                    ->label('Sprache')
                    ->options(fn () => Language::alle()->pluck('label_deutsch', 'code')->all()),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('Noch keine Begriffe im Glossar');
    }
}
