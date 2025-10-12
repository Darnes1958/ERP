<?php

namespace App\Filament\market\Resources\RentResource\Pages;

use App\Filament\market\Resources\RentResource;
use Filament\Actions\DeleteAction;
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
