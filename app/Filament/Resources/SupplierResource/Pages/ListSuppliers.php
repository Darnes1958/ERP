<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

  public function getTitle():  string|Htmlable
  {
    return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">موردين</div>');
  }
  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->label('إضافة مورد جديد'),
    ];
  }
}
