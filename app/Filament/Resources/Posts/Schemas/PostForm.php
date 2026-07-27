<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make()->columns(2)->schema([
                TextInput::make('titel')
                    ->label('Titel')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, $set, $context) {
                        // Nur bei neuen Beiträgen: Ein nachträglich geänderter
                        // Slug macht geteilte Links ungültig.
                        if ($context === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),

                TextInput::make('slug')
                    ->label('Adresse')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->rules(['regex:/^[a-z0-9-]+$/'])
                    ->prefix(url('/aktuelles').'/'),

                Select::make('category_id')
                    ->label('Kategorie')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->createOptionForm([
                        TextInput::make('name')->label('Name')->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')->label('Adresse')->required()->unique('categories', 'slug'),
                    ]),

                DateTimePicker::make('published_at')
                    ->label('Veröffentlichen am')
                    ->seconds(false)
                    ->helperText('Leer = Entwurf. Ein Datum in der Zukunft schaltet den Beitrag automatisch frei.'),
            ]),

            Section::make('Inhalt')->schema([
                Textarea::make('teaser')
                    ->label('Anrisstext')
                    ->rows(3)
                    ->maxLength(400)
                    ->helperText('Erscheint in der Übersicht und in Suchergebnissen.'),

                RichEditor::make('inhalt')
                    ->label('Text')
                    ->required()
                    // Bewusst ohne Überschrift 1: Die h1 der Seite ist der Titel.
                    // Zwei h1 auf einer Seite verwirren Screenreader.
                    ->toolbarButtons([
                        'bold', 'italic', 'link', 'bulletList', 'orderedList',
                        'h2', 'h3', 'blockquote', 'undo', 'redo',
                    ]),
            ]),

            Section::make('Bild')->columns(2)->collapsed()->schema([
                TextInput::make('bild_pfad')->label('Bildpfad'),
                TextInput::make('bild_alt')
                    ->label('Bildbeschreibung')
                    ->helperText('Was auf dem Bild zu sehen ist — wird Menschen vorgelesen, '
                        .'die das Bild nicht sehen können. Bei rein schmückenden Bildern leer lassen.'),
            ]),

            Section::make('Suchmaschinen')->collapsed()->schema([
                TextInput::make('meta_title')->label('Titel für Suchergebnisse')->maxLength(255),
                Textarea::make('meta_description')->label('Beschreibung')->rows(2)->maxLength(320),
            ]),
        ]);
    }
}
