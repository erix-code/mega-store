<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Existencias')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : ($state < 5 ? 'warning' : 'success')),
                TextColumn::make('store.name')
                    ->label('Tienda')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Activo'),
                SelectFilter::make('store_id')
                    ->label('Tienda')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('low_stock')
                    ->label('Stock bajo (< 5)')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '<', 5)),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
