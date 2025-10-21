<?php

namespace App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\Pages;

use App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\HafithaTranResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditHafithaTran extends EditRecord
{
    protected static string $resource = HafithaTranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
