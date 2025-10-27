<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum Haf_kst_type: int implements HasLabel,HasColor
{
  case قائم = 1;
  case ارشيف = 2;
    case فائض = 3;
    case جزئي = 4;
  case بالخطأ = 5;
  case ملغي = 6;




  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::قائم => 'info',
      self::ارشيف => 'danger',
        self::بالخطأ => 'warning',
        self::ملغي => 'warning',


    };
  }
}


