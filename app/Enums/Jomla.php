<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum Jomla: int implements HasLabel,HasColor,HasIcon
{
  case قطاعي = 1;
  case جملة = 0;


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::قطاعي => 'info',
      self::جملة => 'success',


    };
  }
  public function getIcon(): ?string
  {
    return match ($this) {
      self::قطاعي => 'heroicon-m-check-circle',
      self::جملة => 'heroicon-m-plus-circle',


    };
  }
}


