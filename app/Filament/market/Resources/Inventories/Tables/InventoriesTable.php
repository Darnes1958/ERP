<?php

namespace App\Filament\Market\Resources\Inventories\Tables;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Place;
use App\Models\Place_stock;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class InventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('InventoryData.data'),
                TextColumn::make('Place.name')
                 ->label('المكان')
                 ->searchable()
                 ->sortable(),
                TextColumn::make('item_id')
                    ->label('رقم الصنف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Item.name')
                    ->label('اسم الصنف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('book_balance'),
                TextColumn::make('actual_balance'),
                TextColumn::make('Place_stock.stock1')->label('الرصيد الحالي'),
                TextColumn::make('difference'),
                TextColumn::make('its_value'),
                TextColumn::make('created_at'),
            ])
            ->defaultSort('id','desc')
            ->recordUrl(false)
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('delete')
                    ->hiddenLabel()
                    ->visible(fn(Model $record): bool => $record->actual_balance==$record->Place_stock->stock1)
                    ->icon(Heroicon::Trash)
                    ->color('danger')
                    ->action(function (Model $record) {
                        $place=Place_stock::find($record->place_stock_id);
                        $place->stock1=$record->book_balance;
                        $place->inventory_balance-=$record->difference;
                        $place->save();
                        $item=Item::find($record->item_id);
                        $item->stock1-=$record->difference;
                        $item->save();
                        $record->delete();
                    }),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => $record->actual_balance==$record->Place_stock->stock1,
            )
            ->toolbarActions([


                        BulkAction::make('delete')
                            ->requiresConfirmation()
                            ->action(fn (Collection $records) => $records->each->delete())
                        ,

            ]);
    }
}
