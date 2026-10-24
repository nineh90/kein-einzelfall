<?php

namespace App\Filament\Resources\GroupRegistrations;

use App\Filament\Resources\GroupRegistrations\Pages\ListGroupRegistrations;
use App\Filament\Resources\GroupRegistrations\Tables\GroupRegistrationsTable;
use App\Models\GroupRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Anmeldungen zu Selbsthilfegruppen (KEV-226): freigeben, ablehnen, löschen.
 *
 * Nur eine Liste, keine Bearbeiten-Seite: Es gibt nichts zu bearbeiten, nur
 * zu entscheiden. Anmeldungen entstehen ausschließlich über das Formular auf
 * der Gruppenseite.
 */
class GroupRegistrationResource extends Resource
{
    protected static ?string $model = GroupRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $navigationLabel = 'Anmeldungen';

    protected static ?string $modelLabel = 'Anmeldung';

    protected static ?string $pluralModelLabel = 'Anmeldungen';

    protected static ?int $navigationSort = 1;

    /** Wartende Anmeldungen als Zähler, wie bei den Anfragen. */
    public static function getNavigationBadge(): ?string
    {
        $offen = static::getModel()::offen()->count();

        return $offen > 0 ? (string) $offen : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return GroupRegistrationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGroupRegistrations::route('/'),
        ];
    }
}
