<?php

namespace App\Filament\ins\Pages\Reports;

use App\Models\Main;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class mainRep extends Page
{
  protected ?string $heading = '';
  public function getBreadcrumbs(): array
  {
    return [""];
  }
  public static function shouldRegisterNavigation(): bool
  {
    return  auth()->user()->can('تقرير عن عقد');
  }
  public static function getNavigationBadge(): ?string
  {
    return Main::count();
  }

    protected string $view = 'filament.ins.pages.reports.main-rep';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    public static ?string $title = 'تقرير عن عقد';
    protected static string | \UnitEnum | null $navigationGroup='تقارير';
    protected static ?int $navigationSort=1;



}
