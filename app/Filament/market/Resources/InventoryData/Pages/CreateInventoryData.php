<?php

namespace App\Filament\Market\Resources\InventoryData\Pages;

use App\Filament\Market\Resources\InventoryData\InventoryDataResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryData extends CreateRecord
{
    protected ?string $heading='';
    protected static bool $canCreateAnother=false;
    protected static string $resource = InventoryDataResource::class;
}
