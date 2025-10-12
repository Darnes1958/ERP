<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum KsmType: int implements HasLabel,HasColor
{
  case نقدا = 1;
  case المصرف = 2;
  case صك = 3;
  case الكتروني = 4;


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::نقدا => 'info',
      self::المصرف => 'primary',
        self::صك => 'success',
        self::الكتروني => 'warning',


    };
  }
}


