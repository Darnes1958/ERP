<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum TwoUnit: int implements HasLabel,HasColor,HasIcon
{
  case احادي = 0;
  case ثنائي = 1;


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::احادي => 'success',
      self::ثنائي => 'info',
    };
  }
  public function getIcon(): ?string
  {
    return match ($this) {
      self::احادي => 'heroicon-m-minus',
      self::ثنائي => 'heroicon-m-bars-2',
    };
  }
}


