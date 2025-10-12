<?php

namespace App\Filament\market\Resources\CustomerResource\Pages;

use App\Filament\market\Resources\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;
  public function getTitle():  string|Htmlable
  {
    return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">زبائن</div>');
  }
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
              ->label('إضافة زبون جديد'),
        ];
    }
}
