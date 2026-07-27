<?php

namespace App\Filament\Resources\Languages\Schemas;

use App\Models\Language;
use App\Rules\KollidiertNichtMitSprachpraefix;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LanguageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Sprache')
                ->columns(2)
                ->schema([

                    TextInput::make('code')
                        ->label('Sprachcode')
                        ->required()
                        ->maxLength(16)
                        ->unique(ignoreRecord: true)
                        // Dasselbe Muster, das auch im Routing gilt — sonst liesse
                        // sich hier eine Sprache anlegen, die keine Adresse bekommt.
                        ->rule('regex:/^'.Language::ADRESS_MUSTER.'$/')
                        ->rules([KollidiertNichtMitSprachpraefix::fuerSprachcode()])
                        ->disabled(fn (?Language $record) => $record?->ist_standard)
                        ->helperText('Zugleich das Adresspräfix: „en“ ergibt /en/… . '
                            .'Die Standardsprache bekommt kein Präfix und lässt sich nicht ändern.'),

                    Select::make('richtung')
                        ->label('Schreibrichtung')
                        ->options([
                            'ltr' => 'Links nach rechts (Deutsch, Englisch, Russisch …)',
                            'rtl' => 'Rechts nach links (Arabisch, Farsi, Hebräisch …)',
                        ])
                        ->default('ltr')
                        ->required()
                        ->native(false),

                    TextInput::make('label')
                        ->label('Eigenbezeichnung')
                        ->required()
                        ->helperText('So, wie die Sprache sich selbst nennt — „Русский“, nicht „Russisch“. '
                            .'Im Umschalter erkennt man nur die eigene Sprache wieder.'),

                    TextInput::make('label_deutsch')
                        ->label('Deutscher Name')
                        ->required()
                        ->helperText('Für dieses Panel und für die Vorlesehilfe im deutschen Umschalter.'),
                ]),

            Section::make('Sichtbarkeit')
                ->columns(2)
                ->schema([

                    Toggle::make('aktiv')
                        ->label('Öffentlich sichtbar')
                        // Die Standardsprache abzuschalten würde die Website
                        // abschalten. Das darf kein Versehen sein können.
                        ->disabled(fn (?Language $record) => $record?->ist_standard)
                        ->helperText('Erst einschalten, wenn Übersetzungen vorliegen. '
                            .'Vorher erscheint die Sprache in keinem Umschalter.'),

                    Toggle::make('ist_standard')
                        ->label('Standardsprache')
                        ->disabled(fn (?Language $record) => $record?->ist_standard)
                        ->helperText('Die Sprache ohne Adresspräfix und der Rückfall für '
                            .'fehlende Übersetzungen. Es kann nur eine geben.'),

                    TextInput::make('position')
                        ->label('Reihenfolge')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->helperText('Kleine Zahl zuerst — bestimmt die Reihenfolge im Umschalter.'),

                    Select::make('fallback_code')
                        ->label('Rückfall auf')
                        ->options(fn () => Language::alle()->pluck('label_deutsch', 'code')->all())
                        ->placeholder('Standardsprache')
                        ->native(false)
                        ->helperText('Welche Sprache gezeigt wird, wenn eine Übersetzung fehlt. '
                            .'Leer lassen heisst: die Standardsprache.'),
                ]),
        ]);
    }
}
