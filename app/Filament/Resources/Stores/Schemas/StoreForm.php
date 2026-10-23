<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la tienda')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(170)
                            ->unique(ignoreRecord: true),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->maxLength(30),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),
                    ]),
                Section::make('Configuración del sitio')
                    ->relationship('configuration')
                    ->description('Logo, tema de colores, redes sociales y ubicación usados por el frontend.')
                    ->columns(3)
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->disk('public')
                            ->directory('store-logos')
                            ->image()
                            ->imageEditor()
                            ->maxSize(1024)
                            ->columnSpan(1),
                        ColorPicker::make('main_color')
                            ->label('Color principal'),
                        ColorPicker::make('second_color')
                            ->label('Color secundario'),
                        ColorPicker::make('third_color')
                            ->label('Color terciario'),
                    ]),
                Section::make('Redes sociales')
                    ->relationship('configuration')
                    ->columns(3)
                    ->schema([
                        TextInput::make('facebook_url')
                            ->label('Facebook')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('instagram_url')
                            ->label('Instagram')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('x_url')
                            ->label('X (Twitter)')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('tiktok_url')
                            ->label('TikTok')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('whatsapp_url')
                            ->label('WhatsApp')
                            ->url()
                            ->maxLength(255),
                    ]),
                Section::make('Dirección y geolocalización')
                    ->relationship('configuration')
                    ->description('Los datos de ubicación se usan para mostrar la tienda en Google Maps.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('city')
                            ->label('Ciudad')
                            ->maxLength(100),
                        TextInput::make('state')
                            ->label('Estado')
                            ->maxLength(100),
                        TextInput::make('postal_code')
                            ->label('Código postal')
                            ->maxLength(20),
                        TextInput::make('country')
                            ->label('País')
                            ->length(2)
                            ->default('MX'),
                        TextInput::make('latitude')
                            ->label('Latitud')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90),
                        TextInput::make('longitude')
                            ->label('Longitud')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180),
                    ]),
            ]);
    }
}
