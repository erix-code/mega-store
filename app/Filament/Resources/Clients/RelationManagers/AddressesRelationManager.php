<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function form(Schema $schema): Schema
    {
        return $schema
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
                    ->maxLength(255),
                TextInput::make('line2')
                    ->label('Dirección 2')
                    ->maxLength(255),
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
                    ->required()
                    ->length(2),
                Toggle::make('is_default')
                    ->label('Predeterminada')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('line1')
            ->columns([
                TextColumn::make('recipient_name')
                    ->label('Destinatario')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('line1')
                    ->label('Dirección')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('city')
                    ->label('Ciudad'),
                TextColumn::make('state')
                    ->label('Estado'),
                TextColumn::make('postal_code')
                    ->label('C.P.'),
                IconColumn::make('is_default')
                    ->label('Predeterminada')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
