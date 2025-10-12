<?php

namespace App\Livewire\widget;

use Filament\Support\Enums\TextSize;
use App\Enums\Tar_type;
use App\Livewire\Traits\AksatTrait;
use App\Livewire\Traits\MainTrait;
use App\Models\Main;
use App\Models\Sell_tran;
use App\Models\Tran;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\On;


class SelltranWidget extends BaseWidget
{

    protected static ?string $heading='';
    public $sell_id;
    #[On('Take_Main_Id')]
    public function do($main_id)
    {
        $this->sell_id=Main::find($main_id)->sell_id;
    }

    public function mount($main_id){
        $this->sell_id=Main::find($main_id)->sell_id;
    }

    public function table(Table $table): Table
    {
        return $table

            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([5,12,15,50])

            ->query(function (){
                $tran=Sell_tran::where('sell_id',$this->sell_id);
                return $tran;
            })

            ->recordUrl(null)
            ->columns([
                TextColumn::make('ser')
                    ->size(TextSize::ExtraSmall)
                    ->rowIndex()
                    ->color('primary')
                    ->sortable()
                    ->label('ت'),
                TextColumn::make('item_id')
                    ->size(TextSize::ExtraSmall)

                    ->sortable()
                    ->label('رقم الصنف'),
                TextColumn::make('Item.name')
                    ->size(TextSize::ExtraSmall)

                    ->sortable()
                    ->label('اسم الصنف'),
                TextColumn::make('q1')
                    ->size(TextSize::ExtraSmall)
                    ->label('الكمية'),
                TextColumn::make('price1')
                    ->size(TextSize::ExtraSmall)

                    ->label('السعر'),
                TextColumn::make('sub_tot')


                    ->size(TextSize::ExtraSmall)
                    ->label('المجموع'),
            ])
           ;
    }
}
