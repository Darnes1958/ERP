<?php

namespace App\Filament\Resources\SellWorkResource\Pages;

use App\Filament\Resources\SellWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSellWorks extends ListRecords
{
    protected static string $resource = SellWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
