<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Domain\Sales\ValueObjects\OrderStatus;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
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
                TextColumn::make('items_count')
                    ->label('Artículos')
                    ->counts('items'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(OrderStatus::class)
                    ->multiple(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->label('Cambiar estado'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
