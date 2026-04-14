<?php

namespace App\Filament\ins\Resources\Overksts\Pages;

use App\Filament\ins\Resources\Overksts\OverkstResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOverkst extends EditRecord
{
    protected static string $resource = OverkstResource::class;
    protected ?string $heading='';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
