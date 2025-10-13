<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;

enum MyTextSize:  string
{
    case ExtraSmall = 'xs';

    case Small = 'sm';

    case Medium = 'md';

    case Large = 'lg';
    case ExtraLarge = 'xl';
    case TwoExtraLarge = '2xl';
    case ThreeExtraLarge = '3xl';
    case FourExtraLarge = '4xl';
}


