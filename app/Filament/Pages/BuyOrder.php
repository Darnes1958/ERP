<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BuyOrder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.buy-order';

  protected ?string $heading = '';
    public static ?string $title = 'فاتورة شراء جديدة';
    protected static ?string $navigationGroup='فواتير شراء';
    protected static ?int $navigationSort=1;

}
