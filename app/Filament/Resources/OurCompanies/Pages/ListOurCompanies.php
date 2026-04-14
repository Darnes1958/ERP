<?php

namespace App\Filament\Resources\OurCompanies\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\OurCompanies\OurCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOurCompanies extends ListRecords
{
    protected static string $resource = OurCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
