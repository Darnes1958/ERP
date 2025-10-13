<?php

namespace App\Filament\market\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected ?string $heading='صفحة المبيعات';

  public function getColumns(): int|array
  {
    return 6;
  }

}
