<?php

namespace App\Filament\ins\Resources\TarKsts\Pages;

use App\Filament\ins\Resources\TarKsts\TarKstResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTarKst extends EditRecord
{
    protected static string $resource = TarKstResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
