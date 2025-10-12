<?php

namespace App\Filament\market\Resources\SellResource\Pages;

use App\Filament\market\Resources\SellResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSell extends EditRecord
{
    protected static string $resource = SellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
