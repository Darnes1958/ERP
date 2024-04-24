<?php

namespace App\Filament\Resources\KazenaResource\Pages;

use App\Filament\Resources\KazenaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKazena extends EditRecord
{
    protected static string $resource = KazenaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
