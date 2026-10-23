<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del cliente')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->maxLength(30),
                        Select::make('user_id')
                            ->label('Cuenta de usuario')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->helperText('Vincular con una cuenta registrada (opcional)'),
                        Select::make('store_id')
                            ->label('Tienda')
                            ->relationship('store', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Tienda a la que pertenece el cliente (opcional)'),
                    ]),
            ]);
    }
}
