<?php

namespace App\Filament\Market\Resources\InventoryData\Pages;

use App\Filament\Market\Resources\InventoryData\InventoryDataResource;
use App\Models\InventoryData;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListInventoryData extends ListRecords
{
    protected static string $resource = InventoryDataResource::class;

    protected ?string $heading='';
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(function () {return Auth::id()==1;}),
        ];
    }
}
