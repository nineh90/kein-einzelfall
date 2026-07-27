<?php

namespace App\Filament\Resources\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('von')
                ->label('Alte Adresse')
                ->required()
                ->unique(ignoreRecord: true)
                ->prefix(url('/').'/')
                ->helperText('Ohne führenden Schrägstrich, z.B. "impressum-2". '
                    .'Mit und ohne abschließenden Schrägstrich wird beides gefunden.'),

            TextInput::make('nach')
                ->label('Neues Ziel')
                ->required()
                ->helperText('Mit führendem Schrägstrich, z.B. "/impressum". '
                    .'Auch eine vollständige Adresse ist möglich.'),

            Select::make('status')
                ->label('Art der Weiterleitung')
                ->options([
                    301 => '301 – dauerhaft (Standard, überträgt die Suchmaschinen-Bewertung)',
                    302 => '302 – vorübergehend',
                ])
                ->default(301)
                ->required()
                ->native(false),

            TextInput::make('notiz')
                ->label('Notiz')
                ->helperText('Wofür diese Regel da ist — hilft in zwei Jahren beim Aufräumen.'),
        ]);
    }
}
