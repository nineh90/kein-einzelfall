<?php

namespace App\Filament\Verein\Resources\TeamsLinks;

use App\Filament\Verein\Resources\TeamsLinks\Pages\ListTeamsLinks;
use App\Models\Group;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Teams-Links der Selbsthilfegruppen im Vereinsbereich (Kevin, 30.10.2026).
 *
 * Taddi legt die Serientermine in Teams selbst an, also trägt sie auch die
 * Links selbst ein. Nur diese zwei Felder, sonst nichts von den Gruppen:
 * Name, Texte und Bilder bleiben in der Verwaltung. Liegt bewusst nicht unter
 * App\Filament\Resources, damit die Verwaltung sie nicht doppelt findet.
 */
class TeamsLinkResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $slug = 'teams-links';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $navigationLabel = 'Teams-Links';

    protected static ?string $modelLabel = 'Teams-Link';

    protected static ?string $pluralModelLabel = 'Teams-Links';

    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('typ', 'selbsthilfe');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('zugangslink')->label('Teams-Link')
                ->url()->maxLength(2048)
                ->rule('regex:#^https://teams\.(microsoft|live)\.com/#')
                ->validationMessages(['regex' => 'Bitte den Link aus der Teams-Einladung einfügen (beginnt mit https://teams.microsoft.com/).'])
                ->password()->revealable()
                ->placeholder('https://teams.microsoft.com/…')
                ->helperText('Geht per E-Mail an alle, deren Anmeldung ihr freigebt. Steht nirgends auf der Website.'),

            Toggle::make('link_automatisch')->label('Link sofort nach der Anmeldung verschicken')
                ->helperText('Aus: Ihr gebt jede Anmeldung unter „Anmeldungen“ frei, dann geht der Link raus. '
                    .'An: Wer sich anmeldet, bekommt den Link sofort, ohne Prüfung.'),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Gruppe')
                    ->state(fn (?Group $record) => $record?->getRawOriginal('name')),
                TextColumn::make('rhythmus')->label('Wann')
                    ->state(fn (?Group $record) => trim(($record?->getRawOriginal('rhythmus') ?? '').' '.($record?->getRawOriginal('uhrzeit') ?? '')))
                    ->wrap(),
                IconColumn::make('link_eingetragen')->label('Link eingetragen')
                    ->state(fn (?Group $record) => filled($record?->zugangslink))
                    ->boolean(),
                IconColumn::make('link_automatisch')->label('Sofort verschicken')->boolean(),
            ])
            ->defaultSort('position')
            ->paginated(false)
            ->recordActions([
                EditAction::make()->label('Link ändern')->modalHeading(fn (Group $record) => 'Teams-Link: '.$record->getRawOriginal('name')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeamsLinks::route('/'),
        ];
    }
}
