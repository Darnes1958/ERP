<?php

namespace App\Filament\ins\Resources\Banks\Pages;

use App\Filament\ins\Resources\Banks\BankResource;
use App\Models\Main;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBank extends EditRecord
{
    protected static string $resource = BankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $last = $this->getRecord()->id;
        Main::where('bank_id', $this->getRecord()->id)->update(['taj_id'=>$this->data['taj_id']]);
    }
}
