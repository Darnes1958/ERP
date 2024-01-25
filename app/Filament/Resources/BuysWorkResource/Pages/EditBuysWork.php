<?php

namespace App\Filament\Resources\BuysWorkResource\Pages;

use App\Filament\Resources\BuysWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuysWork extends EditRecord
{
    protected static string $resource = BuysWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
