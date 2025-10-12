<?php

namespace App\Filament\market\Resources\PriceTypeResource\Pages;

use App\Filament\market\Resources\PriceTypeResource;
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
