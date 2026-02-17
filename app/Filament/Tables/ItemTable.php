<?php

namespace App\Filament\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Item;

class ItemTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Item::query())

            ->columns([
                TextColumn::make('id')->searchable()->sortable(),
                TextColumn::make('barcode')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
            ])
            ->extraAttributes(['class' => 'mytable'])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
