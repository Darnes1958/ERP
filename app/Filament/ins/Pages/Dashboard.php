<?php

namespace App\Filament\ins\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected ?string $heading='صفحة التقسيط';

  public function getColumns(): int|array
  {
    return 4;
  }

}
