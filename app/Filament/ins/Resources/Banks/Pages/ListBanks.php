<?php

namespace App\Filament\ins\Resources\Banks\Pages;

use App\Filament\ins\Resources\Banks\BankResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBanks extends ListRecords
{
    protected static string $resource = BankResource::class;
    protected ?string $heading='مصارف';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('ادخال مصرف'),
        ];
    }
}
