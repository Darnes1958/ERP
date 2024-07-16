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
  case فاتورة_مشتريات = 8;
  case من_خزينة_الي_خزينة = 9;
  case من_خزينة_الي_مصرف = 10;
  case من_مصرف_الي_خزينة = 11;
  case من_مصرف_الي_مصرف = 12;
  case مصروفات = 13;
  case سحب_مرتب  = 14;
  case ترجيع_مبيعات  = 15;
  case ترجيع_مشتريات  = 16;


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
      self::فاتورة_مشتريات => 'Fuchsia',
      self::من_خزينة_الي_خزينة => 'success',
      self::من_خزينة_الي_مصرف => 'success',
      self::من_مصرف_الي_خزينة => 'danger',
      self::من_مصرف_الي_مصرف => 'danger',
      self::مصروفات => 'info',
      self::سحب_مرتب => 'danger',
      self::ترجيع_مبيعات => 'danger',
      self::ترجيع_مشتريات => 'danger',

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
      self::فاتورة_مشتريات => 'heroicon-m-shopping-cart',
      self::من_خزينة_الي_خزينة => 'heroicon-m-banknotes',
      self::من_خزينة_الي_مصرف => 'heroicon-m-banknotes',
      self::من_مصرف_الي_خزينة => 'heroicon-m-building-library',
      self::من_مصرف_الي_مصرف => 'heroicon-m-building-library',
      self::مصروفات => 'heroicon-m-gift',
      self::سحب_مرتب => 'heroicon-m-arrow-long-right',
      self::ترجيع_مبيعات => 'heroicon-m-arrow-long-right',
      self::ترجيع_مشتريات => 'heroicon-m-arrow-long-right',
    };
  }
}


