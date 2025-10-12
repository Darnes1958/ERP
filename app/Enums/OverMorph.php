<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum OverMorph: string implements HasLabel,HasColor
{
  case قائم = 'App\Models\Main';
  case ارشيف = 'App\Models\Main_arc';




  public function getLabel(): ?string
  {
    return $this->name;
  }
  public function getColor(): string | array | null
  {
    return match ($this) {
      self::قائم => 'success',
      self::ارشيف => 'info',

    };
  }

}


