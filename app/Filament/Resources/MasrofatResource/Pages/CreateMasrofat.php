<?php

namespace App\Filament\Resources\MasrofatResource\Pages;

use App\Filament\Resources\MasrofatResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasrofat extends CreateRecord
{
    protected ?string $heading='';
    protected static string $resource = MasrofatResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
