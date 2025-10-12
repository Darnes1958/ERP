<?php

namespace App\Filament\market\Resources\MasrTypeResource\Pages;

use App\Filament\market\Resources\MasrTypeResource;
use App\Models\Masrofat;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasrType extends EditRecord
{
    protected static string $resource = MasrTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
            ->hidden(function ($record){Masrofat::where('masr_type_id', $record->id)->exists();}),
        ];
    }
}
