<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del usuario')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8)
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null),
                        Toggle::make('is_admin')
                            ->label('Administrador')
                            ->helperText('Los administradores pueden acceder a este panel.')
                            ->default(false),
                        Select::make('store_id')
                            ->label('Tienda administrada')
                            ->relationship('store', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Admin de una tienda específica. Vacío = administrador global.')
                            ->default(null),
                    ]),
            ]);
    }
}
