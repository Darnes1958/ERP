<?php

namespace App\Filament\Resources\CustomerTypeResource\Pages;

use App\Filament\Resources\CustomerTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerTypes extends ListRecords
{
    protected static string $resource = CustomerTypeResource::class;

    protected ?string $heading='تصنيف الزبائن';
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة'),
        ];
    }
}
