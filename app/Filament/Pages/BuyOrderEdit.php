<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BuyOrderEdit extends Page
{
  protected ?string $heading = '';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.buy-order-edit';
    public static ?string $title = 'تعديل فاتورة شراء';
    protected static ?string $navigationGroup='فواتير شراء';
    protected static ?int $navigationSort=2;
}
