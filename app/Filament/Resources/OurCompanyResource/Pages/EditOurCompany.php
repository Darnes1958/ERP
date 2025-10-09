<?php

namespace App\Filament\Resources\OurCompanyResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\OurCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOurCompany extends EditRecord
{
    protected static string $resource = OurCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
