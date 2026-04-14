<?php

namespace App\Filament\ins\Resources\Wrongksts\Pages;

use App\Filament\ins\Resources\Wrongksts\WrongkstResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWrongkst extends EditRecord
{
    protected static string $resource = WrongkstResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
