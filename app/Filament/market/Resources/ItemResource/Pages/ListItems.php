<?php

namespace App\Filament\market\Resources\ItemResource\Pages;

use App\Filament\market\Resources\ItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    public function getTitle():  string|Htmlable
    {
        return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">أصناف</div>');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
             ->label('إضافة صنف جديد'),
        ];
    }
}
