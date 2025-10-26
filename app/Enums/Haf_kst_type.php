<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum Haf_kst_type: int implements HasLabel,HasColor
{
  case قائم = 1;
  case ارشيف = 2;
  case بالخطأ = 3;
  case ملغي = 4;


  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::قائم => 'info',
      self::ارشيف => 'primary',
        self::بالخطأ => 'danger',
        self::ملغي => 'warning',


    };
  }
}


