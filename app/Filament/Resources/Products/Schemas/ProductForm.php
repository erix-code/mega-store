<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(150),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(170)
                    ->unique(ignoreRecord: true)
                    ->hint('Se usa en la URL del producto'),
                Textarea::make('description')
                    ->label('Descripción')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->prefix('MX$')
                    ->minValue(0),
                TextInput::make('stock')
                    ->label('Existencias')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Select::make('store_id')
                    ->label('Tienda')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }
}
