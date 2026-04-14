<?php

namespace App\Filament\market\Resources\Masrofats\Pages;

use App\Filament\market\Resources\Masrofats\MasrofatResource;
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
