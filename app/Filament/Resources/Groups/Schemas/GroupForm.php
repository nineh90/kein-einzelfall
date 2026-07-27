<?php

namespace App\Filament\Resources\Groups\Schemas;

use App\Models\Group;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make()->columns(2)->schema([
                TextInput::make('name')->label('Name der Gruppe')->required()->columnSpanFull()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create'
                        ? $set('slug', Str::slug($state))
                        : null),

                TextInput::make('slug')->label('Kurzform')->required()->unique(ignoreRecord: true)
                    ->rules(['regex:/^[a-z0-9-]+$/']),

                Select::make('typ')->label('Art')->options(Group::TYPEN)
                    ->default('selbsthilfe')->required()->native(false),

                TextInput::make('kuerzel')->label('Kürzel')
                    ->helperText('z.B. „AG 01“. Nur bei Arbeitsgruppen üblich.'),

                TextInput::make('position')->label('Reihenfolge')->numeric()->default(0),
            ]),

            Section::make('Beschreibung')->schema([
                Textarea::make('teaser')->label('Kurzbeschreibung')->rows(2)->maxLength(400)
                    ->helperText('Erscheint in der Übersicht.'),

                RichEditor::make('beschreibung')->label('Ausführlich')
                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList']),
            ]),

            Section::make('Termin und Ort')->columns(2)->schema([
                TextInput::make('rhythmus')->label('Wie oft (Anzeigetext)')
                    ->helperText('z.B. „Jeden 4. Mittwoch im Monat“. Dieser Text erscheint auf der Seite.'),

                TextInput::make('uhrzeit')->label('Uhrzeit (Anzeigetext)')->helperText('z.B. „19:00 Uhr“'),

                // Damit die Treffen im Kalender erscheinen, braucht es die Angabe
                // zusätzlich in maschinenlesbarer Form.
                Select::make('wiederholung')->label('Rhythmus für den Kalender')
                    ->options(Group::WIEDERHOLUNGEN)->default('keine')->native(false)->live()
                    ->helperText('Nur mit dieser Angabe erscheinen die Treffen im Veranstaltungskalender.'),

                Select::make('wochentag')->label('Wochentag')
                    ->options(Group::WOCHENTAGE)->native(false)
                    ->visible(fn ($get) => $get('wiederholung') !== 'keine'),

                Select::make('woche_im_monat')->label('Welche Woche im Monat')
                    ->options([1 => 'erste', 2 => 'zweite', 3 => 'dritte', 4 => 'vierte', 5 => 'letzte'])
                    ->native(false)
                    ->visible(fn ($get) => $get('wiederholung') === 'monatlich_nter_wochentag'),

                TimePicker::make('beginn_zeit')->label('Beginn (Uhrzeit)')
                    ->seconds(false)
                    ->visible(fn ($get) => $get('wiederholung') !== 'keine'),

                Toggle::make('online')->label('Findet online statt')->live(),

                TextInput::make('ort')->label('Ort')
                    ->helperText(fn ($get) => $get('online')
                        ? 'z.B. „online via Teams“'
                        : 'z.B. „Vereinsräume Hamburg“'),
            ]),

            Section::make('Teilnahme')->columns(2)->schema([
                Select::make('status')->label('Status')->options(Group::STATUS)
                    ->default('offen')->required()->native(false)
                    ->helperText('Nur offene Gruppen bekommen einen Anfrage-Knopf.'),

                DateTimePicker::make('published_at')->label('Sichtbar ab')->seconds(false)
                    ->native(false)->helperText('Leer = nicht öffentlich sichtbar.'),

                TextInput::make('anmeldung_hinweis')->label('Hinweis zur Teilnahme')->columnSpanFull()
                    // Bewusst nur ein Hinweis: Anmeldungen zu Selbsthilfegruppen sind
                    // Art.-9-Daten und brauchen ein eigenes Konzept.
                    ->helperText('Anmeldungen werden nicht auf der Website erfasst — '
                        .'die Teilnahme an einer Selbsthilfegruppe ist eine besonders '
                        .'schützenswerte Angabe und braucht ein eigenes Datenschutzkonzept.'),
            ]),
        ]);
    }
}
