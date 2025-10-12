<?php

namespace App\Filament\ins\Resources\BankResource\Pages;

use App\Filament\ins\Resources\BankResource;
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
