<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Infrastructure\Persistence\Eloquent\Catalog\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del producto')
                    ->columns(2)
                    ->components([
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(150)
                            ->helperText('El slug se genera automáticamente a partir del nombre.'),
                        TextInput::make('price')
                            ->label('Precio')
                            ->required()
                            ->numeric()
                            ->prefix(currency_symbol())
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
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn (Set $set) => $set('category_id', null)),
                        Select::make('category_id')
                            ->label('Categoría')
                            ->options(fn (Get $get): array => Category::query()
                                ->where(function ($query) use ($get): void {
                                    $query->whereNull('store_id');

                                    if ($storeId = $get('store_id')) {
                                        $query->orWhere('store_id', $storeId);
                                    }
                                })
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required()
                            ->helperText('Muestra las categorías de la tienda seleccionada y las globales.'),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),
                Section::make('Imágenes')
                    ->description('La primera imagen marcada como principal se muestra en el listado.')
                    ->schema([
                        Repeater::make('images')
                            ->relationship()
                            ->defaultItems(0)
                            ->schema([
                                FileUpload::make('path')
                                    ->label('Imagen')
                                    ->disk('public')
                                    ->directory('products')
                                    ->image()
                                    ->imageEditor()
                                    ->required()
                                    ->maxSize(2048),
                                Toggle::make('is_main')
                                    ->label('Imagen principal')
                                    ->default(false),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
