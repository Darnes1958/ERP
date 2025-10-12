<?php

namespace App\Filament\market\Resources\BuyResource\Pages;

use App\Filament\market\Resources\BuyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBuys extends ListRecords
{
    protected static string $resource = BuyResource::class;
    protected ?string $heading="";

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
