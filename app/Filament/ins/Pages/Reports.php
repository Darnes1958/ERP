<?php

namespace App\Filament\ins\Pages;

use Filament\Pages\Page;


class Reports extends Page
{

  protected ?string $heading = '';
  public function getBreadcrumbs(): array
   {
    return [""];
   }
  public static function shouldRegisterNavigation(): bool
  {
    return  false;
  }

    public static ?string $title = 'تقرير عن مصرف';

    protected static string | \UnitEnum | null $navigationGroup='تقارير';
  protected static ?int $navigationSort=4;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.ins.pages.reports';
}
