<?php

namespace App\Filament\Market\Resources\InventoryData\Pages;

use App\Filament\Market\Resources\InventoryData\InventoryDataResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditInventoryData extends EditRecord
{
    protected static string $resource = InventoryDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(function () {return Auth::id()==1;}),
        ];
    }
}
