<?php

namespace App\Filament\Resources\AccResource\Pages;

use App\Filament\Resources\AccResource;
use Filament\Actions;
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
