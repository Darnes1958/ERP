<?php

namespace App\Filament\Resources\PerResource\Pages;

use App\Filament\Resources\PerResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPer extends EditRecord
{
    protected static string $resource = PerResource::class;

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()

            ->extraAttributes(['type' => 'button', 'wire:click' => 'save'])
            ;
    }
}
