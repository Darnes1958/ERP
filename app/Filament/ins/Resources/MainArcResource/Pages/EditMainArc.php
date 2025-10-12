<?php

namespace App\Filament\ins\Resources\MainArcResource\Pages;

use App\Filament\ins\Resources\MainArcResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMainArc extends EditRecord
{
    protected static string $resource = MainArcResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
