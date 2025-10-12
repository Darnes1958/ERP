<?php

namespace App\Filament\market\Resources\PriceTypeResource\Pages;

use App\Filament\market\Resources\PriceTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPriceType extends EditRecord
{
    protected static string $resource = PriceTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
