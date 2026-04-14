<?php

namespace App\Filament\ins\Resources\Overksts\Pages;

use App\Filament\ins\Resources\Overksts\OverkstResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOverkst extends CreateRecord
{

    protected static string $resource = OverkstResource::class;
    protected ?string $heading='';
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
