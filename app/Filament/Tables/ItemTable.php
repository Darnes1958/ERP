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
            ->query(fn (): Builder => Item::query()->join('place_sticks', 'place_stocks.item_id', '=', 'items.id')
            ->selectRaw('items.id,items.barcode,name,place_stocks.stock1'))
            ->modifyQueryUsing(function (Builder $query) use ($table): Builder {
                $arguments = $table->getArguments();

                if ($placeId = $arguments['place_id'] ?? null) {
                    $query->where('place_id', $placeId);
                }

                if ($noZero = $arguments['noZero'] ?? null) {
                    if ($noZero==1)
                     $query->where('stock1', '>', 0);
                }

                return $query;
            })

            ->columns([
                TextColumn::make('id')->searchable()->sortable(),
                TextColumn::make('barcode')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
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
