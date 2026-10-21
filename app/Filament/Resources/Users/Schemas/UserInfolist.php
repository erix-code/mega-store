<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Usuario')
                    ->columns(3)
                    ->components([
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        IconEntry::make('is_admin')
                            ->label('Administrador')
                            ->boolean(),
                        TextEntry::make('email_verified_at')
                            ->label('Email verificado')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Sin verificar'),
                        TextEntry::make('created_at')
                            ->label('Registro')
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
