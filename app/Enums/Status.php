<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum Status: int implements HasLabel,HasColor
{
  case غير_مرجع = 1;
  case مرجع = 2;

  case مصحح = 3;


  public function getLabel(): ?string
  {
      return str_replace('_',' ',$this->name);
  }
  public function getColor(): string | array | null
  {
    return match ($this) {

      self::غير_مرجع => 'info',
      self::مرجع => 'success',
      self::مصحح => 'primary',
    };
  }

}


