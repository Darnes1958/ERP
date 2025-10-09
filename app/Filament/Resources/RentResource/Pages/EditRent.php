<?php

namespace App\Filament\Resources\RentResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\RentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRent extends EditRecord
{
    protected static string $resource = RentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
