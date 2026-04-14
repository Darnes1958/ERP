<?php

namespace App\Filament\ins\Resources\MainArcs\Pages;

use App\Filament\ins\Resources\MainArcs\MainArcResource;
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
