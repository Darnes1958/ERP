<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum RecWho: int implements HasLabel,HasColor,HasIcon
{
  case قبض = 1;
  case دفع = 2;
  case قبض_فاتورة = 3;
  case دفع_فاتورة = 4;




  public function getLabel(): ?string
  {
      return str_replace('_',' ',$this->name);
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::قبض => 'success',
      self::دفع => 'danger',
      self::قبض_فاتورة => 'success',
      self::دفع_فاتورة => 'danger',



    };
  }
  public function getIcon(): ?string
  {
    return match ($this) {
      self::قبض => 'heroicon-m-arrow-long-left',
      self::دفع => 'heroicon-m-arrow-long-right',
      self::قبض_فاتورة => 'heroicon-m-arrow-long-left',
      self::دفع_فاتورة => 'heroicon-m-arrow-long-right',


    };
  }
}


