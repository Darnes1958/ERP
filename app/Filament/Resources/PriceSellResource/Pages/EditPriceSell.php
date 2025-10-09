<?php

namespace App\Filament\Resources\PriceSellResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\PriceSellResource;
use Filament\Actions;
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
