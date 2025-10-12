<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum Tar_type: int implements HasLabel,HasColor
{
    case من_الفائض = 1;
    case من_الخطأ = 2;
    case من_قسط_مخصوم =3;
    case ترجيع_مبلغ =4;


    public function getLabel(): ?string
    {
        return $this->name;
    }
    public function getColor(): string | array | null
    {
        return match ($this) {
            self::من_الفائض => 'success',
            self::من_الخطأ => 'info',
            self::من_قسط_مخصوم => 'primary',
            self::ترجيع_مبلغ => 'warning',
        };
    }

}


