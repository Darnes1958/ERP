<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum Status: int implements HasLabel,HasColor,HasIcon
{
  case غير_نشط = 0;
  case نشط = 1;


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::نشط => 'success',
      self::غير_نشط => 'danger',
    };
  }
  public function getIcon(): ?string
  {
    return match ($this) {
      self::نشط => 'heroicon-m-check-circle',
      self::غير_نشط => 'heroicon-m-x-circle',
    };
  }
}


