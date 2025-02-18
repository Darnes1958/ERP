<?php

namespace App\Filament\Resources\PriceSellResource\Pages;

use App\Filament\Resources\PriceSellResource;
use Filament\Actions;
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
