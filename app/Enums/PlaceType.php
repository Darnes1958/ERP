<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum PlaceType: int implements HasLabel,HasColor,HasIcon
{
  case مخزن = 0;
  case صالة = 1;


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::مخزن => 'info',
      self::صالة => 'success',
    };
  }
  public function getIcon(): ?string
  {
    return match ($this) {
      self::مخزن => 'heroicon-m-building-office',
      self::صالة => 'heroicon-m-building-storefront',

    };
  }
}


