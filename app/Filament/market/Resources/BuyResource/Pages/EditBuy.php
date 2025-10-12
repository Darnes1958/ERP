<?php

namespace App\Filament\market\Resources\BuyResource\Pages;

use App\Filament\market\Resources\BuyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBuy extends EditRecord
{
    protected static string $resource = BuyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
