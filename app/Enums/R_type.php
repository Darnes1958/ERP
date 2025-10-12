<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum R_type: int implements HasLabel,HasColor
{
  case قيمة = 1;
  case نسبة = 2;



  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::قيمة => 'info',
      self::نسبة => 'primary',



    };
  }
}


