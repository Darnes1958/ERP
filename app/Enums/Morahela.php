<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum Morahela: int implements HasLabel,HasColor,HasIcon
{
  case غير_مرحلة = 0;
  case مرحلة = 1;


  public function getLabel(): ?string
  {
      return str_replace('_',' ',$this->name);
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::مرحلة => 'success',
      self::غير_مرحلة => 'danger',
    };
  }
  public function getIcon(): ?string
  {
    return match ($this) {
      self::مرحلة => 'heroicon-m-check-circle',
      self::غير_مرحلة => 'heroicon-m-plus-circle',
    };
  }
}


