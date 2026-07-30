<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Language;
use App\Models\Page;
use App\Models\PageBlock;
use App\Rules\KollidiertNichtMitSprachpraefix;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    /**
     * Eine Übersetzungsgruppe braucht alles, was nicht die deutsche
     * Hauptfassung ist — also jede Fremdsprache und jede Fassung in Leichter
     * Sprache. Nur die deutsche Hauptfassung ist das Original und bildet ihre
     * eigene Gruppe.
     */
    private static function brauchtGruppe($get): bool
    {
        return ($get('locale') && $get('locale') !== Language::standardCode())
            || $get('fassung') === Page::FASSUNG_LEICHTE_SPRACHE;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Sprache und Fassung')
                ->description('Jede Sprachfassung ist eine eigene Seite mit eigener Adresse '
                    .'und eigenen Bausteinen. Eine Übersetzung darf also anders aufgebaut sein '
                    .'als das Original — das ist Absicht.')
                ->columns(2)
                ->schema([

                    Select::make('fassung')
                        ->label('Fassung')
                        ->options([
                            Page::FASSUNG_STANDARD => 'Alltags-Sprache (normal)',
                            Page::FASSUNG_LEICHTE_SPRACHE => 'Leichte Sprache',
                        ])
                        ->default(Page::FASSUNG_STANDARD)
                        ->required()
                        ->live()
                        ->native(false)
                        // Nachträglich zu wechseln hiesse, eine indexierte
                        // Adresse in einen anderen Bereich zu schieben.
                        ->disabledOn('edit')
                        ->helperText('Leichte Sprache ist keine eigene Sprache, sondern eine '
                            .'zweite Fassung derselben Seite auf Deutsch. Sie bekommt eine '
                            .'eigene Adresse unter /leichte-sprache/ und wird von der '
                            .'Hauptfassung aus verlinkt. Die Texte schreibt der Verein — '
                            .'Leichte Sprache hat ein eigenes Regelwerk und gehört von einer '
                            .'Prüfgruppe aus der Zielgruppe abgenommen.'),

                    Select::make('locale')
                        ->label('Sprache')
                        ->options(fn () => Language::alle()->pluck('label_deutsch', 'code')->all())
                        ->default(fn () => Language::standardCode())
                        ->required()
                        ->live()
                        ->native(false)
                        // Nachträglich die Sprache zu wechseln hiesse, eine
                        // indexierte Adresse in eine andere Sprachfassung zu
                        // schieben. Das ist kein Bedienschritt, das ist ein Umzug.
                        ->disabledOn('edit')
                        ->helperText('Nach dem Anlegen nicht mehr änderbar. '
                            .'Neue Sprachen legst du unter „Sprachen“ an.'),

                    Select::make('uebersetzungs_gruppe')
                        ->label('Übersetzung von')
                        ->options(fn () => Page::query()
                            ->where('locale', Language::standardCode())
                            ->orderBy('titel')
                            ->pluck('titel', 'uebersetzungs_gruppe')
                            ->all())
                        ->searchable()
                        ->native(false)
                        // Nur bei Übersetzungen: Eine Seite in der Standardsprache
                        // ist das Original und bildet ihre eigene Gruppe.
                        ->visible(fn ($get) => self::brauchtGruppe($get))
                        ->required(fn ($get) => self::brauchtGruppe($get))
                        ->helperText('Zu welcher Seite das hier gehört. Darüber finden '
                            .'Sprachumschalter, hreflang, Menü und der Wechsel zwischen '
                            .'Alltags-Sprache und Leichter Sprache zusammen.'),
                ]),

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
                        // Eindeutig innerhalb der Sprache, nicht darüber hinaus:
                        // /kontakt und /en/kontakt duerfen nebeneinander stehen.
                        ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, $get) => $rule
                            ->where('locale', $get('locale') ?: Language::standardCode())
                            ->where('fassung', $get('fassung') ?: Page::FASSUNG_STANDARD))
                        ->rules(['regex:/^[a-z0-9-]+$/'])
                        ->rules([KollidiertNichtMitSprachpraefix::fuerSeitenSlug()])
                        ->prefix(function ($get) {
                            $sprache = Language::finden($get('locale') ?: Language::standardCode())
                                ?? Language::standard();
                            $fassung = Page::FASSUNGEN[$get('fassung') ?: Page::FASSUNG_STANDARD] ?? '';

                            return rtrim(url($sprache->praefix()), '/').'/'.($fassung ? $fassung.'/' : '');
                        })
                        ->helperText('Nur Kleinbuchstaben, Ziffern und Bindestriche. '
                            .'Bei bestehenden Seiten möglichst nicht ändern — die Adresse '
                            .'ist bei Suchmaschinen bekannt. Falls doch: Weiterleitung anlegen. '
                            .'Übersetzungen dürfen und sollen einen eigenen Slug bekommen.'),

                    DateTimePicker::make('published_at')
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

                            Textarea::make('data.einleitung')
                                ->label('Einleitung')
                                ->rows(2)
                                ->visible(fn ($get) => in_array($get('typ'), ['schritte', 'accordion'], true))
                                ->helperText('Kurzer Text über der Liste. Kann leer bleiben.'),

                            Repeater::make('data.absaetze')
                                ->label('Absätze')
                                ->addActionLabel('Absatz hinzufügen')
                                ->visible(fn ($get) => in_array($get('typ'), ['text', 'text_media'], true))
                                ->simple(
                                    Textarea::make('absatz')->label('')->rows(4)->required()
                                ),

                            // --- Text mit Bild ---
                            TextInput::make('data.bild')
                                ->label('Bild (Pfad oder Adresse)')
                                ->visible(fn ($get) => $get('typ') === 'text_media')
                                ->helperText('Leer lassen zeigt eine Platzhalterfläche.'),

                            TextInput::make('data.bild_alt')
                                ->label('Bildbeschreibung')
                                ->visible(fn ($get) => $get('typ') === 'text_media')
                                ->helperText('Was auf dem Bild zu sehen ist — wird Menschen vorgelesen, '
                                    .'die es nicht sehen können. Bei rein schmückenden Bildern leer lassen.'),

                            Select::make('data.bild_seite')
                                ->label('Bild steht')
                                ->options(['rechts' => 'rechts', 'links' => 'links'])
                                ->default('rechts')
                                ->native(false)
                                ->visible(fn ($get) => $get('typ') === 'text_media'),

                            // --- Ablauf in Schritten ---
                            Repeater::make('data.schritte')
                                ->label('Schritte')
                                ->addActionLabel('Schritt hinzufügen')
                                ->visible(fn ($get) => $get('typ') === 'schritte')
                                ->itemLabel(fn (array $state) => $state['titel'] ?? null)
                                ->collapsible()
                                ->schema([
                                    TextInput::make('titel')->label('Überschrift des Schritts')->required(),
                                    Textarea::make('text')->label('Beschreibung')->rows(3),
                                ]),

                            // --- Fragen und Antworten ---
                            Repeater::make('data.eintraege')
                                ->label('Fragen')
                                ->addActionLabel('Frage hinzufügen')
                                ->visible(fn ($get) => $get('typ') === 'accordion')
                                ->itemLabel(fn (array $state) => $state['frage'] ?? null)
                                ->collapsible()
                                ->schema([
                                    TextInput::make('frage')->label('Frage')->required(),
                                    RichEditor::make('antwort')
                                        ->label('Antwort')
                                        ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList']),
                                ]),

                            // --- Hervorgehobener Hinweis ---
                            Select::make('data.art')
                                ->label('Art des Hinweises')
                                ->options([
                                    'hinweis' => 'Gut zu wissen (neutral)',
                                    'wichtig' => 'Wichtig (grün hervorgehoben)',
                                    'frist' => 'Frist beachten (mit Warnfarbe)',
                                ])
                                ->default('hinweis')
                                ->native(false)
                                ->visible(fn ($get) => $get('typ') === 'hinweis'),

                            Textarea::make('data.text')
                                ->label('Text des Hinweises')
                                ->rows(3)
                                ->visible(fn ($get) => $get('typ') === 'hinweis'),

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
