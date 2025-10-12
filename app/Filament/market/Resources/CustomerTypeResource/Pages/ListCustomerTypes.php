<?php

namespace App\Filament\market\Resources\CustomerTypeResource\Pages;

use App\Filament\market\Resources\CustomerTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerTypes extends ListRecords
{
    protected static string $resource = CustomerTypeResource::class;

    protected ?string $heading='تصنيف الزبائن';
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة'),
        ];
    }
}
