<?php

namespace App\Filament\Resources\RecsuppResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\RecsuppResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListRecsupps extends ListRecords
{
    protected static string $resource = RecsuppResource::class;
  public function getTitle():  string|Htmlable
  {
    return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">ايصالات مورددين</div>');
  }
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
              ->label('إضافة إيصال'),

          ];
    }
}
