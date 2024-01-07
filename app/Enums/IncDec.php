<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum IncDec: int implements HasLabel,HasColor,HasIcon
{
  case طبيعي = 0;
  case يزداد = 1;
  case ينقص = 2;

  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::طبيعي => 'info',
      self::يزداد => 'success',
      self::ينقص => 'danger',

    };
  }
  public function getIcon(): ?string
  {
    return match ($this) {
      self::طبيعي => 'heroicon-m-check-circle',
      self::يزداد => 'heroicon-m-plus-circle',
      self::ينقص => 'heroicon-m-minus-circle',

    };
  }
}


