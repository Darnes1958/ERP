<?php

namespace App\Filament\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Main;
use Tiptap\Nodes\Text;

class MainTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Main::query())

            ->columns([
                TextColumn::make('id'),
                TextColumn::make('Customer.name'),
                TextColumn::make('sul'),
                TextColumn::make('sul_begin')
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
