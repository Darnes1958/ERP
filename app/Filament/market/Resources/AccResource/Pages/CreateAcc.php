<?php

namespace App\Filament\market\Resources\AccResource\Pages;

use App\Filament\market\Resources\AccResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAcc extends CreateRecord
{
    protected static string $resource = AccResource::class;
    protected ?string $heading='';
    protected static bool $canCreateAnother = false;
    public function getMaxContentWidth(): ?string
    {
        return '3xl';
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('create');
    }
}
