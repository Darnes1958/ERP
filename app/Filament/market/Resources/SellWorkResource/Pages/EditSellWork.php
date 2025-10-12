<?php

namespace App\Filament\market\Resources\SellWorkResource\Pages;

use App\Filament\market\Resources\SellWorkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSellWork extends EditRecord
{
    protected static string $resource = SellWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
