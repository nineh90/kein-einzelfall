<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make()->columns(2)->schema([
                TextInput::make('titel')->label('Titel')->required()->maxLength(255)
                    ->columnSpanFull()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create'
                        ? $set('slug', Str::slug($state))
                        : null),

                TextInput::make('slug')->label('Adresse')->required()
                    ->unique(ignoreRecord: true)->rules(['regex:/^[a-z0-9-]+$/'])
                    ->prefix(url('/veranstaltungen').'/'),

                TextInput::make('art')->label('Art')
                    ->datalist(['Selbsthilfegruppe', 'Arbeitsgruppe', 'Vortrag', 'Workshop', 'Austausch'])
                    ->helperText('Frei wählbar — die Vorschläge sind nur eine Hilfe.'),
            ]),

            Section::make('Wann')->columns(2)->schema([
                Toggle::make('ganztaegig')->label('Ganztägig')->live()
                    ->helperText('Dann wird keine Uhrzeit angezeigt.'),

                DateTimePicker::make('beginnt_am')->label('Beginn')->required()->seconds(false)
                    ->native(false),

                DateTimePicker::make('endet_am')->label('Ende')->seconds(false)->native(false)
                    ->after('beginnt_am')
                    ->helperText('Leer lassen, wenn kein Ende feststeht.'),
            ]),

            Section::make('Wo')->columns(2)->schema([
                Toggle::make('online')->label('Findet online statt')->live(),

                TextInput::make('ort')->label('Ort')
                    ->visible(fn ($get) => ! $get('online'))
                    ->helperText('z.B. „Vereinsräume Hamburg“'),

                TextInput::make('adresse')->label('Adresse')
                    ->visible(fn ($get) => ! $get('online'))
                    ->columnSpanFull(),
            ]),

            Section::make('Beschreibung')->schema([
                Textarea::make('teaser')->label('Kurzfassung')->rows(2)->maxLength(400)
                    ->helperText('Erscheint in der Terminübersicht und im Kalender-Export.'),

                RichEditor::make('beschreibung')->label('Ausführlich')
                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'h2', 'undo', 'redo']),
            ]),

            Section::make('Anmeldung und Sichtbarkeit')->columns(2)->schema([
                TextInput::make('anmeldung_url')->label('Link zur Anmeldung')->url()
                    // Bewusst nur ein Link: Anmeldungen zu Selbsthilfegruppen
                    // sind Art.-9-Daten und gehören nicht nebenbei in den Kalender.
                    ->helperText('Anmeldungen werden nicht auf der Website erfasst — '
                        .'dafür braucht es ein eigenes Datenschutzkonzept.'),

                TextInput::make('anmeldung_hinweis')->label('Hinweis zur Anmeldung')
                    ->helperText('z.B. „Bitte bis eine Woche vorher melden.“'),

                DateTimePicker::make('published_at')->label('Veröffentlichen am')
                    ->seconds(false)->native(false)
                    ->helperText('Leer = Entwurf, für Besucher nicht sichtbar.'),
            ]),
        ]);
    }
}
