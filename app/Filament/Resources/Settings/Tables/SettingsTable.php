<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company')
                    ->searchable(),
                IconColumn::make('has_exp')
                    ->boolean(),
                IconColumn::make('has_two')
                    ->boolean(),
                IconColumn::make('many_place')
                    ->boolean(),
                IconColumn::make('jomla')
                    ->boolean(),
                IconColumn::make('barcode')
                    ->boolean(),
                IconColumn::make('is_together')
                    ->boolean(),
                IconColumn::make('price_update')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
