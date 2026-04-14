<?php

namespace App\Filament\market\Resources\PriceSells\Pages;

use App\Filament\market\Resources\PriceSells\PriceSellResource;
use Filament\Resources\Pages\ListRecords;

class ListPriceSells extends ListRecords
{
    protected static string $resource = PriceSellResource::class;

    protected ?string $heading='اسعار الاصناف';
    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
