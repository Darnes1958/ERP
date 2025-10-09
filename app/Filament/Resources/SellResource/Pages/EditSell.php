<?php

namespace App\Filament\Resources\SellResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\SellResource;
use Filament\Actions;
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
