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

                // Öffentlich auf der Veranstaltungsseite (Kevin, 30.10.2026).
                // Nur Teams-Adressen, damit kein falscher Link durchrutscht.
                TextInput::make('teams_link')->label('Teams-Link')
                    ->url()->maxLength(2048)
                    ->rule('regex:#^https://teams\.(microsoft|live)\.com/#')
                    ->validationMessages(['regex' => 'Bitte den Link aus der Teams-Einladung einfügen (beginnt mit https://teams.microsoft.com/).'])
                    ->placeholder('https://teams.microsoft.com/…')
                    ->helperText('Steht öffentlich auf der Veranstaltungsseite. Jede und jeder kann damit teilnehmen.')
                    ->visible(fn ($get) => (bool) $get('online'))
                    ->columnSpanFull(),

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

                // Beim Anlegen schon „jetzt“: Leer hieß Entwurf, und wer das
                // übersah, legte eine Veranstaltung an, die niemand sah
                // (Vorführvideo, 30.10.2026).
                DateTimePicker::make('published_at')->label('Veröffentlichen am')
                    ->seconds(false)->native(false)
                    ->default(fn () => now())
                    ->helperText('Ab dann auf der Website zu sehen. Leer = Entwurf, für Besucher nicht sichtbar.'),
            ]),
        ]);
    }
}
