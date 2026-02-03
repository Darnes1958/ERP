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
            CreateAction::make(),

        ];
    }

}
