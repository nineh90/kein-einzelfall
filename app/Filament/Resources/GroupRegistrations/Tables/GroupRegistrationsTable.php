<?php

namespace App\Filament\Resources\GroupRegistrations\Tables;

use App\Models\Group;
use App\Models\GroupRegistration;
use App\Support\GruppenAnmeldung;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GroupRegistrationsTable
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

                TextColumn::make('group.name')->label('Gruppe'),

                // Verschlüsselt: nicht sortier- und nicht durchsuchbar.
                TextColumn::make('name')->label('Name')->placeholder('(nicht angegeben)'),
                TextColumn::make('email')->label('E-Mail-Adresse')->copyable(),

                TextColumn::make('status')
                    ->label('Stand')
                    ->badge()
                    ->formatStateUsing(fn ($state) => GroupRegistration::STATUS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'offen' => 'warning',
                        'freigegeben' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('zugestimmt_at')
                    ->label('Zugestimmt (Vereinbarung, Regeln, Daten)')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Stand')->options(GroupRegistration::STATUS),
                SelectFilter::make('group_id')->label('Gruppe')
                    ->options(fn () => Group::vomTyp('selbsthilfe')->orderBy('position')->pluck('name', 'id')->all()),
            ])
            ->recordActions([
                Action::make('freigeben')
                    ->label(fn ($record) => $record->status === 'freigegeben' ? 'Link erneut senden' : 'Freigeben und Link senden')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Zugangslink verschicken?')
                    ->modalDescription(fn ($record) => 'Der Zugangslink zu „'.$record->group?->getRawOriginal('name')
                        .'“ geht per E-Mail an '.$record->email.'.')
                    ->disabled(fn ($record) => blank($record->group?->zugangslink))
                    ->tooltip(fn ($record) => blank($record->group?->zugangslink)
                        ? 'Bei der Gruppe ist noch kein Zugangslink eingetragen (Gruppen → Teilnahme).'
                        : null)
                    ->action(function (GroupRegistration $record) {
                        if (GruppenAnmeldung::zugangSenden($record)) {
                            Notification::make()->title('Zugangslink verschickt')->success()->send();
                        } else {
                            Notification::make()->title('Nicht verschickt')
                                ->body('Die E-Mail konnte nicht verschickt werden. Die Anmeldung bleibt, wie sie ist.')
                                ->danger()->send();
                        }
                    }),

                // Ablehnen und Löschen im Menü daneben: Als eigene Knöpfe lagen
                // sie auf Laptops (1280 bis 1440 px) rechts außerhalb und waren
                // nur mit seitlichem Scrollen zu finden (Durchlauf 30.10.2026).
                ActionGroup::make([
                    Action::make('ablehnen')
                        ->label('Ablehnen')
                        ->icon('heroicon-o-x-mark')
                        ->color('gray')
                        ->visible(fn ($record) => $record->status === 'offen')
                        ->requiresConfirmation()
                        ->modalHeading('Anmeldung ablehnen?')
                        ->modalDescription('Es geht keine E-Mail raus. Wer sich angemeldet hat, erfährt davon nur, wenn ihr selbst schreibt. '
                            .'Die Anmeldung wird nach Frist automatisch gelöscht.')
                        ->action(fn (GroupRegistration $record) => $record->update(['status' => 'abgelehnt'])),

                    DeleteAction::make()->label('Löschen'),
                ])->label('Mehr')->tooltip('Ablehnen oder löschen'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('Keine Anmeldungen')
            ->emptyStateDescription('Über die Seiten der Selbsthilfegruppen hat sich bisher niemand angemeldet.');
    }
}
