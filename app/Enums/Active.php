<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum Active: int implements HasLabel,HasColor
{
  case مفتوح = 1;
  case مقفل = 0;


  public function getLabel(): ?string
  {
      return str_replace('_',' ',$this->name);
  }
  public function getColor(): string | array | null
  {
    return match ($this) {


      self::مفتوح => 'success',
      self::مقفل => 'danger',
    };
  }

}


