<?php

namespace App\Filament\Resources\BuysWorkResource\Pages;

use App\Filament\Resources\BuysWorkResource;
use App\Models\Buys_work;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuysWorks extends ListRecords
{
    protected static string $resource = BuysWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),


        ];
    }
}
