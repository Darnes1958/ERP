<?php

namespace App\Filament\ins\Resources\MainResource\Pages;

use App\Filament\ins\Resources\MainResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class EditMain extends EditRecord
{
    protected static string $resource = MainResource::class;
  protected ?string $heading = '';
  public function getBreadcrumbs(): array
  {
    return [""];
  }
  public function getTitle():  string|Htmlable
  {
    return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">تعديل عقود</div>');
  }
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
