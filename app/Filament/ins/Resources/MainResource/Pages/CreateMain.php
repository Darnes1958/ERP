<?php

namespace App\Filament\ins\Resources\MainResource\Pages;

use App\Filament\ins\Resources\MainResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMain extends CreateRecord
{
    protected static string $resource = MainResource::class;

  protected ?string $heading = '';
  public function getBreadcrumbs(): array
  {
    return [""];
  }

  protected function getRedirectUrl(): string
  {
    return $this->previousUrl ?? $this->getResource()::getUrl('create');
  }
}

