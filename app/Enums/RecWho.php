<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum RecWho: int implements HasLabel,HasColor,HasIcon
{
  case قبض = 1;
  case دفع = 2;
  case قبض_مبيعات = 3;
  case دفع_مبيعات = 4;
  case قبض_مشتريات = 5;
  case دفع_مشتريات = 6;

  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::قبض => 'success',
      self::دفع => 'danger',
      self::قبض_مبيعات => 'success',
      self::دفع_مبيعات => 'danger',
      self::قبض_مشتريات => 'success',
      self::دفع_مشتريات => 'danger',
    };
  }
  public function getIcon(): ?string
  {
    return match ($this) {
      self::قبض => 'heroicon-m-arrow-long-left',
      self::دفع => 'heroicon-m-arrow-long-right',
      self::قبض_مبيعات => 'heroicon-m-arrow-long-left',
      self::دفع_مبيعات => 'heroicon-m-arrow-long-right',
      self::قبض_مشتريات => 'heroicon-m-arrow-long-left',
      self::دفع_مشتريات => 'heroicon-m-arrow-long-right',
    };
  }
}


