<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\Sales\ValueObjects\OrderStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resumen')
                    ->columns(3)
                    ->components([
                        TextEntry::make('id')
                            ->label('Orden #'),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (OrderStatus $state): string => $state->getLabel())
                            ->color(fn (OrderStatus $state): string => match ($state) {
                                OrderStatus::Pending => 'warning',
                                OrderStatus::Paid => 'success',
                                OrderStatus::Shipped => 'info',
                                OrderStatus::Delivered => 'success',
                                OrderStatus::Cancelled => 'danger',
                            }),
                        TextEntry::make('total')
                            ->label('Total')
                            ->money('MXN'),
                    ]),
                Section::make('Cliente')
                    ->columns(2)
                    ->components([
                        TextEntry::make('customer_name')
                            ->label('Nombre'),
                        TextEntry::make('customer_email')
                            ->label('Email'),
                        TextEntry::make('user.name')
                            ->label('Usuario registrado')
                            ->placeholder('Compra de invitado'),
                        TextEntry::make('created_at')
                            ->label('Fecha')
                            ->dateTime('d/m/Y H:i'),
                    ]),
                Section::make('Dirección de envío')
                    ->columns(2)
                    ->components([
                        TextEntry::make('shipping_address.recipient_name')
                            ->label('Destinatario')
                            ->placeholder('—'),
                        TextEntry::make('shipping_address.phone')
                            ->label('Teléfono')
                            ->placeholder('—'),
                        TextEntry::make('shipping_address.line1')
                            ->label('Dirección')
                            ->columnSpan(2)
                            ->placeholder('—'),
                        TextEntry::make('shipping_address.city')
                            ->label('Ciudad')
                            ->placeholder('—'),
                        TextEntry::make('shipping_address.state')
                            ->label('Estado')
                            ->placeholder('—'),
                        TextEntry::make('shipping_address.postal_code')
                            ->label('Código postal')
                            ->placeholder('—'),
                        TextEntry::make('shipping_address.country')
                            ->label('País')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
