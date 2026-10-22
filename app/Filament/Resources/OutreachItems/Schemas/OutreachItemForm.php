<?php

namespace App\Filament\Resources\OutreachItems\Schemas;

use App\Models\OutreachItem;
use App\Support\Medien;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OutreachItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make()->columns(2)->schema([
                Select::make('art')->label('Art')->options(OutreachItem::ARTEN)
                    ->default('messe')->required()->native(false)
                    ->helperText('Danach wird der Eintrag auf der Übersicht einsortiert, '
                        .'z. B. unter „Messen“.'),

                DatePicker::make('datum')->label('Datum')->native(false)->displayFormat('d.m.Y')
                    ->helperText('Neueste stehen oben. Ohne Datum (etwa bei Petitionen) am Ende.'),

                TextInput::make('titel')->label('Titel')->required()->columnSpanFull()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set, $get, $context) => $context === 'create'
                        ? $set('slug', Str::slug(trim($state.' '.($get('datum') ? substr($get('datum'), 0, 4) : ''))))
                        : null)
                    ->helperText('Zum Beispiel „Ehrenamtsmesse Bayreuth“.'),

                TextInput::make('slug')->label('Adresse')->required()->unique(ignoreRecord: true)
                    ->rules(['regex:/^[a-z0-9-]+$/'])
                    ->prefix('/oeffentlichkeitsarbeit/')
                    ->helperText('Nur Kleinbuchstaben, Ziffern und Bindestriche. Nach dem '
                        .'Veröffentlichen besser nicht mehr ändern, sonst gehen geteilte Links ins Leere.'),

                TextInput::make('ort')->label('Ort oder Medium')
                    ->helperText('Bei Messen der Ort, bei Presse das Medium, z. B. „stern“. '
                        .'Steht neben dem Datum. Kann leer bleiben.'),
            ]),

            Section::make('Text')->schema([
                Textarea::make('teaser')->label('Kurztext für die Karte')->rows(2)->maxLength(400)
                    ->helperText('Ein, zwei Sätze für die Übersicht. Leer lassen nimmt den Anfang '
                        .'des ausführlichen Texts.'),

                RichEditor::make('beschreibung')->label('Ausführlicher Text')
                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList'])
                    ->helperText('Steht auf der eigenen Seite des Eintrags.'),
            ]),

            Section::make('Fotos')
                ->description('Das erste Foto steht auf der Karte und oben auf der Seite, '
                    .'die übrigen darunter. Reihenfolge per Ziehen ändern.')
                ->schema([
                    Repeater::make('fotos')->label('')
                        ->addActionLabel('Foto hinzufügen')
                        ->reorderable()
                        ->collapsible()
                        ->columns(2)
                        ->defaultItems(0)
                        ->schema([
                            Medien::bild('bild')->label('Foto')->required(),
                            Textarea::make('alt')->label('Bildbeschreibung')->rows(3)
                                ->helperText('Was auf dem Foto zu sehen ist, für Menschen, die es '
                                    .'nicht sehen können. Bitte nur Namen nennen, wenn klar ist, '
                                    .'wer wer ist.'),
                        ]),
                ]),

            Section::make('Link und Sichtbarkeit')->columns(2)->schema([
                TextInput::make('link_url')->label('Link')->url()
                    ->helperText('Zum Artikel, zur Petition, zum Video … Kann leer bleiben.'),

                TextInput::make('link_text')->label('Beschriftung des Links')
                    ->helperText('Zum Beispiel „Zur Petition“. Leer lassen zeigt „Mehr erfahren“.'),

                DateTimePicker::make('published_at')->label('Sichtbar ab')->seconds(false)
                    ->native(false)->default(now())
                    ->helperText('Leer = nicht öffentlich sichtbar.'),
            ]),
        ]);
    }
}
