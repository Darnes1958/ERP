<?php

namespace App\Filament\Resources\PriceTypeResource\Pages;

use App\Filament\Resources\PriceTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPriceType extends EditRecord
{
    protected static string $resource = PriceTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
