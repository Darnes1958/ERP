<?php

namespace App\Filament\market\Resources\PerResource\Pages;

use App\Filament\market\Resources\PerResource;
use App\Models\Place_stock;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;


class CreatePer extends CreateRecord
{
    protected static string $resource = PerResource::class;

 //  protected function getCreateFormAction(): Action
 //  {
 //      return parent::getCreateFormAction()
 //         ->extraAttributes(['type' => 'button', 'wire:click' => 'create']);
 //  }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected function beforeCreate(): void
    {
        $cuurent=$this->data['Per_tran'];
        foreach ($cuurent as $item) {
            if (Place_stock::where('item_id', $item['item_id'])
                    ->where('place_id', $this->data['place_from'])
                    ->first()->stock1 < $item['quantity']) {
                Notification::make()->warning()->title('يوجد صنف او اصناف رصيدها لا يسمح')
                    ->body('يجب مراجعة الكميات')
                    ->persistent()
                    ->send();
                $this->halt();
            }
        }
    }
}
