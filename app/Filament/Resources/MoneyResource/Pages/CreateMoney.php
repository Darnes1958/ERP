<?php

namespace App\Filament\Resources\MoneyResource\Pages;

use App\Filament\Resources\MoneyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMoney extends CreateRecord
{
    protected static string $resource = MoneyResource::class;
    protected ?string $heading='';
  protected static bool $canCreateAnother = false;

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('create');
  }

}
