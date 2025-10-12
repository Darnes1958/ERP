<?php

namespace App\Filament\market\Resources\MoneyResource\Pages;

use App\Filament\market\Resources\MoneyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListMoney extends ListRecords
{
    protected static string $resource = MoneyResource::class;
  public function getTitle():  string|Htmlable
  {
    return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">تحويلات بين الخزائن والمصارف</div>');
  }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()

            ->label('اضافة'),
        ];
    }
}
