<?php

namespace App\Filament\ins\Resources\Mains\Pages;

use App\Filament\ins\Resources\Mains\MainResource;
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

