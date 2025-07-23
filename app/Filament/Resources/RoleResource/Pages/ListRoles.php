<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Livewire\Traits\Raseed;
use App\Models\Sell_tran;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    use Raseed;
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Dec Qs1')
             ->action(function (){

                 $trans=Sell_tran::query()->orderBy('sell_id')->get();
                 foreach ($trans as $tran){
                     $this->decQs2($tran->id,$tran->sell_id,$tran->item_id,1,$tran->q1);
                 }
                 Notification::make('ok')->title('Ok')->success()->send();

             })

        ];
    }
}
