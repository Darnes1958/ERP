<?php

namespace App\Filament\Resources\SellWorkResource\Pages;

use App\Filament\Resources\SellWorkResource;
use App\Models\Sell_work;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSellWorks extends ListRecords
{
    protected ?string $heading='ادخال فواتير مبيعات';
    protected static string $resource = SellWorkResource::class;

    public function mount(): void
    {

        parent::mount(); // TODO: Change the autogenerated stub

        if (!Sell_work::find(Auth::id()))
            Sell_work::create([
                'id'=>Auth::id(),'user_id'=>Auth::id(),'customer_id'=>1,
            ]);

    }

}
