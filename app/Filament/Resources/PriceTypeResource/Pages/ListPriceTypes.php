<?php

namespace App\Filament\Resources\PriceTypeResource\Pages;

use App\Filament\Resources\PriceTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPriceTypes extends ListRecords
{
    protected static string $resource = PriceTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
