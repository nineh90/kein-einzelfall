<?php

namespace App\Filament\Resources\Fristen\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class FristForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Auf der Karte')
                ->description('Das steht auf der zugeklappten Karte in der Fristenübersicht.')
                ->columns(2)
                ->schema([
                    TextInput::make('titel')
                        ->label('Titel')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set, $context) => $context === 'create'
                            ? $set('slug', Str::slug($state))
                            : null)
                        ->helperText('„Widerspruch“, „Klage vor dem Sozialgericht“. Danach wird von A bis Z sortiert.'),

                    TextInput::make('slug')
                        ->label('Sprungziel')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->rules(['regex:/^[a-z0-9-]+$/'])
                        ->helperText('Damit lässt sich die Frist einzeln verlinken: /fristen#widerspruch. '
                            .'Möglichst nicht mehr ändern, verschickte Links zeigen sonst ins Leere.'),

                    Textarea::make('frist')
                        ->label('Frist in Kürze')
                        ->rows(2)
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->helperText('Zum Beispiel „Frist: grundsätzlich 1 Monat“ oder „Keine allgemeine feste '
                            .'Bearbeitungsfrist“. Zwei Zeilen gehen auch. Kann leer bleiben.'),

                    TagsInput::make('schlagworte')
                        ->label('Schlagworte')
                        ->suggestions(['Behörde', 'Gericht', 'Widerspruch', 'Antrag', 'Klage', 'Bescheid'])
                        ->columnSpanFull()
                        ->helperText('Kleine Etiketten auf der Karte, die Suche findet sie mit. Eingeben und '
                            .'Enter drücken.'),
                ]),

            Section::make('Ausführlich')
                ->description('Steht in der Karte, wenn man sie aufklappt.')
                ->schema([
                    RichEditor::make('inhalt')
                        ->label('Text')
                        ->toolbarButtons([['bold', 'italic', 'link'], ['h3'], ['bulletList', 'orderedList']])
                        ->helperText('Zwischenüberschriften (H3) für längere Fristen wie „Fristverlängerung“. '
                            .'Paragrafen bitte so, wie sie im Gesetz heißen: § 84 SGG.'),
                ]),

            Section::make()
                ->schema([
                    DateTimePicker::make('published_at')
                        ->label('Veröffentlicht am')
                        ->default(now())
                        ->seconds(false)
                        ->helperText('Leer lassen = Entwurf, in der Übersicht nicht sichtbar.'),
                ]),
        ]);
    }
}
