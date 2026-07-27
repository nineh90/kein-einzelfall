<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\PageBlock;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Seite')
                ->columns(2)
                ->schema([
                    TextInput::make('titel')
                        ->label('Titel')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $set, $context) {
                            // Slug nur bei neuen Seiten automatisch setzen.
                            // Bei bestehenden Seiten wäre eine Änderung ein SEO-Bruch:
                            // die alte Adresse ist bei Google indexiert.
                            if ($context === 'create') {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    TextInput::make('slug')
                        ->label('Adresse (Slug)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->rules(['regex:/^[a-z0-9-]+$/'])
                        ->prefix(url('/').'/')
                        ->helperText('Nur Kleinbuchstaben, Ziffern und Bindestriche. '
                            .'Bei bestehenden Seiten möglichst nicht ändern — die Adresse '
                            .'ist bei Suchmaschinen bekannt. Falls doch: Weiterleitung anlegen.'),

                    \Filament\Forms\Components\DateTimePicker::make('published_at')
                        ->label('Veröffentlicht am')
                        ->helperText('Leer lassen = Entwurf, für Besucher nicht sichtbar.')
                        ->seconds(false),

                    Toggle::make('noindex')
                        ->label('Von Suchmaschinen ausschließen')
                        ->helperText('Nur setzen, wenn die Seite bewusst nicht gefunden werden soll.'),
                ]),

            Section::make('Suchmaschinen')
                ->description('Was bei Google in den Trefferlisten steht. Leer lassen übernimmt den Titel.')
                ->collapsed()
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Titel für Suchergebnisse')
                        ->maxLength(255)
                        ->helperText('Etwa 60 Zeichen, sonst schneidet Google ab.'),

                    Textarea::make('meta_description')
                        ->label('Beschreibung für Suchergebnisse')
                        ->rows(3)
                        ->maxLength(320)
                        ->helperText('Etwa 150–160 Zeichen. Der Text, der unter dem Titel erscheint.'),
                ]),

            Section::make('Inhalt')
                ->description('Die Seite besteht aus Bausteinen. Reihenfolge lässt sich per Ziehen ändern.')
                ->schema([
                    Repeater::make('blocks')
                        ->label('')
                        ->relationship()
                        ->orderColumn('position')
                        ->reorderable()
                        ->collapsible()
                        ->cloneable()
                        ->addActionLabel('Baustein hinzufügen')
                        // Ohne den Titel in der Kopfzeile ist eine Seite mit 13
                        // Bausteinen im zugeklappten Zustand nicht navigierbar.
                        ->itemLabel(fn (array $state): ?string => $state['data']['titel']
                            ?? (PageBlock::TYPEN[$state['typ'] ?? ''] ?? 'Baustein'))
                        ->schema([
                            Select::make('typ')
                                ->label('Art des Bausteins')
                                ->options(PageBlock::TYPEN)
                                ->default('text')
                                ->required()
                                ->live()
                                ->native(false),

                            TextInput::make('data.titel')
                                ->label('Überschrift')
                                ->helperText('Erscheint als Zwischenüberschrift und im Inhaltsverzeichnis.'),

                            Repeater::make('data.absaetze')
                                ->label('Absätze')
                                ->addActionLabel('Absatz hinzufügen')
                                ->visible(fn ($get) => $get('typ') === 'text')
                                ->simple(
                                    Textarea::make('absatz')->label('')->rows(4)->required()
                                ),

                            Repeater::make('data.dokumente')
                                ->label('Dokumente')
                                ->addActionLabel('Dokument hinzufügen')
                                ->visible(fn ($get) => $get('typ') === 'download_list')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('titel')
                                        ->label('Bezeichnung')
                                        ->required()
                                        ->columnSpanFull()
                                        ->helperText('Was die Besucherin liest — nicht der Dateiname. '
                                            .'Screenreader lesen genau diesen Text vor.'),
                                    TextInput::make('url')->label('Adresse der Datei')->required(),
                                    TextInput::make('bytes')->label('Größe in Bytes')->numeric()
                                        ->helperText('Für den Hinweis „PDF, 180 KB“.'),
                                ]),
                        ]),
                ]),
        ]);
    }
}
