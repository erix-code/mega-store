<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la tienda')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(170)
                            ->unique(ignoreRecord: true),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->maxLength(30),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),
                    ]),
            ]);
    }
}
