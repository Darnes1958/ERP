<?php

namespace App\Filament\Resources\MasrofatResource\Pages;

use App\Filament\Resources\MasrofatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasrofat extends EditRecord
{
    protected static string $resource = MasrofatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
