<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

use Filament\Support\Contracts\HasColor;

enum RecWhoMoney: int implements HasLabel,HasColor
{
  case خزينة_الي_خزينة = 1;
  case خزينة_الي_مصرف = 2;
  case مصرف_الي_خزينة = 3;
  case مصرف_الي_مصرف = 4;

  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::خزينة_الي_خزينة => 'success',
      self::خزينة_الي_مصرف => 'success',
      self::مصرف_الي_خزينة => 'danger',
      self::مصرف_الي_مصرف => 'danger',

    };
  }


}
