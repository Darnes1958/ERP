<?php

namespace App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\Pages;

use App\Filament\Ins\Resources\HafithaResource\Resources\HafithaTrans\HafithaTranResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHafithaTran extends ViewRecord
{
    protected static string $resource = HafithaTranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
