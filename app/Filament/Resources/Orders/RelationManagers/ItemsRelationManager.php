<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Producto'),
                TextColumn::make('unit_price')
                    ->label('Precio unitario')
                    ->money('MXN'),
                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('MXN')
                    ->summarize(Sum::make()
                        ->label('Total')
                        ->money('MXN')),
            ]);
    }
}
