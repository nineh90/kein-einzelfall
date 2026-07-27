<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make()->columns(2)->schema([
                TextInput::make('name')->label('Name')->required()->maxLength(255),

                TextInput::make('rolle')->label('Rolle im Verein')
                    ->datalist(['1. Vorsitzende', '2. Vorsitzende', 'Kassenwart', 'Landesstelle'])
                    ->helperText('Erscheint als Überzeile über dem Namen.'),

                TextInput::make('untertitel')->label('Kurzangaben')->columnSpanFull()
                    ->helperText('z.B. „Gründungsmitglied, Jahrgang 1969, geb. in Hamburg“. '
                        .'Nur das aufnehmen, was die Person selbst veröffentlicht sehen möchte.'),

                TextInput::make('bereich')->label('Bereich')
                    ->datalist(['Vorstand', 'Landesstellen', 'Team'])
                    ->helperText('Zum Gruppieren auf der Seite. Kann leer bleiben.'),

                TextInput::make('position')->label('Reihenfolge')->numeric()->default(0)
                    ->helperText('Kleinere Zahl steht weiter oben.'),
            ]),

            Section::make('Vorstellung')->schema([
                Textarea::make('kurzprofil')->label('Kurzfassung')->rows(3)->maxLength(400)
                    ->helperText('Zwei bis drei Sätze, immer sichtbar.'),

                RichEditor::make('profil')->label('Ausführlicher Text')
                    // Ohne Überschriften: Der Text steht in einem aufklappbaren
                    // Bereich innerhalb einer Karte — eigene Überschriften würden
                    // die Gliederung der Seite durcheinanderbringen.
                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'undo', 'redo'])
                    ->helperText('Wird über „Mehr lesen“ aufgeklappt. Kann lang sein.'),
            ]),

            Section::make('Foto und Sichtbarkeit')->columns(2)->schema([
                TextInput::make('foto_pfad')->label('Foto (Pfad oder Adresse)')
                    ->helperText('Ohne Foto erscheinen die Initialen.'),

                TextInput::make('foto_alt')->label('Bildbeschreibung')
                    ->helperText('Was auf dem Bild zu sehen ist — für Menschen, die es nicht sehen können.'),

                DateTimePicker::make('published_at')->label('Sichtbar ab')->seconds(false)
                    ->native(false)->helperText('Leer = nicht öffentlich sichtbar.'),
            ]),
        ]);
    }
}
