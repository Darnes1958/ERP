<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum RecWho: int implements HasLabel,HasColor,HasIcon
{
  case ايصال_قبض = 1;
  case ايصال_دفع = 2;
  case ايصال_قبض_فاتورة = 3;

  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::ايصال_قبض => 'info',
      self::ايصال_دفع => 'success',
      self::ايصال_قبض_فاتورة => 'danger',

    };
  }
  public function getIcon(): ?string
  {
    return match ($this) {
      self::ايصال_قبض => 'heroicon-m-check-circle',
      self::ايصال_دفع => 'heroicon-m-plus-circle',
      self::ايصال_قبض_فاتورة => 'heroicon-m-minus-circle',

    };
  }
}


