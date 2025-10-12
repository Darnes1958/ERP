<?php

namespace App\Filament\ins\Pages\Reports;

use Filament\Pages\Page;

class RepStop extends Page
{
  protected ?string $heading = '';
  public function getBreadcrumbs(): array
  {
    return [""];
  }
  public static ?string $title = 'ايقاف الخصم';

  protected static string | \UnitEnum | null $navigationGroup='تقارير';
  protected static ?int $navigationSort=5;
  public static function shouldRegisterNavigation(): bool
  {
    return  auth()->user()->can('تقرير ايقاف الخصم');
  }

  protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.ins.pages.reports.rep-stop';
}
