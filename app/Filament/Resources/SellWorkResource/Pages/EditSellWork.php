<?php

namespace App\Filament\Resources\SellWorkResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\SellWorkResource;
use Filament\Actions;
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
