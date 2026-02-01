<?php

namespace App\Filament\Market\Resources\InventoryData\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryDataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data')
                 ->searchable(),
                TextColumn::make('notes')
                ->searchable(),
                IconColumn::make('active')
                ->label('مفتوح/مقفل')
                ->boolean(),
                TextColumn::make('created_at'),
                TextColumn::make('updated_at'),
                TextColumn::make('end_at')->label('قفل بتاريخ'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
               //
            ]);
    }
}
