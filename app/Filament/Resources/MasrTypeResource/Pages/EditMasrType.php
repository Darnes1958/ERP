<?php

namespace App\Filament\Resources\MasrTypeResource\Pages;

use App\Filament\Resources\MasrTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasrType extends EditRecord
{
    protected static string $resource = MasrTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
