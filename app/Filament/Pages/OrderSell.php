<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class OrderSell extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.order-sell';
  protected ?string $heading = '';
  public static ?string $title = 'فاتورة مبيعات جديدة';
  protected static ?string $navigationGroup='فواتير مبيعات';

  protected static ?int $navigationSort=1;

}
