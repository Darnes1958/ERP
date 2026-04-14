<?php

namespace App\Filament\Resources\PriceTypes\Pages;

use App\Filament\Resources\PriceTypes\PriceTypeResource;
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
