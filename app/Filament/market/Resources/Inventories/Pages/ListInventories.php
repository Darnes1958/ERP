<?php

namespace App\Filament\Market\Resources\Inventories\Pages;

use App\Filament\Market\Resources\Inventories\InventoryResource;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Place_stock;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->before(function (CreateAction $action, Inventory $record) {
                    $place=Place_stock::find($record->place_stock_id);
                    $place->stock1=$record->actual_balance;
                    $place->save();
                    $item=Item::find($record->item_id);
                    $item->stock1=$item->stock1+$record->difference;
                    $item->save();
                })
                ->preserveFormDataWhenCreatingAnother(
                    ['place_id']
                )
            ->successRedirectUrl(fn()=>$this->getResource()::getUrl('create'))

        ];
    }
}
