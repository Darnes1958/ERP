<?php

namespace App\Filament\Resources\BuyResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\BuyResource;
use Filament\Actions;
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
