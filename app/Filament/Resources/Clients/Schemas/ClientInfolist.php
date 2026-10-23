<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cliente')
                    ->columns(2)
                    ->components([
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label('Teléfono')
                            ->placeholder('—'),
                        TextEntry::make('user.email')
                            ->label('Cuenta de usuario')
                            ->placeholder('Sin cuenta registrada'),
                        TextEntry::make('created_at')
                            ->label('Registro')
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
