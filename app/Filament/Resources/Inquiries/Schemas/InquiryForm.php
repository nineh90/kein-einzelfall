<?php

namespace App\Filament\Resources\Inquiries\Schemas;

use App\Models\Inquiry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Bearbeitung')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label('Stand')
                        ->options(Inquiry::STATUS)
                        ->default('offen')
                        ->required()
                        ->native(false)
                        // Abschlusszeitpunkt setzt die Aufbewahrungsfrist in Gang,
                        // deshalb automatisch statt von Hand.
                        ->afterStateUpdated(fn ($state, $set) => $set(
                            'erledigt_at',
                            $state === 'erledigt' ? now() : null
                        ))
                        ->live(),

                    // Nur Anzeige (08.10.2026): Von Hand geleert oder in die
                    // Zukunft gesetzt, hielt es die Löschfrist an. Gesetzt wird
                    // es über den Stand oben, gespeichert trotzdem.
                    DateTimePicker::make('erledigt_at')
                        ->label('Erledigt am')
                        ->seconds(false)
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Ab hier läuft die Aufbewahrungsfrist. Setzt sich von selbst, wenn der Stand auf „Erledigt“ wechselt.'),
                ]),

            Section::make('Die Anfrage')
                ->description('Verschlüsselt gespeichert. Bitte nicht in andere Systeme kopieren.')
                ->schema([
                    TextInput::make('betreff')->label('Betreff')->disabled(),

                    Textarea::make('nachricht')
                        ->label('Nachricht')
                        ->rows(12)
                        ->disabled(),
                ]),

            Section::make('Absender')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->disabled()
                        ->placeholder('keine Angabe'),

                    TextInput::make('email')
                        ->label('E-Mail-Adresse')
                        ->disabled()
                        ->placeholder('keine Angabe — Antwort nicht möglich')
                        ->helperText(fn ($record) => $record?->istAnonym()
                            ? 'Diese Anfrage kam ohne Absender. Eine Antwort ist nicht möglich.'
                            : null),
                ]),
        ]);
    }
}
