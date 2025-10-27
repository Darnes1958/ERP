<?php

namespace App\Filament\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Main_arc;

class MainArcTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Main_arc::query())
            ->columns([
                TextColumn::make('id')->searchable()->sortable(),
                TextColumn::make('Customer.name')->searchable()->sortable(),
                TextColumn::make('acc')->searchable()->sortable(),
                TextColumn::make('sul')->searchable(),
                TextColumn::make('sul_begin')->searchable()->sortable()
            ])
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
