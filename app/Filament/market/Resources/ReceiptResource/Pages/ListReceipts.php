<?php

namespace App\Filament\market\Resources\ReceiptResource\Pages;

use App\Filament\market\Resources\ReceiptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListReceipts extends ListRecords
{
    protected static string $resource = ReceiptResource::class;


  public function getTitle():  string|Htmlable
  {
    return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">ايصالات قبض ودفع</div>');
  }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
             ->createAnother(false)
             ->label('إضافة إيصال'),
        ];
    }
}
