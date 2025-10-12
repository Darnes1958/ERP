<?php

namespace App\Filament\market\Resources\ItemResource\Pages;

use App\Filament\market\Resources\ItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;
  protected ?string $heading="";
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
