<?php

namespace App\Filament\Resources\MoneyResource\Pages;

use App\Filament\Resources\MoneyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMoney extends EditRecord
{
    protected static string $resource = MoneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
