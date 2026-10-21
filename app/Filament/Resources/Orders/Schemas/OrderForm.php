<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\Sales\ValueObjects\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Estado de la orden')
                    ->components([
                        Select::make('status')
                            ->label('Estado')
                            ->options(OrderStatus::class)
                            ->required(),
                    ]),
                Section::make('Datos del pedido (solo lectura)')
                    ->columns(2)
                    ->components([
                        TextInput::make('client.name')
                            ->label('Cliente')
                            ->disabled(),
                        TextInput::make('client.email')
                            ->label('Email')
                            ->disabled(),
                        TextInput::make('total')
                            ->label('Total')
                            ->prefix('MX$')
                            ->disabled(),
                        TextInput::make('created_at')
                            ->label('Fecha')
                            ->disabled(),
                    ]),
            ]);
    }
}
