<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use Filament\Forms\Components\Repeater;
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
                Section::make('Pedido')
                    ->columns(2)
                    ->components([
                        Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn (string $operation): bool => $operation === 'create')
                            ->createOptionForm([
                                TextInput::make('name')->label('Nombre')->required(),
                                TextInput::make('email')->label('Email')->email()->required(),
                                TextInput::make('phone')->label('Teléfono'),
                            ]),
                        Select::make('status')
                            ->label('Estado')
                            ->options(OrderStatus::class)
                            ->default(OrderStatus::Pending)
                            ->required(),
                    ]),
                Section::make('Artículos')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->components([
                        Repeater::make('items')
                            ->label('Productos')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Producto')
                                    ->options(fn (): array => Product::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),
                            ])
                            ->columns(2)
                            ->required()
                            ->minItems(1)
                            ->columnSpanFull(),
                    ]),
                Section::make('Dirección de envío')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->columns(3)
                    ->components([
                        TextInput::make('recipient_name')
                            ->label('Destinatario')
                            ->maxLength(150),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->maxLength(30),
                        TextInput::make('line1')
                            ->label('Dirección')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('city')
                            ->label('Ciudad')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('state')
                            ->label('Estado')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('postal_code')
                            ->label('Código postal')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('country')
                            ->label('País')
                            ->length(2)
                            ->default('BO'),
                    ]),
            ]);
    }
}
