<?php

namespace App\Filament\Resources\GlossaryTerms\Schemas;

use App\Models\GlossaryTerm;
use App\Models\Language;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GlossaryTermForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Begriff')
                ->columns(2)
                ->schema([
                    TextInput::make('kuerzel')
                        ->label('Abkürzung')
                        ->maxLength(60)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set, $get, $context) => $context === 'create' && $state
                            ? $set('slug', Str::slug($state))
                            : null)
                        ->helperText('So, wie sie im Bescheid steht — „GdB“, „SGB XIV“, „OEG“. '
                            .'Kann leer bleiben, wenn der Begriff keine Abkürzung hat.'),

                    TextInput::make('begriff')
                        ->label('Ausgeschrieben')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set, $get, $context) => $context === 'create' && ! $get('kuerzel')
                            ? $set('slug', Str::slug($state))
                            : null)
                        ->helperText('„Grad der Behinderung“'),

                    TextInput::make('slug')
                        ->label('Sprungziel')
                        ->required()
                        ->rules(['regex:/^[a-z0-9-]+$/'])
                        ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, $get) => $rule
                            ->where('locale', $get('locale') ?: Language::standardCode()))
                        ->helperText('Damit lässt sich der Eintrag einzeln verlinken: '
                            .'/glossar#gdb. Möglichst nicht mehr ändern — verschickte '
                            .'Links zeigen sonst ins Leere.'),

                    Select::make('locale')
                        ->label('Sprache')
                        ->options(fn () => Language::alle()->pluck('label_deutsch', 'code')->all())
                        ->default(fn () => Language::standardCode())
                        ->required()
                        ->native(false)
                        ->disabledOn('edit'),

                    Select::make('uebersetzungs_gruppe')
                        ->label('Übersetzung von')
                        ->options(fn () => GlossaryTerm::query()
                            ->where('locale', Language::standardCode())
                            ->orderBy('begriff')
                            ->pluck('begriff', 'uebersetzungs_gruppe')
                            ->all())
                        ->searchable()
                        ->native(false)
                        ->visible(fn ($get) => $get('locale') && $get('locale') !== Language::standardCode())
                        ->helperText('Zu welchem deutschen Begriff das hier gehört.'),
                ]),

            Section::make('Erklärung')
                ->schema([
                    Textarea::make('erklaerung')
                        ->label('In eigenen Worten')
                        ->required()
                        ->rows(4)
                        /*
                         * Der Hinweis auf die Länge ist kein Stilwunsch. Wer
                         * einen Begriff nachschlägt, steckt meistens gerade
                         * mitten in etwas anderem — einem Bescheid, einem
                         * Formular. Eine Erklärung, die selbst wieder drei
                         * Fachbegriffe braucht, hilft dort niemandem.
                         */
                        ->helperText('Zwei, drei Sätze reichen. Wer hier nachschlägt, sitzt '
                            .'meist gerade über einem Bescheid und braucht eine Antwort, '
                            .'keine zweite Recherche. Weiterführendes gehört in den '
                            .'Verweis darunter.'),

                    TextInput::make('mehr_url')
                        ->label('Weiterführender Verweis — Ziel')
                        ->helperText('Eine eigene Themenseite oder der Gesetzestext, z.B. '
                            .'/erwerbsminderungsrente. Kann leer bleiben.'),

                    TextInput::make('mehr_label')
                        ->label('Weiterführender Verweis — Beschriftung')
                        ->helperText('„Zur Seite über den Schwerbehindertenausweis“ statt „mehr“ — '
                            .'Vorlesehilfen lesen Links auch aus dem Zusammenhang gerissen vor. '
                            .'Vorgabe: „Mehr dazu“.'),
                ]),

            Section::make()
                ->schema([
                    DateTimePicker::make('published_at')
                        ->label('Veröffentlicht am')
                        ->default(now())
                        ->seconds(false)
                        ->helperText('Leer lassen = Entwurf, im Glossar nicht sichtbar.'),
                ]),
        ]);
    }
}
