<?php

namespace App\Filament\ins\Resources\WrongkstResource\Pages;

use App\Filament\ins\Resources\WrongkstResource;
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
