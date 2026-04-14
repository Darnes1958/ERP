<?php

namespace App\Filament\market\Resources\PriceSells\Pages;

use App\Filament\market\Resources\PriceSells\PriceSellResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPriceSell extends EditRecord
{
    protected static string $resource = PriceSellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
