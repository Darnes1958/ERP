<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum RecWhoView: int implements HasLabel,HasColor,HasIcon
{
  case قبض = 1;
  case دفع = 2;
  case قبض_فاتورة = 3;
  case دفع_فاتورة = 4;
  case دفـع_فاتورة = 5;
  case قبـض_فاتورة = 6;
  case فاتورة_مبيعات = 7;


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::قبض => 'success',
      self::دفع => 'danger',
      self::قبض_فاتورة => 'success',
      self::دفع_فاتورة => 'danger',
      self::قبـض_فاتورة => 'success',
      self::دفـع_فاتورة => 'danger',
      self::فاتورة_مبيعات => 'blue',

    };
  }
  public function getIcon(): ?string
  {
    return match ($this) {
      self::قبض => 'heroicon-m-arrow-long-left',
      self::دفع => 'heroicon-m-arrow-long-right',
      self::قبض_فاتورة => 'heroicon-m-arrow-long-left',
      self::دفع_فاتورة => 'heroicon-m-arrow-long-right',
        self::قبـض_فاتورة => 'heroicon-m-arrow-left-circle',
        self::دفـع_فاتورة => 'heroicon-m-arrow-right-circle',
      self::فاتورة_مبيعات => 'heroicon-m-shopping-cart',

    };
  }
}


